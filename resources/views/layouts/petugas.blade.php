<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Petugas</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 200px;
            background: #f2f2f2;
            height: 100vh;
            position: fixed;
            padding: 20px;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        .sidebar a {
            display: block;
            margin: 10px 0;
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Menu Petugas</h3>
        <a href="{{ url('/') }}">Pemantauan Realtime</a>
        <a href="{{ url('/grafik') }}">Grafik Volume</a>
        <a href="{{ url('/riwayat') }}">Riwayat Monitoring</a>
        <a href="{{ route('logout') }}">Logout</a>
    </div>
    <div class="content">
        @yield('content')
    </div>
</body>
</html>
