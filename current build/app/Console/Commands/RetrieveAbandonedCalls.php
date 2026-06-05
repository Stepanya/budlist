<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class RetrieveAbandonedCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retrieve:abandoned-calls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve abandoned calls and update the auto dialer.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Make the API call using Results API
        $apiResponse = $this->getRealtimeCalls();

        if ($apiResponse === null) {
            $this->error('No response received from the API. Task stopped.');
            Log::error('No response received from the API. Task stopped.');
            return 1; // Return an error code
        }

        // Filter the API response based on categories
        $filteredCalls = $this->filterAbandonedCalls($apiResponse);

        if (empty($filteredCalls)) {
            $this->info('No abandoned calls found. Task stopped.');
            Log::info('No abandoned calls found. Task stopped.');
            return 0; // Return a success code
        }

        // Your logic to retrieve abandoned calls and update the auto dialer goes here
        $this->info('Abandoned calls retrieved and auto dialer updated successfully.');
        Log::info('Abandoned calls retrieved and auto dialer updated successfully.');
    }

    /**
     * Get realtime calls data via a GET API call using Guzzle.
     *
     * @return array|null
     */
    private function getRealtimeCalls()
    {
        $apiKey = env('RESULTS_API_KEY');
        $apiUrl = "https://api.apac2.quandago.app/v2/realtimeinfo/inbound/routepoints.json?apikey=$apiKey";

        $client = new Client();

        try {
            $response = $client->get($apiUrl);
        } catch (\Exception $e) {
            // Catch general exceptions
            $message = $e->getMessage();
            Log::error("Error making API request: $message");
            return null;
        }
        
        // Check if the request was successful (status code 200)
        if ($response->getStatusCode() == 200) {
            $allCalls = json_decode($response->getBody(), true);

            // Log the count of all calls retrieved
            $allCallsCount = count($allCalls);
            Log::info("Count of all calls retrieved: $allCallsCount");
            
            return $allCalls;
        }

        return null;
    }

    /**
     * Filter abandoned calls based on specified categories.
     *
     * @param array|null $apiResponse
     * @return array
     */
    private function filterAbandonedCalls($apiResponse)
    {
        if ($apiResponse === null) {
            return [];
        }

        $filteredCalls = array_filter($apiResponse, function ($call) {
            return $call['call_status'] === 'completed'
                && $call['milli_routed'] !== null
                && $call['milli_hangup'] !== null
                && $call['milli_answered'] === null;
        });

        // Log the count of filtered calls
        $filteredCallsCount = count($filteredCalls);
        Log::info("Count of filtered abandoned calls: $filteredCallsCount");

        return $filteredCalls;
    }
}
