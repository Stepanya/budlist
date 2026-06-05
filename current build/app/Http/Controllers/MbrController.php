<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MbrController extends Controller
{
    public function process(Request $request)
    {
        try {
            $request->validate([
                'files' => ['required', 'array', 'min:1'],
                'files.*' => ['required', 'file', 'mimes:csv,txt'],
            ]);

            $rows = [];
            $requestPrefix = null;
            $requestMode = null;

            foreach ($request->file('files') as $uploadedFile) {
                $stats = $this->extractFileStats($uploadedFile);

                if ($requestPrefix === null) {
                    $requestPrefix = $stats['prefix'];
                    $requestMode = $stats['mode'];
                } elseif ($requestPrefix !== $stats['prefix']) {
                    throw new \RuntimeException(
                        "All uploaded files must be the same type/prefix. ".
                        "Expected '{$requestPrefix}', got '{$stats['prefix']}' from {$stats['originalName']}."
                    );
                }
                $rows[] = $stats;
            }

            usort($rows, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            $content = $this->buildOutputCsv($rows, $requestMode ?? 'gcash');
            $outputName = $this->buildOutputFilename($requestPrefix ?? 'gcash', $rows);

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$outputName.'"',
            ]);
        } catch (ValidationException $e) {
            Log::warning('MBR validation error.', [
                'errors' => $e->errors(),
                'exception' => $e,
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error('MBR processing failed.', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to process uploaded file(s). '.$e->getMessage(),
            ], 422);
        }
    }

    private function buildOutputCsv(array $rows, string $mode): string
    {
        if ($mode === 'zolos') {
            $bucketColumns = [];
            foreach ($rows as $stats) {
                foreach ($stats['bucketLabelCounts'] as $label => $count) {
                    if (!isset($bucketColumns[$label])) {
                        $bucketColumns[$label] = true;
                    }
                }
            }
            $bucketColumns = array_keys($bucketColumns);
            usort($bucketColumns, 'strcasecmp');

            $headers = [
                'Date',
                'Batch',
                'Zoloz Run Completed',
                'Total Processed',
            ];
            foreach ($bucketColumns as $label) {
                $headers[] = $label;
            }
            $headers[] = 'Total Passed';
            $headers[] = 'Total Failed';
            $headers[] = 'Total Zoloz Images';
        } elseif ($mode === 'atome') {
            $headers = [
                'Date',
                'Batch',
                'Atome Run Completed',
                'Total Processed',
                'Pass - AC01',
                'Pass - AC02',
                'Pass - AC03',
                'Pass - AC04',
                'Total Passed',
                'Fail - For Documents (AtomeCredit)',
                'Fail - Manual QA (AtomeCredit)',
                'Unsupported IDs (AtomeCredit)',
                'Fail - Minimum REDO (AtomeCredit)',
                'FAIL SUSPICIOUS',
                'Total Failed',
                'Total AtomeCredit Images',
            ];
        } elseif ($mode === 'dragon') {
            $headers = [
                'Date',
                'Batch',
                'DragonFi Run Completed',
                'Total Processed',
                'Pass - DS01',
                'Pass - DS02',
                'Pass - DS03',
                'Pass - DS04',
                'Total Passed',
                'Fail - For Documents (DragonSave)',
                'Fail - Manual QA (DragonSave)',
                'Unsupported IDs (DragonSave)',
                'Fail - Minimum REDO (DragonSave)',
                'Total Failed',
                'Total DragonSave Images',
            ];
        } elseif ($mode === 'lazada') {
            $headers = [
                'Date',
                'Batch',
                'Lazada Run Completed',
                'Total Processed',
                'Pass - LS01',
                'Pass - LS02',
                'Pass - LS03',
                'Pass - LS04',
                'Pass - LP03',
                'Pass - LP04',
                'Total Passed',
                'Fail - For Documents (LazSave)',
                'Fail - For Documents (LazPaylater)',
                'Fail - Manual QA (LazSave)',
                'Fail - Manual QA (LazPaylater)',
                'Unsupported IDs (LazSave)',
                'Unsupported IDs (LazPaylater)',
                'Fail - Minimum REDO (LazSave)',
                'Fail - Minimum REDO (LazPaylater)',
                'Fail Suspicious - LazSave',
                'Fail Suspicious - LazPaylater',
                'Matched ID DOB (LazSave)',
                'Matched ID DOB (LazPaylater)',
                'Mismatched ID DOB (LazSave)',
                'Mismatched ID DOB (LazPaylater)',
                'With ID DOB (LazSave)',
                'With ID DOB (LazPaylater)',
                'Without ID DOB (LazSave)',
                'Without ID DOB (LazPaylater)',
                'Total Failed',
                'Total LazSave Images',
                'Total LazPaylater Images',
            ];
        } elseif ($mode === 'seamoney') {
            $headers = [
                'Date',
                'Batch',
                'SeaMoney Run Completed',
                'Total Processed',
                'Pass - SM03',
                'Pass - SM04',
                'Fail - For Documents',
                'Fail - Manual QA',
                'Unsupported IDs',
                'Fail - Minimum REDO',
                'FAIL SUSPICIOUS',
                'Matched ID DOB',
                'Mismatched ID DOB',
                'With ID DOB',
                'Without ID DOB',
                'Total SeaMoney Images',
            ];
        } else {
            $headers = [
                'Date',
                'Batch',
                'CAPTRO Run Completed',
                'Total Processed',
                'Pass - GS01',
                'Pass - GS02',
                'Pass - GS03',
                'Pass - GS04',
                'Pass - GC03',
                'Pass - GC04',
                'Total Passed',
                'Fail - For Documents (GSave)',
                'Fail - For Documents (GCredit)',
                'Fail - Manual QA (GSave)',
                'Fail - Manual QA (GCredit)',
                'Unsupported IDs (GSave)',
                'Unsupported IDs (GCredit)',
                'Fail - Minimum REDO (GSave)',
                'Fail - Minimum REDO (GCredit)',
                'FAIL SUSPICIOUS (GSave)',
                'FAIL SUSPICIOUS (GCredit)',
                'Matched ID DOB (GSave)',
                'Matched ID DOB (GCredit)',
                'Mismatched ID DOB (GSave)',
                'Mismatched ID DOB (GCredit)',
                'With ID DOB (GSave)',
                'With ID DOB (GCredit)',
                'Without ID DOB (GSave)',
                'Without ID DOB (GCredit)',
                'Total Failed',
                'Total GSave Images',
                'Total GCredit Images',
            ];
        }

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers);

        foreach ($rows as $stats) {
            if ($mode === 'zolos') {
                $row = [
                    $stats['yesterday'],
                    $stats['prefix'].'_captro_input_'.$stats['inputFileDate'].'.csv',
                    'YES',
                    $stats['totalProcessed'],
                ];
                foreach ($bucketColumns as $label) {
                    $row[] = $stats['bucketLabelCounts'][$label] ?? 0;
                }
                $row[] = $stats['totalPassed'];
                $row[] = $stats['totalFailed'];
                $row[] = $stats['totalZolozImages'];
            } elseif ($mode === 'atome') {
                $row = [
                    $stats['yesterday'],
                    'atome_captro_input_'.$stats['inputFileDate'].'.csv',
                    'YES',
                    $stats['totalProcessed'],
                    $stats['counts']['ac01'] ?? 0,
                    $stats['counts']['ac02'] ?? 0,
                    $stats['counts']['ac03'] ?? 0,
                    $stats['counts']['ac04'] ?? 0,
                    $stats['totalPassed'],
                    $stats['counts']['ffd'] ?? 0,
                    $stats['counts']['fmqa'] ?? 0,
                    $stats['counts']['unsupp'] ?? 0,
                    $stats['counts']['fmr'] ?? 0,
                    $stats['counts']['fs'] ?? 0,
                    $stats['totalFailed'],
                    $stats['totalACImages'],
                ];
            } elseif ($mode === 'dragon') {
                $row = [
                    $stats['yesterday'],
                    'dragon_captro_input_'.$stats['inputFileDate'].'.csv',
                    'YES',
                    $stats['totalProcessed'],
                    $stats['counts']['ds01'] ?? 0,
                    $stats['counts']['ds02'] ?? 0,
                    $stats['counts']['ds03'] ?? 0,
                    $stats['counts']['ds04'] ?? 0,
                    $stats['totalPassed'],
                    $stats['counts']['dffd'] ?? 0,
                    $stats['counts']['dfmqa'] ?? 0,
                    $stats['counts']['dunsupp'] ?? 0,
                    $stats['counts']['dfmr'] ?? 0,
                    $stats['totalFailed'],
                    $stats['totalDSImages'],
                ];
            } elseif ($mode === 'lazada') {
                $row = [
                    $stats['yesterday'],
                    'lazada_captro_input_'.$stats['inputFileDate'].'.csv',
                    'YES',
                    $stats['totalProcessed'],
                    $stats['counts']['ls01'] ?? 0,
                    $stats['counts']['ls02'] ?? 0,
                    $stats['counts']['ls03'] ?? 0,
                    $stats['counts']['ls04'] ?? 0,
                    $stats['counts']['lp03'] ?? 0,
                    $stats['counts']['lp04'] ?? 0,
                    $stats['totalPassed'],
                    $stats['counts']['ffdls'] ?? 0,
                    $stats['counts']['ffdlp'] ?? 0,
                    $stats['counts']['fmqals'] ?? 0,
                    $stats['counts']['fmqalp'] ?? 0,
                    $stats['counts']['unsuppls'] ?? 0,
                    $stats['counts']['unsupplp'] ?? 0,
                    $stats['counts']['fmrls'] ?? 0,
                    $stats['counts']['fmrlp'] ?? 0,
                    $stats['counts']['fsls'] ?? 0,
                    $stats['counts']['fslp'] ?? 0,
                    $stats['counts']['matchiddobls'] ?? 0,
                    $stats['counts']['matchiddoblp'] ?? 0,
                    $stats['counts']['mismatchiddobls'] ?? 0,
                    $stats['counts']['mismatchiddoblp'] ?? 0,
                    $stats['counts']['withiddobls'] ?? 0,
                    $stats['counts']['withiddoblp'] ?? 0,
                    $stats['counts']['withoutiddobls'] ?? 0,
                    $stats['counts']['withoutiddoblp'] ?? 0,
                    $stats['totalFailed'],
                    $stats['totalLSImages'],
                    $stats['totalLPImages'],
                ];
            } elseif ($mode === 'seamoney') {
                $row = [
                    $stats['yesterday'],
                    'smn_captro_input_'.$stats['inputFileDate'].'.csv',
                    'YES',
                    $stats['totalProcessed'],
                    $stats['counts']['sm03'] ?? 0,
                    $stats['counts']['sm04'] ?? 0,
                    $stats['counts']['sffd'] ?? 0,
                    $stats['counts']['sfmqa'] ?? 0,
                    $stats['counts']['sunsupp'] ?? 0,
                    $stats['counts']['sfmr'] ?? 0,
                    $stats['counts']['sfs'] ?? 0,
                    $stats['counts']['smatchiddob'] ?? 0,
                    $stats['counts']['smismatchiddob'] ?? 0,
                    $stats['counts']['swithiddob'] ?? 0,
                    $stats['counts']['swithoutiddob'] ?? 0,
                    $stats['totalSMImages'],
                ];
            } else {
                $row = [
                    $stats['yesterday'],
                    'captro_input_'.$stats['inputFileDate'].'.csv',
                    'YES',
                    $stats['totalProcessed'],
                    $stats['counts']['gs01'] ?? 0,
                    $stats['counts']['gs02'] ?? 0,
                    $stats['counts']['gs03'] ?? 0,
                    $stats['counts']['gs04'] ?? 0,
                    $stats['counts']['gc03'] ?? 0,
                    $stats['counts']['gc04'] ?? 0,
                    $stats['totalPassed'],
                    $stats['counts']['ffdgs'] ?? 0,
                    $stats['counts']['ffdgc'] ?? 0,
                    $stats['counts']['fmqags'] ?? 0,
                    $stats['counts']['fmqagc'] ?? 0,
                    $stats['counts']['unsuppgs'] ?? 0,
                    $stats['counts']['unsuppgc'] ?? 0,
                    $stats['counts']['fmrgs'] ?? 0,
                    $stats['counts']['fmrgc'] ?? 0,
                    $stats['counts']['fsgs'] ?? 0,
                    $stats['counts']['fsgc'] ?? 0,
                    $stats['counts']['matchiddobgs'] ?? 0,
                    $stats['counts']['matchiddobgc'] ?? 0,
                    $stats['counts']['mismatchiddobgs'] ?? 0,
                    $stats['counts']['mismatchiddobgc'] ?? 0,
                    $stats['counts']['withiddobgs'] ?? 0,
                    $stats['counts']['withiddobgc'] ?? 0,
                    $stats['counts']['withoutiddobgs'] ?? 0,
                    $stats['counts']['withoutiddobgc'] ?? 0,
                    $stats['totalFailed'],
                    $stats['totalGSImages'],
                    $stats['totalGCImages'],
                ];
            }
            fputcsv($stream, $row);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content !== false ? $content : '';
    }

    private function extractFileStats($uploadedFile): array
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $prefix = $this->extractPrefix($originalName);
        $mode = $this->resolveModeFromPrefix($prefix);
        $date = $this->extractDate($originalName);
        $inputFileDate = str_replace('-', '', $date);
        $yesterday = date('Y-m-d', strtotime($date.' -1 day'));

        $handle = fopen($uploadedFile->getRealPath(), 'r');
        if (!$handle) {
            throw new \RuntimeException("Could not open file: {$originalName}");
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException("File is empty: {$originalName}");
        }

        $captroBucketIndex = $this->findHeaderIndex($header, 'captro_bucket');
        if ($captroBucketIndex === null) {
            fclose($handle);
            throw new \RuntimeException("Missing captro_bucket column in {$originalName}");
        }
        $docNoIndex = $this->findHeaderIndex($header, 'document_no_primary_id');

        $totalProcessed = 0;
        $totalPassedByRule = 0;
        $totalFailedByRule = 0;
        $bucketLabelCounts = [];
        $counts = [
            'gs01' => 0,
            'gs02' => 0,
            'gs03' => 0,
            'gs04' => 0,
            'gc03' => 0,
            'gc04' => 0,
            'ffdgs' => 0,
            'ffdgc' => 0,
            'fmqags' => 0,
            'fmqagc' => 0,
            'unsuppgs' => 0,
            'unsuppgc' => 0,
            'fmrgs' => 0,
            'fmrgc' => 0,
            'fsgs' => 0,
            'fsgc' => 0,
            'matchiddobgs' => 0,
            'matchiddobgc' => 0,
            'mismatchiddobgs' => 0,
            'mismatchiddobgc' => 0,
            'withiddobgs' => 0,
            'withiddobgc' => 0,
            'withoutiddobgs' => 0,
            'withoutiddobgc' => 0,
            'ac01' => 0,
            'ac02' => 0,
            'ac03' => 0,
            'ac04' => 0,
            'ffd' => 0,
            'fmqa' => 0,
            'unsupp' => 0,
            'fmr' => 0,
            'fs' => 0,
            'ds01' => 0,
            'ds02' => 0,
            'ds03' => 0,
            'ds04' => 0,
            'dffd' => 0,
            'dfmqa' => 0,
            'dunsupp' => 0,
            'dfmr' => 0,
            'ls01' => 0,
            'ls02' => 0,
            'ls03' => 0,
            'ls04' => 0,
            'lp03' => 0,
            'lp04' => 0,
            'ffdls' => 0,
            'ffdlp' => 0,
            'fmqals' => 0,
            'fmqalp' => 0,
            'unsuppls' => 0,
            'unsupplp' => 0,
            'fmrls' => 0,
            'fmrlp' => 0,
            'fsls' => 0,
            'fslp' => 0,
            'matchiddobls' => 0,
            'matchiddoblp' => 0,
            'mismatchiddobls' => 0,
            'mismatchiddoblp' => 0,
            'withiddobls' => 0,
            'withiddoblp' => 0,
            'withoutiddobls' => 0,
            'withoutiddoblp' => 0,
            'sm03' => 0,
            'sm04' => 0,
            'sffd' => 0,
            'sfmqa' => 0,
            'sunsupp' => 0,
            'sfmr' => 0,
            'sfs' => 0,
            'smatchiddob' => 0,
            'smismatchiddob' => 0,
            'swithiddob' => 0,
            'swithoutiddob' => 0,
        ];
        $totalGSImages = 0;
        $totalGCImages = 0;
        $totalACImages = 0;
        $totalDSImages = 0;
        $totalLSImages = 0;
        $totalLPImages = 0;
        $totalSMImages = 0;
        $totalZolozImages = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $totalProcessed++;
            $bucketRaw = isset($row[$captroBucketIndex]) ? trim((string) $row[$captroBucketIndex]) : '';
            $bucketKey = $this->normalizeBucketKey($bucketRaw);
            if ($bucketRaw !== '') {
                $bucketLabelCounts[$bucketRaw] = ($bucketLabelCounts[$bucketRaw] ?? 0) + 1;
            }
            $docNo = $docNoIndex !== null ? strtoupper(trim((string) ($row[$docNoIndex] ?? ''))) : '';
            $segment = $this->detectSegmentFromDocumentNumber($docNo);

            if ($bucketKey !== '') {
                if (strpos($bucketKey, 'pass') === 0) {
                    $totalPassedByRule++;
                } else {
                    $totalFailedByRule++;
                }
            }

            $mapped = $this->mapBucketToCounter($bucketKey, $segment, $mode);
            if ($mapped !== null) {
                $counts[$mapped]++;
            }

            if ($segment === 'gc') {
                $totalGCImages++;
            } elseif ($segment === 'gs') {
                $totalGSImages++;
            } elseif ($segment === 'ac') {
                $totalACImages++;
            } elseif ($segment === 'ds') {
                $totalDSImages++;
            } elseif ($segment === 'ls') {
                $totalLSImages++;
            } elseif ($segment === 'lp') {
                $totalLPImages++;
            } elseif ($segment === 'sm') {
                $totalSMImages++;
            } elseif ($segment === 'zolos') {
                $totalZolozImages++;
            }
        }
        fclose($handle);

        $totalPassed = $totalPassedByRule;
        $totalFailed = $totalFailedByRule;

        return [
            'originalName' => $originalName,
            'prefix' => $prefix,
            'mode' => $mode,
            'date' => $date,
            'inputFileDate' => $inputFileDate,
            'yesterday' => $yesterday,
            'totalProcessed' => $totalProcessed,
            'counts' => $counts,
            'bucketLabelCounts' => $bucketLabelCounts,
            'totalPassed' => $totalPassed,
            'totalFailed' => $totalFailed,
            'totalGSImages' => $totalGSImages,
            'totalGCImages' => $totalGCImages,
            'totalACImages' => $totalACImages,
            'totalDSImages' => $totalDSImages,
            'totalLSImages' => $totalLSImages,
            'totalLPImages' => $totalLPImages,
            'totalSMImages' => $totalSMImages,
            'totalZolozImages' => $totalZolozImages,
        ];
    }

    private function findHeaderIndex(array $header, string $expectedColumn): ?int
    {
        foreach ($header as $index => $column) {
            $normalized = strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $column)));
            if ($normalized === strtolower($expectedColumn)) {
                return $index;
            }
        }

        return null;
    }

    private function buildOutputFilename(string $prefix, array $rows): string
    {
        $dates = [];
        foreach ($rows as $row) {
            if (!empty($row['inputFileDate'])) {
                $dates[] = (string) $row['inputFileDate'];
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        if (count($dates) === 1) {
            return $prefix.'_result_'.$dates[0].'.csv';
        }

        if (count($dates) > 1) {
            return $prefix.'_result_'.$dates[0].'-'.$dates[count($dates) - 1].'.csv';
        }

        return $prefix.'_result_'.date('Ymd').'.csv';
    }

    private function extractPrefix(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $prefix = '';

        if (preg_match('/^(.*?)_?captro_output(?:_\d{8})?$/i', $base, $matches)) {
            $prefix = trim((string) ($matches[1] ?? ''), '_');
        }

        if ($prefix === '') {
            $prefix = 'gcash';
        }

        $safePrefix = strtolower((string) preg_replace('/[^A-Za-z0-9_-]/', '_', $prefix));
        return $safePrefix !== '' ? $safePrefix : 'gcash';
    }

    private function extractDate(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        if (preg_match('/captro_output_(\d{8})$/i', $base, $matches)) {
            $raw = $matches[1];
            $year = substr($raw, 0, 4);
            $month = substr($raw, 4, 2);
            $day = substr($raw, 6, 2);
            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        return date('Y-m-d');
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeBucketKey(string $bucket): string
    {
        $normalized = strtolower(trim($bucket));
        return (string) preg_replace('/[^a-z0-9]/', '', $normalized);
    }

    private function mapBucketToCounter(string $bucketKey, ?string $segment, string $mode): ?string
    {
        if ($mode === 'atome') {
            $atomeMap = [
                'passac01' => 'ac01',
                'passac02' => 'ac02',
                'passac03' => 'ac03',
                'passac04' => 'ac04',
            ];

            if (isset($atomeMap[$bucketKey])) {
                return $atomeMap[$bucketKey];
            }

            if ($bucketKey === 'failfordocuments') {
                return 'ffd';
            }
            if ($bucketKey === 'failmanualqa') {
                return 'fmqa';
            }
            if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                return 'unsupp';
            }
            if ($bucketKey === 'failminimumredo') {
                return 'fmr';
            }
            if ($bucketKey === 'failsuspicious') {
                return 'fs';
            }

            return null;
        }

        if ($mode === 'dragon') {
            $dragonMap = [
                'passds01' => 'ds01',
                'passds02' => 'ds02',
                'passds03' => 'ds03',
                'passds04' => 'ds04',
            ];

            if (isset($dragonMap[$bucketKey])) {
                return $dragonMap[$bucketKey];
            }

            if ($bucketKey === 'failfordocuments') {
                return 'dffd';
            }
            if ($bucketKey === 'failmanualqa') {
                return 'dfmqa';
            }
            if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                return 'dunsupp';
            }
            if ($bucketKey === 'failminimumredo') {
                return 'dfmr';
            }

            return null;
        }

        if ($mode === 'lazada') {
            $lazadaMap = [
                'passls01' => 'ls01',
                'passls02' => 'ls02',
                'passls03' => 'ls03',
                'passls04' => 'ls04',
                'passlp03' => 'lp03',
                'passlp04' => 'lp04',
            ];

            if (isset($lazadaMap[$bucketKey])) {
                return $lazadaMap[$bucketKey];
            }

            if ($segment === 'ls') {
                if ($bucketKey === 'failfordocuments') {
                    return 'ffdls';
                }
                if ($bucketKey === 'failmanualqa') {
                    return 'fmqals';
                }
                if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                    return 'unsuppls';
                }
                if ($bucketKey === 'failminimumredo') {
                    return 'fmrls';
                }
                if ($bucketKey === 'failsuspicious') {
                    return 'fsls';
                }
                if ($bucketKey === 'matchediddob') {
                    return 'matchiddobls';
                }
                if ($bucketKey === 'mismatchediddob') {
                    return 'mismatchiddobls';
                }
                if ($bucketKey === 'withiddob') {
                    return 'withiddobls';
                }
                if ($bucketKey === 'withoutiddob') {
                    return 'withoutiddobls';
                }
            }

            if ($segment === 'lp') {
                if ($bucketKey === 'failfordocuments') {
                    return 'ffdlp';
                }
                if ($bucketKey === 'failmanualqa') {
                    return 'fmqalp';
                }
                if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                    return 'unsupplp';
                }
                if ($bucketKey === 'failminimumredo') {
                    return 'fmrlp';
                }
                if ($bucketKey === 'failsuspicious') {
                    return 'fslp';
                }
                if ($bucketKey === 'matchediddob') {
                    return 'matchiddoblp';
                }
                if ($bucketKey === 'mismatchediddob') {
                    return 'mismatchiddoblp';
                }
                if ($bucketKey === 'withiddob') {
                    return 'withiddoblp';
                }
                if ($bucketKey === 'withoutiddob') {
                    return 'withoutiddoblp';
                }
            }

            return null;
        }

        if ($mode === 'seamoney') {
            $seaMoneyMap = [
                'passsm03' => 'sm03',
                'passsm04' => 'sm04',
            ];

            if (isset($seaMoneyMap[$bucketKey])) {
                return $seaMoneyMap[$bucketKey];
            }

            if ($bucketKey === 'failfordocuments') {
                return 'sffd';
            }
            if ($bucketKey === 'failmanualqa') {
                return 'sfmqa';
            }
            if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                return 'sunsupp';
            }
            if ($bucketKey === 'failminimumredo') {
                return 'sfmr';
            }
            if ($bucketKey === 'failsuspicious') {
                return 'sfs';
            }
            if ($bucketKey === 'matchediddob') {
                return 'smatchiddob';
            }
            if ($bucketKey === 'mismatchediddob') {
                return 'smismatchiddob';
            }
            if ($bucketKey === 'withiddob') {
                return 'swithiddob';
            }
            if ($bucketKey === 'withoutiddob') {
                return 'swithoutiddob';
            }

            return null;
        }

        // Explicit labels that already encode the segment.
        static $map = [
            'passgs01' => 'gs01',
            'passgs02' => 'gs02',
            'passgs03' => 'gs03',
            'passgs04' => 'gs04',
            'passgc03' => 'gc03',
            'passgc04' => 'gc04',
            'failfordocumentsgsave' => 'ffdgs',
            'failfordocumentsgcredit' => 'ffdgc',
            'failmanualqagsave' => 'fmqags',
            'failmanualqagcredit' => 'fmqagc',
            'unsupportedidsgsave' => 'unsuppgs',
            'unsupportedidsgcredit' => 'unsuppgc',
            'failminimumredogsave' => 'fmrgs',
            'failminimumredogcredit' => 'fmrgc',
            'failsuspiciousgsave' => 'fsgs',
            'failsuspiciousgcredit' => 'fsgc',
            'matchediddobgsave' => 'matchiddobgs',
            'matchediddobgcredit' => 'matchiddobgc',
            'mismatchediddobgsave' => 'mismatchiddobgs',
            'mismatchediddobgcredit' => 'mismatchiddobgc',
            'withiddobgsave' => 'withiddobgs',
            'withiddobgcredit' => 'withiddobgc',
            'withoutiddobgsave' => 'withoutiddobgs',
            'withoutiddobgcredit' => 'withoutiddobgc',
        ];

        if (isset($map[$bucketKey])) {
            return $map[$bucketKey];
        }

        // Generic fail labels from actual files, segment decided by document number prefix.
        if ($segment === 'gs') {
            if ($bucketKey === 'failfordocuments') {
                return 'ffdgs';
            }
            if ($bucketKey === 'failmanualqa') {
                return 'fmqags';
            }
            if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                return 'unsuppgs';
            }
            if ($bucketKey === 'failminimumredo') {
                return 'fmrgs';
            }
            if ($bucketKey === 'failsuspicious') {
                return 'fsgs';
            }
            if ($bucketKey === 'matchediddob') {
                return 'matchiddobgs';
            }
            if ($bucketKey === 'mismatchediddob') {
                return 'mismatchiddobgs';
            }
            if ($bucketKey === 'withiddob') {
                return 'withiddobgs';
            }
            if ($bucketKey === 'withoutiddob') {
                return 'withoutiddobgs';
            }
        }

        if ($segment === 'gc') {
            if ($bucketKey === 'failfordocuments') {
                return 'ffdgc';
            }
            if ($bucketKey === 'failmanualqa') {
                return 'fmqagc';
            }
            if ($bucketKey === 'unsupportedid' || $bucketKey === 'unsupportedids') {
                return 'unsuppgc';
            }
            if ($bucketKey === 'failminimumredo') {
                return 'fmrgc';
            }
            if ($bucketKey === 'failsuspicious') {
                return 'fsgc';
            }
            if ($bucketKey === 'matchediddob') {
                return 'matchiddobgc';
            }
            if ($bucketKey === 'mismatchediddob') {
                return 'mismatchiddobgc';
            }
            if ($bucketKey === 'withiddob') {
                return 'withiddobgc';
            }
            if ($bucketKey === 'withoutiddob') {
                return 'withoutiddobgc';
            }
        }

        return null;
    }

    private function detectSegmentFromDocumentNumber(string $docNo): ?string
    {
        if (strpos($docNo, 'AC') === 0) {
            return 'ac';
        }
        if (strpos($docNo, 'DS') === 0) {
            return 'ds';
        }
        if (strpos($docNo, 'LP') === 0) {
            return 'lp';
        }
        if (strpos($docNo, 'LS') === 0) {
            return 'ls';
        }
        if (strpos($docNo, 'SMN') === 0 || strpos($docNo, 'SM') === 0) {
            return 'sm';
        }
        if (strpos($docNo, 'ZOLOZ') === 0) {
            return 'zolos';
        }
        if (strpos($docNo, 'GC-FULL') === 0) {
            return 'gc';
        }
        if (strpos($docNo, 'FULL') === 0) {
            return 'gs';
        }

        return null;
    }

    private function resolveModeFromPrefix(string $prefix): string
    {
        if ($prefix === 'atome') {
            return 'atome';
        }
        if ($prefix === 'dragon' || $prefix === 'dragonfi') {
            return 'dragon';
        }
        if ($prefix === 'lazada') {
            return 'lazada';
        }
        if ($prefix === 'smn' || $prefix === 'seamoney') {
            return 'seamoney';
        }
        if ($prefix === 'nat' || $prefix === 'zolos') {
            return 'zolos';
        }

        return 'gcash';
    }
}
