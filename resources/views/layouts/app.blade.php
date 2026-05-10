<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Friday Fun League</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

<a href="#main-content" class="skip-link">Skip to main content</a>

<nav class="navbar navbar-dark site-nav" aria-label="Main navigation">
    <div class="page-shell d-flex justify-content-between align-items-center py-0">
        <a class="navbar-brand" href="{{ url('/') }}">Friday Fun League</a>
        <span class="navbar-subtitle d-none d-md-inline">Tournament Dashboard</span>
    </div>
</nav>

<main id="main-content" class="page-shell">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-app-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-app-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')

</body>
</html>