<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Compact Rate Calculator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
</head>

<style>
    /* Custom styles for specific screen size */
    @media (max-width: 480px) {
        .fc-toolbar.fc-header-toolbar {
            flex-direction: column;
            align-items: flex-end;
        }

        .fc-toolbar .fc-left,
        .fc-toolbar .fc-center,
        .fc-toolbar .fc-right {
            width: 100%;
            margin-bottom: 8px;
            text-align: right;
        }

        .fc-button {
            width: auto;
            padding: 4px 8px; /* Reduce padding for smaller buttons */
            font-size: 10px; /* Reduce button font size */
        }

        .fc-toolbar-title {
            font-size: 14px; /* Further reduce the month title font size */
        }

        .fc-daygrid-day-top {
            font-size: 10px; /* Reduce the size of the day numbers */
        }

        .fc-daygrid-event {
            font-size: 10px; /* Reduce the font size of the events */
            padding: 2px 4px; /* Adjust padding for events */
        }

        .fc-col-header-cell-cushion {
            font-size: 12px; /* Reduce the font size of day headers (Sun, Mon, etc.) */
        }

        .fc .fc-scrollgrid-sync-inner {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        /* Ensure the calendar takes up the full height of its container */
        #calendar-container {
            width: 100%;
            max-width: 100%; /* Prevents overflowing */
            margin: 0 auto; /* Center the calendar */
        }
    }
</style>



<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="container mx-auto p-6 overflow-auto h-screen">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            
            <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-lg h-max sm:w-fit w-full">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold mb-3 text-center">Rate Calculator</h2>
                    <button id="toggleRateCalculator" class="block md:hidden">
                        <svg id="toggleArrow" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <div id="rateCalculatorContent" class="hidden md:block"> <!-- Initially hidden on mobile view -->
                    <table class="min-w-full text-sm">
                        <tbody>
                            <tr>
                                <td class="px-2 py-1">Diesel price</td>
                                <td class="px-2 py-1">₱ <input type="number" id="diesel-price" value="63.00" class="input-cell bg-yellow-100 rounded w-24 text-right p-1" step="0.1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Km per liter</td>
                                <td class="px-2 py-1"><input type="number" id="km-per-liter" value="12" class="input-cell bg-yellow-100 rounded w-24 text-right p-1" step="0.1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Distance (km)</td>
                                <td class="px-2 py-1"><input type="number" id="distance" value="0.00" class="input-cell bg-yellow-100 rounded w-24 text-right p-1" step="0.1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Fuel Consumed (L)</td>
                                <td class="px-2 py-1"><input type="number" id="fuel-consumed" value="0.00" class="rounded w-24 text-right p-1" readonly></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Fuel Cost</td>
                                <td class="px-2 py-1">₱ 
                                    <input type="number" id="fuel-cost" value="0.00" class="rounded w-24 text-right p-1" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="exclude-fuel-cost" class="inline-flex items-center ml-2">
                                        <input type="checkbox" id="exclude-fuel-cost" class="input-cell rounded">
                                        <strong class="ml-1">Exclude Fuel Cost</strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Idle Fuel Cost</td>
                                <td class="px-2 py-1">₱ <input type="number" id="idle-fuel-cost" value="200.00" class="input-cell rounded w-24 text-right p-1" step="0.1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Toll Fees</td>
                                <td class="px-2 py-1">₱ 
                                    <input type="number" id="toll-fees" value="0.00" class="input-cell bg-yellow-100 rounded w-24 text-right p-1" step="0.1">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="exclude-toll-fees" class="inline-flex items-center ml-2">
                                        <input type="checkbox" id="exclude-toll-fees" class="input-cell rounded">
                                        <strong class="ml-1">Exclude toll fees</strong>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">L300 Daily Rate</td>
                                <td class="px-2 py-1">₱ <input type="number" id="l300-daily-rate" value="2000.00" class="input-cell rounded w-24 text-right p-1" step="0.1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Driver Daily Rate</td>
                                <td class="px-2 py-1">₱ <input type="number" id="driver-daily-rate" value="550.00" class="input-cell rounded w-24 text-right p-1" step="0.1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Days</td>
                                <td class="px-2 py-1"><input type="number" id="days" value="1" class="input-cell rounded w-24 text-right p-1" step="1"></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 font-semibold">Total Daily Rate</td>
                                <td class="px-2 py-1 font-semibold">₱ <input type="number" id="total-daily-rate" value="0.00" class="rounded w-24 text-right p-1" readonly></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 font-semibold">Total Rate</td>
                                <td class="px-2 py-1 font-semibold">₱ <input type="number" id="total-rate" value="200.00" class="rounded bg-green-300 w-24 text-right p-1" readonly></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            

            <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4">Calendar</h2>
                <div id='calendar'></div>
            </div>

            <!-- Modal for Event Creation -->
            <div id="eventModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center z-50 justify-center">
                <div class="modal-content bg-white p-6 m-6 rounded-lg shadow-lg pointer-events-auto md:w-1/2 lg:w-1/3 w-5/6" onclick="event.stopPropagation()">
                    <h2 class="text-xl font-bold mb-4">Add Event</h2>
                    <form id="eventForm">
                        <input type="hidden" id="startDate">
                        <div class="mb-4">
                            <label class="block text-gray-700">Client Name</label>
                            <input type="text" id="clientName" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">Pickup Location</label>
                            <input type="text" id="pickupLocation" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">Pickup Time</label>
                            <input type="time" id="pickupTime" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">Dropoff Location</label>
                            <input type="text" id="dropoffLocation" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">Rate</label>
                            <input type="text" id="rate" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700">End Date</label>
                            <input type="date" id="endDate" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" id="cancelBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg mr-2">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Add Event</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Modal for Event Details -->
            <div id="detailsModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center z-50 justify-center">
                <div class="modal-content bg-white p-6 m-6 rounded-lg shadow-lg pointer-events-auto md:w-1/2 lg:w-1/3 w-5/6" onclick="event.stopPropagation()">
                    <h2 class="text-xl font-bold mb-4">Event Details</h2>
                    <form id="eventDetailsForm">
                        <input type="hidden" id="eventIdDetails">
                        <div class="mb-4">
                            <label for="clientNameDetails" class="block text-gray-700">Client Name:</label>
                            <input type="text" id="clientNameDetails" class="w-full p-2 border rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label for="pickupLocationDetails" class="block text-gray-700">Pickup Location:</label>
                            <input type="text" id="pickupLocationDetails" class="w-full p-2 border rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label for="pickupTimeDetails" class="block text-gray-700">Pickup Time:</label>
                            <input type="time" id="pickupTimeDetails" class="w-full p-2 border rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label for="dropoffLocationDetails" class="block text-gray-700">Dropoff Location:</label>
                            <input type="text" id="dropoffLocationDetails" class="w-full p-2 border rounded-lg">
                        </div>
                        
                        <div class="mb-4">
                            <label for="startDateDetails" class="block text-gray-700">Start Date:</label>
                            <input type="date" id="startDateDetails" class="w-full p-2 border rounded-lg">
                        </div>

                        <div class="mb-4">
                            <label for="endDateDetails" class="block text-gray-700">End Date:</label>
                            <input type="date" id="endDateDetails" class="w-full p-2 border rounded-lg">
                        </div>

                        <div class="mb-4">
                            <label for="rate" class="block text-gray-700">Rate:</label>
                            <input type="text" id="rateDetails" class="w-full p-2 border rounded-lg">
                        </div>
                        <div class="flex justify-end">
                            <button type="button" id="deleteBtn" class="mr-2 px-4 py-2 bg-red-500 text-white rounded-lg">Delete</button>
                            <button type="button" id="closeDetailsBtn" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg">Close</button>
                            <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Submit</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Custom Confirmation Modal -->
            <div id="confirmDeleteModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center z-50 justify-center">
                <div class="modal-content bg-white p-6 m-6 rounded-lg shadow-lg pointer-events-auto md:w-1/2 lg:w-1/3 w-5/6">
                    <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
                    <p class="mb-4">Are you sure you want to delete this event?</p>
                    <div class="flex justify-end">
                        <button id="cancelDeleteBtn" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg">Cancel</button>
                        <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Custom Confirmation Modal for Saving Changes -->
            <div id="confirmSaveModal" class="modal hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center z-50 justify-center">
                <div class="modal-content bg-white p-6 m-6 rounded-lg shadow-lg pointer-events-auto md:w-1/2 lg:w-1/3 w-5/6">
                    <h2 class="text-xl font-bold mb-4">Confirm Save</h2>
                    <p class="mb-4">Are you sure you want to save your changes?</p>
                    <div class="flex justify-end">
                        <button id="cancelSaveBtn" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg">Cancel</button>
                        <button id="confirmSaveBtn" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                events: '/events',
                // Adjust content height to fit the screen
                contentHeight: 'auto',
                height: 'auto',  // Adjust the height to fit the container
                eventSourceSuccess: function(events) {
                    // Convert the object to an array of values
                    var eventsArray = Object.values(events);

                    if (Array.isArray(eventsArray)) {
                        eventsArray.forEach(event => {
                            var startDate = moment(event.start);
                            var endDate = moment(event.end);

                            if (endDate.isAfter(startDate)) {
                                event.end = moment(event.end, 'YYYY-MM-DD').add(1, 'days').format('YYYY-MM-DD');
                            }
                        });
                        return eventsArray;
                    } else {
                        console.error('Unexpected response format: events is not an array');
                        return []; // Return an empty array if the events are not in the expected format
                    }
                },

                dateClick: function(info) {
                    $('#eventModal').removeClass('hidden').addClass('block');
                    $('#startDate').val(info.dateStr);
                },
                eventClick: function(info) {
                    var eventObj = info.event;

                    let endDate = eventObj.endStr ? eventObj.endStr : eventObj.startStr;

                    if (moment(eventObj.endStr).isAfter(moment(eventObj.startStr))) {
                        endDate = moment(eventObj.endStr, 'YYYY-MM-DD').subtract(1, 'days').format('YYYY-MM-DD');
                    }

                    $('#clientNameDetails').val(eventObj.extendedProps.clientName);
                    $('#pickupLocationDetails').val(eventObj.extendedProps.pickupLocation);
                    $('#pickupTimeDetails').val(eventObj.extendedProps.pickupTime);
                    $('#dropoffLocationDetails').val(eventObj.extendedProps.dropoffLocation);
                    $('#startDateDetails').val(eventObj.startStr);
                    $('#endDateDetails').val(endDate);
                    $('#rateDetails').val(eventObj.extendedProps.rate);
                    $('#eventIdDetails').val(eventObj.id); // Populate the hidden input with the event ID

                    $('#detailsModal').removeClass('hidden').addClass('block');
                }
            });

            calendar.render();

            var eventIdToDelete = null;

            // Handle delete button click to show the custom confirmation modal
            $('#deleteBtn').on('click', function() {
                eventIdToDelete = $('#eventIdDetails').val(); // Get the event ID
                $('#confirmDeleteModal').removeClass('hidden').addClass('block');
            });

            // Handle cancellation of delete
            $('#cancelDeleteBtn').on('click', function() {
                $('#confirmDeleteModal').removeClass('block').addClass('hidden');
            });

            // Handle confirmation of delete
            $('#confirmDeleteBtn').on('click', function() {
                if (eventIdToDelete) {
                    // Send DELETE request to Laravel controller
                    $.ajax({
                        url: `/events/${eventIdToDelete}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include CSRF token for security
                        },
                        success: function(response) {
                            console.log('Event deleted successfully:', response);

                            // Remove the event from the calendar
                            var existingEvent = calendar.getEventById(eventIdToDelete);
                            if (existingEvent) {
                                existingEvent.remove();
                            }

                            // Close the modals
                            $('#confirmDeleteModal').removeClass('block').addClass('hidden');
                            $('#detailsModal').removeClass('block').addClass('hidden');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting event:', error);
                        }
                    });
                }
            });

            // Handle form submission for adding events
            $('#eventForm').on('submit', function(event) {
                event.preventDefault();

                var clientName = $('#clientName').val();
                var pickupLocation = $('#pickupLocation').val();
                var pickupTime = $('#pickupTime').val();
                var dropoffLocation = $('#dropoffLocation').val();
                var rate = $('#rate').val();
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                var eventId = Date.now().toString(); // Generates a unique ID based on the current timestamp

                let newEvent = {
                    id: eventId,
                    title: `${clientName}: ${pickupLocation} to ${dropoffLocation}`,
                    start: startDate,
                    end: endDate,
                    allDay: true,
                    clientName: clientName,
                    pickupLocation: pickupLocation,
                    pickupTime: pickupTime,
                    dropoffLocation: dropoffLocation,
                    rate: rate.replace(/\B(?=(\d{3})+(?!\d))/g, ',') // Ensure the rate is correctly formatted
                };

                if (moment(endDate).isAfter(moment(startDate))) {
                    endDate = moment(endDate, 'YYYY-MM-DD').add(1, 'days').format('YYYY-MM-DD');
                }

                calendar.addEvent(newEvent);

                // Send event data to Laravel controller
                $.ajax({
                    url: '/events', // Adjust to your Laravel route
                    type: 'POST',
                    data: newEvent,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include CSRF token for security
                    },
                    success: function(response) {
                        console.log('Event saved successfully:', response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error saving event:', error);
                    }
                });

                // Hide the modal and reset the form
                $('#eventModal').removeClass('block').addClass('hidden');
                $('#eventForm')[0].reset();
            });

            // Close buttons for modals
            $('#cancelBtn, #closeDetailsBtn').on('click', function() {
                $('#eventModal, #detailsModal').removeClass('block').addClass('hidden');
            });

            var eventIdToSave = null;

            // Handle form submission for editing events
            $('#submitBtn').on('click', function(event) {
                event.preventDefault(); // Prevent form from submitting immediately
                eventIdToSave = $('#eventIdDetails').val(); // Get the event ID
                $('#confirmSaveModal').removeClass('hidden').addClass('block'); // Show the save confirmation modal
            });

            // Handle cancellation of save
            $('#cancelSaveBtn').on('click', function() {
                $('#confirmSaveModal').removeClass('block').addClass('hidden');
            });

            // Handle confirmation of save
            $('#confirmSaveBtn').on('click', function() {
                if (eventIdToSave) {
                    var clientName = $('#clientNameDetails').val();
                    var pickupLocation = $('#pickupLocationDetails').val();
                    var pickupTime = $('#pickupTimeDetails').val();
                    var dropoffLocation = $('#dropoffLocationDetails').val();
                    var rate = $('#rateDetails').val();
                    var startDate = $('#startDateDetails').val();
                    var endDate = $('#endDateDetails').val();
                    var eventId = eventIdToSave; // Use the stored event ID

                    let updatedEvent = {
                        id: eventId,
                        title: `${clientName}: ${pickupLocation} to ${dropoffLocation}`,
                        start: startDate,
                        end: endDate,
                        allDay: true,
                        clientName: clientName,
                        pickupLocation: pickupLocation,
                        pickupTime: pickupTime,
                        dropoffLocation: dropoffLocation,
                        rate: rate.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                    };

                    if (moment(endDate).isAfter(moment(startDate))) {
                        endDate = moment(endDate, 'YYYY-MM-DD').add(1, 'days').format('YYYY-MM-DD');
                    }

                    var existingEvent = calendar.getEventById(eventId);
                    if (existingEvent) {
                        existingEvent.setProp('title', `${clientName}: ${pickupLocation} to ${dropoffLocation}`);
                        existingEvent.setStart(startDate);
                        existingEvent.setEnd(endDate);
                        existingEvent.setAllDay(true);
                        existingEvent.setExtendedProp('clientName', clientName);
                        existingEvent.setExtendedProp('pickupLocation', pickupLocation);
                        existingEvent.setExtendedProp('pickupTime', pickupTime);
                        existingEvent.setExtendedProp('dropoffLocation', dropoffLocation);
                        existingEvent.setExtendedProp('rate', rate);
                    }

                    $.ajax({
                        url: `/events/${eventId}`,
                        type: 'PUT',
                        data: updatedEvent,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            console.log('Event updated successfully:', response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error updating event:', error);
                        }
                    });

                    $('#confirmSaveModal').removeClass('block').addClass('hidden');
                    $('#detailsModal').removeClass('block').addClass('hidden');
                    $('#eventDetailsForm')[0].reset();
                }
            });

            // Close modals when clicking outside the content
            $('.modal').click(function() {
                $(this).removeClass('block').addClass('hidden');
            });

            // Calculate total rate based on inputs
            $('.input-cell').on("input propertychange", function() {
                var dieselPrice = parseFloat($('#diesel-price').val());
                var kmPerLiter = parseFloat($('#km-per-liter').val());
                var distance = parseFloat($('#distance').val());
                var idleFuelCost = parseFloat($('#idle-fuel-cost').val());
                var tollFees = parseFloat($('#toll-fees').val());
                var l300DailyRate = parseFloat($('#l300-daily-rate').val());
                var driverDailyRate = parseFloat($('#driver-daily-rate').val());
                var days = parseFloat($('#days').val());

                var fuelConsumed = distance / kmPerLiter;
                var fuelCost = fuelConsumed * dieselPrice;

                if ($('#exclude-fuel-cost').is(':checked')) {
                    fuelCost = 0;
                }

                if ($('#exclude-toll-fees').is(':checked')) {
                    tollFees = 0;
                }

                var totalDailyRate = l300DailyRate + driverDailyRate;
                var totalRate = (totalDailyRate * days) + fuelCost + idleFuelCost + tollFees;

                $('#fuel-consumed').val(fuelConsumed.toFixed(2));
                $('#fuel-cost').val(fuelCost.toFixed(2));
                $('#total-daily-rate').val(totalDailyRate.toFixed(2));
                $('#total-rate').val(totalRate.toFixed(2));
            });

            // Trigger recalculation on load
            $('.input-cell').trigger('input');

            // Toggle the rate calculator visibility on mobile view
            $('#toggleRateCalculator').on('click', function() {
                $('#rateCalculatorContent').toggleClass('hidden'); // Toggle visibility
                $('#toggleArrow').toggleClass('rotate-180'); // Rotate the arrow
            });
        });
    </script>
</body>
</html>
