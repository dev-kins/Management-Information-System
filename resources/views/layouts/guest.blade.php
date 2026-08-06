<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'LSHS OMIS'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --guest-bg: linear-gradient(180deg, #edf7ef 0%, #f8f9fa 100%);
            --guest-brand: #014421;
            --guest-card: rgba(255, 255, 255, 0.96);
            --guest-border: rgba(1, 68, 33, 0.08);
        }
        body {
            background: var(--guest-bg);
            font-family: 'Segoe UI', sans-serif;
            color: #163020;
            min-height: 100vh;
        }
        .navbar {
            background-color: var(--guest-brand);
            color: white;
            box-shadow: 0 10px 30px rgba(1, 68, 33, 0.18);
        }
        .navbar .navbar-brand {
            color: white;
            font-weight: bold;
        }
        .navbar .nav-link {
            color: white;
        }
        .container-boxed {
            max-width: 960px;
            margin: auto;
            padding: 40px 16px 56px;
        }
        .guest-shell {
            background: var(--guest-card);
            border: 1px solid var(--guest-border);
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        @media (max-width: 576px) {
            .container-boxed {
                padding-top: 24px;
            }

            .guest-shell {
                border-radius: 18px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg px-4">
        <div class="container-fluid justify-content-between">
<a class="navbar-brand d-flex align-items-center" href="{{ route('login.student') }}">
    <img src="{{ asset('images/logo.jpg') }}" alt="CvSU" style="width:54px; border-radius:6px; margin-right:8px;">
    <span>CvSU Portal</span>
</a>
            {{-- <a class="nav-link" href="{{ route('enroll.form') }}">Not enrolled yet? Enroll now!</a> --}}
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container-boxed">
        <main class="guest-shell" role="main">
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

</body>
</html>
