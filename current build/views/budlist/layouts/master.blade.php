<!DOCTYPE html>
<html lang="en" class="{{ session('theme', 'auto') === 'dark' ? 'dark' : '' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BUDLIST @yield('title')</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/tw-elements/css/tw-elements.min.css" />
  <script src="https://cdn.tailwindcss.com/3.3.0"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <script>
    // Check if dark mode is enabled in localStorage
    if (localStorage.getItem('darkMode') === 'enabled') {
      document.documentElement.classList.add('dark');
    }
  </script>
</head>
<body>

  <nav class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center p-4 shadow-md">
    <!-- Left side: Return Button -->
    <div class="justify-self-start">
      @yield('returnButton')
    </div>

    <!-- Centered Title and Muted Text -->
    <div class="flex flex-col items-center min-w-0 text-center">
      <h1 class="text-xl font-bold whitespace-nowrap">@yield('title')</h1>
      <small class="text-sm text-gray-400 mt-1 whitespace-nowrap">@yield('budgetText')</small>
      @if (Request::is('budlist/loan/*'))
        <!-- Sort By Dropdown -->
        <div class="relative flex flex-wrap items-stretch">
          <label for="sort-by" class="mr-2">Sort by:</label>
          <select id="sort-by" class="relative m-0 block flex-auto rounded-e border border-solid border-neutral-200 bg-transparent bg-clip-padding px-3 py-[0.25rem] text-base font-normal leading-[1.6] text-surface outline-none transition duration-200 ease-in-out placeholder:text-neutral-500 focus:z-[3] focus:border-primary focus:shadow-inset focus:outline-none motion-reduce:transition-none dark:border-white/10 dark:text-white dark:placeholder:text-neutral-200 dark:focus:border-primary" onchange="sortBy(this.value)">
            <option value="date" class="dark:bg-surface-dark">Date</option>
            <option value="title" class="dark:bg-surface-dark">Title</option>
            <option value="amount" class="dark:bg-surface-dark">Amount</option>
          </select>
        </div>
      @endif
    </div>
  
    <!-- Right side: Edit Button and Dark Mode Toggle -->
    <div class="flex items-center space-x-4 justify-self-end">
      @yield('editButton')
      <button id="darkModeToggle" data-twe-ripple-init data-twe-ripple-color="light" class="text-xl rounded-full">
        <i id="iconSun" class="fas fa-sun hidden"></i>
        <i id="iconMoon" class="fas fa-moon"></i>
      </button>
    </div>
  </nav>

  

  @yield('content')
  
  <script>
    $(document).ready(function() {

      tailwind.config = {
        darkMode: "dark",
        theme: {
          fontFamily: {
            sans: ["Roboto", "sans-serif"],
            body: ["Roboto", "sans-serif"],
            mono: ["ui-monospace", "monospace"],
          },
        },
        corePlugins: {
          preflight: false,
        },
      };

      // Check if the current URL matches the pattern "budlist/loan/{id}"
      const pathPattern = /^\/budlist\/loan\/\d+$/; // Matches "/budlist/loan/{id}" where {id} is a number
      const currentPath = window.location.pathname;

      if (pathPattern.test(currentPath)) {
          const dropdown = $('#sort-by'); // Select the dropdown
          const urlParams = new URLSearchParams(window.location.search);
          const querySortBy = urlParams.get('sort');
          const savedSortBy = localStorage.getItem('sortBy');

          // Ensure the URL has a sort parameter before the page loads
          if (!querySortBy && savedSortBy) {
              urlParams.set('sort', savedSortBy);
              window.history.replaceState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
          }

          // Set the dropdown value based on the query parameter
          dropdown.val(querySortBy || savedSortBy);

          // Listen for changes to the dropdown
          dropdown.on('change', function () {
              const selectedValue = $(this).val();
              localStorage.setItem('sortBy', selectedValue);

              // Update URL with new sort value
              urlParams.set('sort', selectedValue);
              window.history.pushState({}, '', `${window.location.pathname}?${urlParams.toString()}`);

              // Reload to apply sorting
              location.reload();
          });
      }

      const darkModeToggle = $('#darkModeToggle');


      // Handle the dark mode toggle
      darkModeToggle.on('click', function() {
        // Toggle dark mode on the html element
        $('html').toggleClass('dark');

        // Save the current mode to localStorage for persistence
        if ($('html').hasClass('dark')) {
          localStorage.setItem('darkMode', 'enabled');
        } else {
          localStorage.removeItem('darkMode');
        }
      });
    });
  </script>

  @yield('js')

  <script src="https://cdn.jsdelivr.net/npm/tw-elements/js/tw-elements.umd.min.js"></script>
</body>
</html>
