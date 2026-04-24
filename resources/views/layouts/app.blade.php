<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Friday Fun League</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #007bff;
            --primary-dark: #004080;
            --surface: #ffffff;
            --surface-alt: #e9ecef;
            --surface-muted: #f8f9fa;
            --surface-border: #dee2e6;
            --page-bg: #f8f9fa;
            --text: #495057;
            --heading: #004080;
            --danger: #ff4d4d;
            --shadow: 0 6px 18px rgba(0, 64, 128, 0.08);
        }

        body {
            background: var(--page-bg);
            color: var(--text);
            font-family: 'Open Sans', sans-serif;
            min-block-size: 100vh;
        }

        .site-nav {
            background: var(--primary-dark);
            box-shadow: 0 2px 10px rgba(0, 64, 128, 0.16);
        }

        .page-shell {
            max-inline-size: 1200px;
            margin: 0 auto;
            padding: 24px 18px 40px;
        }

        .navbar-brand {
            color: #ffffff;
            font-size: 1.55rem;
            font-weight: 700;
        }

        .navbar-subtitle {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
            font-weight: 600;
        }

        .alert {
            border-radius: 4px;
            box-shadow: var(--shadow);
        }

        .alert-success {
            border-color: #cdebd7;
            background: #eefaf2;
            color: #1f6f43;
        }

        .alert-danger {
            border-color: #ffd0d0;
            background: #fff3f3;
            color: #b4232f;
        }

        .card {
            border: 1px solid var(--surface-border);
            border-radius: 8px;
            box-shadow: var(--shadow);
            background: var(--surface);
        }

        .card-header {
            background: var(--surface-alt);
            border-block-end: 1px solid var(--surface-border);
            padding: 1rem 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        .feature-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-block-end: 1rem;
        }

        .section-heading {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--heading);
        }

        .table {
            margin-block-end: 0;
            font-size: 0.95rem;
        }

        .table thead th {
            padding: 0.85rem 1rem;
            font-size: 0.84rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            vertical-align: middle;
            background: var(--primary-dark);
            color: #ffffff;
            border: 0;
        }

        .table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-color: var(--surface-border);
        }

        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background: var(--surface-muted);
        }

        .table-hover > tbody > tr:hover > * {
            background: #eef5ff;
        }

        .form-label {
            font-weight: 600;
            color: var(--heading);
            margin-block-end: 0.4rem;
        }

        .form-control,
        .form-select {
            min-block-size: 44px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.14);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: var(--danger);
        }

        .btn {
            min-block-size: 44px;
            border-radius: 4px;
            font-weight: 600;
        }

        .btn-sm {
            min-block-size: auto;
        }

        .btn-primary,
        .btn-success {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-success:hover,
        .btn-success:focus {
            background: #0069d9;
            border-color: #0062cc;
        }

        .btn-outline-secondary {
            color: var(--text);
            border-color: #adb5bd;
            background: var(--surface);
        }

        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            color: var(--heading);
            border-color: #6c757d;
            background: var(--surface-alt);
        }

        .btn-outline-danger {
            color: var(--danger);
            border-color: var(--danger);
            background: var(--surface);
        }

        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            color: #c73737;
            background: #fff2f2;
            border-color: #c73737;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .table-actions .btn {
            padding: 0.45rem 0.8rem;
            font-size: 0.88rem;
        }

        .score-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-inline-size: 72px;
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            background: var(--primary);
            color: #ffffff;
            font-weight: 700;
        }

        .player-line {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .player-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            inline-size: 30px;
            block-size: 30px;
            border-radius: 999px;
            background: #dfefff;
            color: var(--primary-dark);
            font-size: 0.95rem;
            font-weight: 700;
        }

        .muted-note {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .filter-bar {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 0.85rem;
            align-items: end;
        }

        .stat-tile {
            border: 1px solid var(--surface-border);
            border-radius: 8px;
            padding: 1.2rem;
            box-shadow: var(--shadow);
            background: var(--surface);
        }

        .tile-blue {
            background: #e7f1ff;
        }

        .tile-navy {
            background: #dfe9f5;
        }

        .tile-light {
            background: #ffffff;
        }

        .tile-gray {
            background: #e9ecef;
        }

        .stat-label {
            font-size: 0.92rem;
            font-weight: 600;
            color: #6c757d;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--heading);
            margin-block-start: 0.45rem;
        }

        .chart-card .card-body {
            min-block-size: 320px;
        }

        .chart-wrap {
            position: relative;
            block-size: 260px;
        }

        .chart-summary {
            margin-block-start: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .chart-summary div + div {
            margin-block-start: 0.35rem;
        }

        .modal-content {
            border-radius: 8px;
            border: 1px solid var(--surface-border);
        }

        .modal-header {
            background: var(--surface-alt);
            border-block-end: 1px solid var(--surface-border);
        }

        .empty-state {
            padding: 1.5rem 1rem;
            text-align: center;
            color: #6c757d;
        }

        .field-error {
            margin-block-start: 0.35rem;
            color: var(--danger);
            font-size: 0.875rem;
        }

        @media (max-inline-size: 991.98px) {
            .page-shell {
                padding-inline: 12px;
            }

            .filter-bar {
                grid-template-columns: 1fr;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }

            .navbar-subtitle {
                font-size: 0.88rem;
            }
        }
    </style>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>