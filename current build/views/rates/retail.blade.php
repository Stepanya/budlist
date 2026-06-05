<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Rates Table</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-4">
  <div class="container mx-auto">
    <div class="grid grid-cols-5 gap-4 ">
        <!-- Left Column -->
        <div class="col-span-4 grid grid-rows-3 gap-4 pr-4 border-r border-gray-300">
          <div class="grid grid-cols-3 gap-4">
            <!-- Origin Section -->
            <div>
              <label for="origin-regions" class="block text-gray-700 text-sm font-bold mb-2">Select Origin Region:</label>
              <select id="origin-regions" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Select Region</option>
              </select>
            </div>
            <div>
              <label for="origin-province" class="block text-gray-700 text-sm font-bold mb-2">Select Origin Province:</label>
              <select id="origin-province" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" disabled>
                <option value="">Select Province</option>
              </select>
            </div>
            <div>
              <label for="origin-cities" class="block text-gray-700 text-sm font-bold mb-2">Select Origin City:</label>
              <select id="origin-cities" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" disabled>
                <option value="">Select City</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <!-- Destination Section -->
            <div>
              <label for="destination-regions" class="block text-gray-700 text-sm font-bold mb-2">Select Destination Region:</label>
              <select id="destination-regions" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Select Region</option>
              </select>
            </div>
            <div>
              <label for="destination-province" class="block text-gray-700 text-sm font-bold mb-2">Select Destination Province:</label>
              <select id="destination-province" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" disabled>
                <option value="">Select Province</option>
              </select>
            </div>
            <div>
              <label for="destination-cities" class="block text-gray-700 text-sm font-bold mb-2">Select Destination City:</label>
              <select id="destination-cities" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" disabled>
                <option value="">Select City</option>
              </select>
            </div>
          </div>
        </div>
        <!-- Right Column -->
        <div class="grid grid-rows-3 gap-4 pl-4">
          <div>
            <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Weight (kg):</label>
            <input type="number" id="weight" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" min="0" step="0.01">
          </div>
          <div>
            <label for="declared-value" class="block text-gray-700 text-sm font-bold mb-2">Declared Value:</label>
            <input type="number" id="declared-value" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" min="0" step="0.01">
          </div>
          <div>
            <label for="cargo" class="block text-gray-700 text-sm font-bold mb-2">Cargo:</label>
            <input type="checkbox" id="cargo" class="form-checkbox h-5 w-5 text-blue-600">
          </div>
        </div>
      </div>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border-collapse">
        <thead class="bg-red-800 text-white">
          <tr>
            <th class="border px-4 py-2">Destination</th>
            <th class="border px-4 py-2">Product</th>
            <th class="border px-4 py-2">Size (In)</th>
            <th class="border px-4 py-2">Price</th>
            <th class="border px-4 py-2">Valuation Fee</th>
          </tr>
        </thead>
        <tbody id="ratesTable">
          <tr>
            <td colspan="5" class="text-center py-4">Select cities and enter weight to see rates</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <script>
    $(document).ready(function() {

      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

      function fetchRegions(selectElementId) {
        $.ajax({
          url: '/proxy/retail/GetRegions',
          method: 'POST',
          success: function(response) {
            response.ResponseData.Regions.forEach(region => {
              $(selectElementId).append(`<option value="${region.RegionId}">${region.RegionName}</option>`);
            });
          }
        });
      }

      // Fetch regions for origin and destination on page load
      fetchRegions('#origin-regions');
      fetchRegions('#destination-regions');

      function fetchProvinces(regionId, selectElementId) {
        if (regionId) {
          $.ajax({
            url: '/proxy/retail/GetProvinces',
            method: 'POST',
            data: { RegionId: regionId },
            success: function(response) {
              $(selectElementId).prop('disabled', false).empty().append('<option value="">Select Province</option>');
              response.ResponseData.Provinces.forEach(province => {
                $(selectElementId).append(`<option value="${province.Id}">${province.Name}</option>`);
              });
            }
          });
        } else {
          $(selectElementId).prop('disabled', true).empty().append('<option value="">Select Province</option>');
        }
      }

      function fetchCities(provinceId, selectElementId) {
        if (provinceId) {
          $.ajax({
            url: '/proxy/retail/GetCities',
            method: 'POST',
            data: { provinceId: provinceId },
            success: function(response) {
              $(selectElementId).prop('disabled', false).empty().append('<option value="">Select City</option>');
              response.ResponseData.Cities.forEach(city => {
                $(selectElementId).append(`<option value="${city.Id}">${city.Name}</option>`);
              });
            }
          });
        } else {
          $(selectElementId).prop('disabled', true).empty().append('<option value="">Select City</option>');
        }
      }

      // Fetch provinces when a region is selected for origin
      $('#origin-regions').on('change', function() {
        fetchProvinces($(this).val(), '#origin-province');
      });

      // Fetch provinces when a region is selected for destination
      $('#destination-regions').on('change', function() {
        fetchProvinces($(this).val(), '#destination-province');
      });
      
      // Fetch cities when a province is selected for origin
      $('#origin-province').on('change', function() {
        fetchCities($(this).val(), '#origin-cities');
      });

      // Fetch cities when a province is selected for destination
      $('#destination-province').on('change', function() {
        fetchCities($(this).val(), '#destination-cities');
      });

      // Fetch rates when cities and weight are selected
      $('#origin-cities, #destination-cities, #weight, #declared-value, #cargo').on('change', function() {
        const originCityId = $('#origin-cities').val();
        const destinationCityId = $('#destination-cities').val();
        const weight = $('#weight').val();
        const declaredValue = $('#declared-value').val();
        const cargo = $('#cargo').is(":checked");

        if (originCityId && destinationCityId && weight > 0) {
          $.ajax({
            url: '/proxy/retail/GetProductsV2',
            method: 'POST',
            data: { IsCargo: cargo },
            success: function(response) {
              const products = response.ResponseData.Products;
              $('#ratesTable').empty();
              products.forEach(product => {
                if (weight <= parseFloat(product.MaxWeight_Kg)) {
                  $.ajax({
                    url: '/proxy/retail/CalculatePriceV2',
                    method: 'POST',
                    data: {
                      OriginCityId: originCityId,
                      DestinationCityId: destinationCityId,
                      ProductId: product.ProductId,
                      Weight: weight,
                      DeclaredValue: declaredValue
                    },
                    success: function(priceResponse) {
                      $('#ratesTable').append(`
                        <tr>
                          <td class="border px-4 py-2">${$('#destination-cities option:selected').text()}</td>
                          <td class="border px-4 py-2">${product.ProductName}</td>
                          <td class="border px-4 py-2">${product.Size_Inch}</td>
                          <td class="border px-4 py-2">${priceResponse.ResponseData.Price.toFixed(2)}</td>
                          <td class="border px-4 py-2">${priceResponse.ResponseData.ValuationFee.toFixed(2)}</td>
                        </tr>
                      `);
                    }
                  });
                }
              });
            }
          });
        } else {
          $('#ratesTable').empty().append('<tr><td colspan="4" class="text-center py-4">Select cities and enter weight to see rates</td></tr>');
        }
      });
    });
  </script>
</body>
</html>
