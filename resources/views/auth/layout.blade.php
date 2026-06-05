<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Budlist')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    {{-- Apply the saved theme before first paint to avoid a flash of the wrong theme. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('budlist-theme');
                if (t !== 'light' && t !== 'dark') {
                    t = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    <link href="{{ asset('vendor/css/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v=4" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="bg-glows" aria-hidden="true">
        <span class="glow glow-1"></span>
        <span class="glow glow-2"></span>
        <span class="glow glow-3"></span>
    </div>

    <main class="auth-wrap">
        <div class="auth-card glass">
            <div class="auth-brand">
                <span class="brand-mark">₱</span>
                <span class="brand-name">Budlist</span>
            </div>

            <h1 class="auth-title">@yield('heading')</h1>
            @hasSection('subtitle')
                <p class="auth-sub">@yield('subtitle')</p>
            @endif

            @if (session('status'))
                <div class="auth-note">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('form')
        </div>

        @hasSection('footer')
            <p class="auth-foot">@yield('footer')</p>
        @endif
    </main>
</body>
</html>
