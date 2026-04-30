<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Friday Fun League</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<nav class="navbar navbar-dark site-nav">
    <div class="page-shell d-flex justify-content-between align-items-center py-0">
        <a class="navbar-brand" href="{{ url('/') }}">Friday Fun League</a>
        <span class="navbar-subtitle d-none d-md-inline">Tournament Dashboard</span>
    </div>
</nav>

<div class="page-shell">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @yield('content')
</div>

<div class="toast-container position-fixed top-0 end-0 p-3 app-toast-stack">
    <div id="app-toast" class="toast app-toast border-0" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex align-items-center">
            <div class="toast-body d-flex align-items-center gap-2">
                <span class="status-icon" aria-hidden="true">
                    <x-icon name="check" />
                </span>
                <span id="app-toast-message">Action completed.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>


@stack('scripts')

</body>
</html>