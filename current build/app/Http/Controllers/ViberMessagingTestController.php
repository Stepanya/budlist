<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ViberMessagingTestController extends Controller
{
    public function store(Request $request)
    {
        Log::info(
            "Viber messaging test payload received.\n"
            . 'Headers:' . "\n"
            . $this->prettyPrint($request->headers->all()) . "\n"
            . 'Payload:' . "\n"
            . $this->prettyPrint($request->all()) . "\n"
            . 'Raw body:' . "\n"
            . $this->prettyPrint($request->getContent())
        );

        if ($this->messageAndNumberAreEmpty($request)) {
            return response()->json([
                [
                    'message' => 'No message or number provided. Nothing forwarded.',
                ],
            ], 200);
        }

        $validated = $request->validate([
            'Message' => ['required', 'string'],
            'Number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $invalidNumbers = $this->invalidPhilippineNumbers($value);

                    if ($invalidNumbers !== []) {
                        $fail(
                            'Invalid mobile number(s): '.implode(', ', $invalidNumbers)
                            .'. Mobile numbers may be entered using 0, 63, or +63 formats. Multiple numbers may be separated by a comma.'
                        );
                    }
                },
            ],
            'Channel' => ['required', 'string'],
        ]);

        $normalizedNumbers = $this->normalizePhilippineNumbers($validated['Number']);

        $payload = [
            'messages' => [
                'msg' => [[
                    'from' => env('CM_VIBER_FROM', 'Tritel'),
                    'to' => array_map(static function (string $number): array {
                        return ['number' => $number];
                    }, $normalizedNumbers),
                    'body' => [
                        'type' => 'auto',
                        'content' => $validated['Message'],
                    ],
                    'allowedChannels' => [
                        $validated['Channel'],
                    ],
                ]],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'X-CM-PRODUCTTOKEN' => env('CM_PRODUCT_TOKEN', '9477aba9-703f-4ca5-a7e3-c4e551c26956'),
                'Accept' => 'application/json',
            ])->post('https://gw.cmtelecom.com/v1.0/message', $payload);
        } catch (Throwable $exception) {
            Log::error('CM Telecom Viber request failed before response.', [
                'exception' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => 'Failed to reach CM Telecom.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        $responseBody = $response->json();

        if ($responseBody === null) {
            $responseBody = [
                'raw' => $response->body(),
            ];
        }

        Log::info('CM Telecom Viber response received.', [
            'status' => $response->status(),
            'payload' => $payload,
            'response' => $responseBody,
        ]);

        return response()->json([
            [
                'message' => $response->successful()
                    ? 'Message sent to CM Telecom.'
                    : 'CM Telecom returned an error.',
            ],
        ], $response->status());
    }

    private function messageAndNumberAreEmpty(Request $request): bool
    {
        return $this->isNullLike($request->input('Message'))
            && $this->isNullLike($request->input('Number'));
    }

    private function prettyPrint($value): string
    {
        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? (string) $value : $encoded;
    }

    private function isNullLike($value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    private function normalizePhilippineNumbers(string $numbers): ?array
    {
        $parts = $this->splitNumbers($numbers);

        if ($parts === []) {
            return null;
        }

        $normalizedNumbers = [];

        foreach ($parts as $number) {
            $normalized = $this->normalizePhilippineNumber($number);

            if ($normalized === null) {
                return null;
            }

            $normalizedNumbers[] = $normalized;
        }

        return $normalizedNumbers;
    }

    private function invalidPhilippineNumbers(string $numbers): array
    {
        $invalidNumbers = [];

        foreach ($this->splitNumbers($numbers) as $number) {
            if ($this->normalizePhilippineNumber($number) === null) {
                $invalidNumbers[] = $number;
            }
        }

        return $invalidNumbers;
    }

    private function splitNumbers(string $numbers): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $numbers)), static function ($value) {
            return $value !== '';
        }));
    }

    private function normalizePhilippineNumber(string $number): ?string
    {
        $normalized = preg_replace('/[\s()-]+/', '', trim($number));

        if ($normalized === null || ! preg_match('/^(?:\+63|63)\d{9,10}$|^0\d{9,10}$/', $normalized)) {
            return null;
        }

        if (strpos($normalized, '+63') === 0) {
            return '63'.substr($normalized, 3);
        }

        if (strpos($normalized, '0') === 0) {
            return '63'.substr($normalized, 1);
        }

        return $normalized;
    }
}
