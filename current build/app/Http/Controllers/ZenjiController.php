<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ZenjiController extends Controller
{
    private $filePath;

    public function __construct()
    {
        // Define the file path in the storage directory
        $this->filePath = storage_path('app/events.json');
    }

    public function getEvents()
    {
        if (File::exists($this->filePath)) {
            $events = File::get($this->filePath);
            return response($events, 200)->header('Content-Type', 'application/json');
        }

        return response()->json([], 200);
    }

    public function saveEvent(Request $request)
    {
        try {
            $newEvent = $request->all();

            // Read existing events
            $events = [];
            if (File::exists($this->filePath)) {
                $events = json_decode(File::get($this->filePath), true);
            }

            // Add new event
            $events[] = $newEvent;

            // Save updated events to the file
            File::put($this->filePath, json_encode($events, JSON_PRETTY_PRINT));

            return response()->json(['message' => 'Event saved successfully'], 200);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error saving event: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save event'], 500);
        }
    }

    public function deleteEvent($id)
    {
        try {
            // Read existing events
            if (!File::exists($this->filePath)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $events = json_decode(File::get($this->filePath), true);

            // Filter out the event to delete
            $events = array_filter($events, function ($event) use ($id) {
                return $event['id'] != $id;
            });

            // Save the updated events back to the file
            File::put($this->filePath, json_encode($events, JSON_PRETTY_PRINT));

            return response()->json(['message' => 'Event deleted successfully'], 200);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error deleting event: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete event'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Read the JSON file
            $events = json_decode(File::get($this->filePath), true);

            // Find the event by ID and update its details
            foreach ($events as &$event) {
                if ($event['id'] == $id) {
                    $event['title'] = $request->input('title');
                    $event['start'] = $request->input('start');
                    $event['end'] = $request->input('end');
                    $event['allDay'] = $request->input('allDay');
                    $event['clientName'] = $request->input('clientName');
                    $event['pickupLocation'] = $request->input('pickupLocation');
                    $event['pickupTime'] = $request->input('pickupTime');
                    $event['dropoffLocation'] = $request->input('dropoffLocation');
                    $event['rate'] = $request->input('rate');
                    break;
                }
            }

            // Save the updated events back to the JSON file
            File::put($this->filePath, json_encode($events, JSON_PRETTY_PRINT));

            return response()->json(['message' => 'Event updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
