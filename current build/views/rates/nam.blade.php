<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>NAM Rates</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-4">
  <div class="container mx-auto">
    <div class="grid grid-cols-5 gap-4 ">
      <form class="col-span-5 grid grid-rows-2 gap-4 pr-4 mb-4">
        <div class="grid grid-cols-4 gap-4 ">
          <!-- Origin Section -->
          <div>
              <label for="origin-country" class="block text-gray-700 text-sm font-bold mb-2">Select Origin Country:</label>
              <select id="origin-country" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                  <option value="" selected>Select Country</option>
                  <option value="USA">USA</option>
                  <option value="CAN">Canada</option>
              </select>
          </div>
          <div>
              <label for="origin-zipcode" class="block text-gray-700 text-sm font-bold mb-2">Origin Zip Code:</label>
              <input id="origin-zipcode" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" />
          </div>
          <div>
            <label for="origin-branch" class="block text-gray-700 text-sm font-bold mb-2">Branch Code:</label>
            <input id="origin-branch" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" readonly />
          </div>
          <div>
            <label for="destination" class="block text-gray-700 text-sm font-bold mb-2">Destination:</label>
            <select id="destination" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                <option value="" selected>Select Destination</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-6 gap-4">
          <!-- Package Section -->
          <div>
            <label for="products" class="block text-gray-700 text-sm font-bold mb-2">Select Product:</label>
            <select id="products" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
              <option value="">Select Product</option>
            </select>
          </div>
          <div>
            <label for="length" class="block text-gray-700 text-sm font-bold mb-2">Length:</label>
            <input id="length" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" \>
          </div>
          <div>
            <label for="width" class="block text-gray-700 text-sm font-bold mb-2">Width:</label>
            <input id="width" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" \>
          </div>
          <div>
            <label for="height" class="block text-gray-700 text-sm font-bold mb-2">Height:</label>
            <input id="height" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" \>
          </div>
          <div>
            <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Weight:</label>
            <input id="weight" class="block w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 rounded shadow leading-tight focus:outline-none focus:shadow-outline" \>
          </div>
          <!-- Reset button on the far right -->
          <div class=" flex justify-end items-end">
            <button id="resetForm" type="reset" class="bg-red-800 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
              Reset
            </button>
          </div>
        </div>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border-collapse">
        <thead class="bg-red-800 text-white">
          <tr>
            <th class="border px-4 py-2">Destination</th>
            <th class="border px-4 py-2">Product</th>
            <th class="border px-4 py-2">Dimensions (L x W x H)</th>
            <th class="border px-4 py-2">Weight</th>
            <th class="border px-4 py-2">Freight</th>
            <th class="border px-4 py-2">Volume Weight</th>
            <th class="border px-4 py-2">Cubic Feet</th>
            <th class="border px-4 py-2">Actual Weight</th>
          </tr>
        </thead>
        <tbody id="ratesTable">
          <tr>
            <td colspan="8" class="text-center py-4">Please fill out all fields to see rates</td>
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

      function fetchBranch(zipCode, countryCode) {
        $.ajax({
        url: '/proxy/nam/GetOffice',
        method: 'GET',
        data: { 
            zip: zipCode,
            countryCode: countryCode
            },
        success: function(response) {
                $("#origin-branch").val(response).trigger('change');
            }
        });
      }

      function fetchProducts(selectElementId) {
        $.ajax({
          url: '/proxy/nam/GetProductCodes',
          method: 'GET',
          success: function(response) {
            response.forEach(product => {
              $(selectElementId).append(`<option value="${product}">${product}</option>`);
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

      fetchProducts('#products');
      fetchDestination('#destination');

      $('#origin-country, #origin-zipcode').on('change', function() {
        const originCountryCode = $('#origin-country').val();
        const originZipcode = $('#origin-zipcode').val();

        if (originCountryCode && originZipcode) {
            fetchBranch(originZipcode, originCountryCode)
        } else {
            $("#origin-branch").empty();
        }
      });
  
      // Fetch rates when branch code, destination, product, length, width, height, and weight contain a value
      $('#origin-branch, #destination, #products, #length, #width, #height, #weight').on('change', function() {
        const branchCode = $('#origin-branch').val();
        const destination = $('#destination').val();
        const productCode = $('#products').val();
        const length = $('#length').val();
        const width = $('#width').val();
        const height = $('#height').val();
        const weight = $('#weight').val();

        // Ensure all required fields have values
        if (branchCode && destination && productCode && length > 0 && width > 0 && height > 0 && weight > 0) {
          $.ajax({
            url: '/proxy/nam/GetFreight',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
              Destination: destination,
              BranchCode: branchCode,
              ProductCode: productCode,
              Length: length,
              Width: width,
              Height: height,
              ActualWeight: weight
            }),
            success: function(response) {
              const freight = parseFloat(response.Freight);
              const volumeWeight = parseFloat(response.VolumeWeight);
              const cubicFeet = parseFloat(response.CubicFeet);
              const actualWeight = parseFloat(response.Weight);

              $('#ratesTable').empty().append(`
                <tr>
                  <td class="border px-4 py-2">${$('#destination option:selected').text()}</td>
                  <td class="border px-4 py-2">${productCode}</td>
                  <td class="border px-4 py-2">${length} x ${width} x ${height}</td>
                  <td class="border px-4 py-2">${weight}</td>
                  <td class="border px-4 py-2">${freight.toFixed(2)}</td>
                  <td class="border px-4 py-2">${volumeWeight.toFixed(2)}</td>
                  <td class="border px-4 py-2">${cubicFeet.toFixed(2)}</td>
                  <td class="border px-4 py-2">${actualWeight.toFixed(2)}</td>
                </tr>
              `);
            }
          });
        } else {
          $('#ratesTable').empty().append('<tr><td colspan="8" class="text-center py-4">Please fill out all fields to see rates</td></tr>');
        }
      });
    });
  </script>
</body>
</html>
