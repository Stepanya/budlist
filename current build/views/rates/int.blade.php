<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>International Rates</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-4 flex items-center justify-center min-h-screen">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
    <h2 class="text-2xl font-semibold text-center mb-4 bg-red-800 text-white py-2 rounded-md">International Rates Calculator</h2>
    
    <!-- Origin Country and Origin City -->
    <div class="mb-4 flex space-x-4">
      <div class="w-1/2">
        <label for="origin-country" class="block text-sm font-medium text-gray-700">Origin Country</label>
        <select id="origin-country" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          <option value="" selected disabled>Please select a country</option>
        </select>
      </div>
      <div class="w-1/2">
        <label for="origin-city" class="block text-sm font-medium text-gray-700">Origin City</label>
        <select id="origin-city" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <!-- Options go here -->
        </select>
      </div>
    </div>

    <!-- Shipment Mode and Branch-->
    <div class="mb-4 flex space-x-4">
      <div class="w-1/2">
        <label for="shipment-mode" class="block text-sm font-medium text-gray-700">Shipment Mode</label>
        <select id="shipment-mode" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          <!-- Options go here -->
        </select>
      </div>
      <div class="w-1/2">
        <label for="branch" class="block text-sm font-medium text-gray-700">Branch</label>
        <select id="branch" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <!-- Options go here -->
        </select>
      </div>
    </div>

    <!-- Products and Destination Region (Philippines)-->
    <div class="mb-4 flex space-x-4">
      <div class="w-1/2">
        <label for="products" class="block text-sm font-medium text-gray-700">Product</label>
        <select id="products" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          <!-- Options go here -->
        </select>
      </div>
      <div class="w-1/2">
        <label for="destination-region" class="block text-sm font-medium text-gray-700">Destination Region (Philippines)</label>
        <select id="destination-region" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          <option value="" selected disabled>Please select a region</option>
        </select>
      </div>
    </div>

    <!-- Weight -->
    <div class="mb-4 flex space-x-4">
      <div class="w-1/2">
        <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
        <input type="text" id="weight" value=0.5 class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
      </div>
      <div class="w-1/2">
      </div>
    </div>
    
    <!-- Submit and Reset Button -->
    <div class="mb-4 flex space-x-4">
      <div class="w-1/3">
        <button id="reset-btn" class="w-full px-4 py-2 bg-gray-200 text-slate-950 text-sm font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
          Reset
        </button>
      </div>
      <div class="w-2/3">
        <button id="calculate-btn" class="w-full px-4 py-2 bg-red-800 text-white text-sm font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-800">
          Calculate
        </button>
      </div>
    </div>

    <!-- Price -->
    <div class="text-center">
      <div>
        <span class="block text-sm font-medium text-gray-700">The Rate is</span>
        <span id="price" class="text-4xl">0</span>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    $(document).ready(function() {

      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

      function showErrorPopup(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: message,
            button: {
              text: "OK",
            },
        });
      }

      function fetchCountries() {
        $.ajax({
          url: '/proxy/int/GetCountries',
          method: 'POST',
          success: function(response) {
            response.ResponseData.Countries.forEach(item => {
              $("#origin-country").append(`<option value="${item.CountryId}">${item.CountryName}</option>`);
            });
          }
        });
      }

      function fetchRegions() {
        $.ajax({
          url: '/proxy/int/GetPHRegions',
          method: 'POST',
          success: function(response) {
            response.ResponseData.Regions.forEach(item => {
              $("#destination-region").append(`<option value="${item.RegionId}">${item.RegionName}</option>`);
            });
          }
        });
      }

      function fetchCities(countryId, selectElementId) {
        $.ajax({
          url: '/proxy/int/GetCities',
          method: 'POST',
          data: { "CountryId": countryId },
          success: function(response) {
            const options = response.ResponseData.Cities.map(item => 
              `<option value="${item.CityId}">${item.CityName}</option>`
            );

            const defaultOption = '<option value="" selected disabled>Please select a city</option>';
            
            $(selectElementId).html(defaultOption + options.join(''));
          }
        });
      }

      function fetchShipmentModes(countryId, selectElementId) {
        $.ajax({
          url: '/proxy/int/ShipmentModes',
          method: 'POST',
          data: { "CountryId": countryId },
          success: function(response) {
            const options = response.ResponseData.ShipmentModes.map(item => 
              `<option value="${item.ShipmentModeId}">${item.ShipmentModeName}</option>`
            );

            const defaultOption = '<option value="" selected disabled>Please select a shipment mode</option>';
            
            $(selectElementId).html(defaultOption + options.join(''));
          }
        });
      }

      async function fetchBranch(shipmentModeId, cityId) {
        return new Promise((resolve, reject) => {
          $.ajax({
            url: '/proxy/int/GetDefaultBranch',
            method: 'POST',
            data: { 
              CityId: cityId,
              ShipmentModeId: shipmentModeId
            },
            success: function(response) {
              const branch = response.ResponseData.Branch;
              if (branch) {
                $("#branch").html(`<option value="${branch.BranchId}">${branch.BranchName}</option>`);
                resolve(branch.BranchId);
              } else {
                resolve(null);
              }
            },
            error: function(xhr, status, error) {
              reject(error);
            }
          });
        });
      }

      function fetchProducts(branchId, shipmentMode) {
        $.ajax({
          url: '/proxy/int/GetProducts',
          method: 'POST',
          data: {
            BranchId: branchId,
            ShipmentModeId: shipmentMode
          },
          success: function(response) {
            response.ResponseData.Products.forEach(item => {
              $("#products").append(`<option value="${item.ProductId}">${item.ProductName}</option>`);
            });
          }
        });
      }

      function fetchDestination(selectElementId) {
        $.ajax({
          url: '/proxy/nam/GetAllProvinces',
          method: 'GET',
          success: function(response) {
            response.forEach(destination => {
              $(selectElementId).append(`<option value="${destination.Destination}">${destination.Province}</option>`);
            });
          }
        });
      }

      $('#origin-country').on('change', function() {
        fetchCities($(this).val(), '#origin-city');
      });

      $('#origin-country').on('change', function() {
        fetchShipmentModes($(this).val(), '#shipment-mode');
      });

      $('#shipment-mode, #origin-city').on('change', async function() {

        const shipmentMode = $("#shipment-mode").val(); 
        const cityId = $("#origin-city").val()

        if (shipmentMode && cityId) {
          const branchId = await fetchBranch(shipmentMode, cityId);
          if (branchId) {
            fetchProducts(branchId, shipmentMode)
          }
        }
      });

      fetchCountries()
      fetchRegions()

      // Fetch rates when calculate button is clicked
      $('#calculate-btn').on('click', function() {
        const branchId = $('#branch').val();
        const destinationRegionId = $('#destination-region').val();
        const shipmentModeId = $('#shipment-mode').val();
        const productId = $('#products').val();
        const weight = $('#weight').val();

        // Ensure all required fields have values
        if (
            branchId && 
            destinationRegionId && 
            shipmentModeId && 
            productId &&
            weight
        ) {

          const payload = {
              BranchId: parseInt(branchId, 10),
              DestinationRegionId: parseInt(destinationRegionId, 10),
              ShipmentModeId: parseInt(shipmentModeId, 10),
              ProductId: parseInt(productId, 10),
              Weight: parseFloat(weight),
              Dimension: {
                  Length: 0,
                  Width: 0,
                  Height: 0
              },
              Amount: 0
          };

          $.ajax({
            url: '/proxy/int/CalculatePrice',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
              $("#price").text(response.ResponseData.Price)
            },
            error: function(xhr, status, error) {
              showErrorPopup(error)
            }
          });
        } else {
          showErrorPopup("Please fill out all fields to see the rate")
        }
      });

      $('#reset-btn').on('click', function() {
        $("#origin-country").prop('selectedIndex',0);
        $("#origin-city").empty().append([]);
        $("#shipment-mode").empty().append([]);
        $("#branch").empty().append([]);
        $("#products").empty().append([]);
        $("#destination-region").prop('selectedIndex',0);
        $("#weight").val(0.5);
        $("#price").val("0");
      });

    });
  </script>
</body>
</html>
