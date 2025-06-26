<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons (optional) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Tempat Sampah</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }

        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            height: 100vh;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            overflow: auto;
        }

        .sidebar h2 {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #444;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .content {
            margin-left: 200px;
            padding: 20px;
            flex: 1;
        }

        .navbar {
            background-color: #2980b9;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .content {
                margin-left: 0;
            }

            .navbar {
                text-align: left;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Monitoring</h2>
        <a href="{{ url('/') }}">📊 Pemantauan Realtime</a>
        <a href="{{ url('/grafik') }}">📈 Pemantauan Grafik</a>
        <a class="nav-link" href="{{ route('riwayat') }}">Riwayat Monitoring</a>
        <a class="nav-link" href="{{ route('nonfuzzy') }}">Monitoring Non-Fuzzy</a>
        <a class="nav-link" href="{{ route('users.index') }}">Manajemen User</a>
        <a href="{{ url('/logout') }}">logout</a>
    </div>

    <div class="content">
        <div class="navbar">
            Aplikasi Monitoring Tempat Sampah
        </div>

        @yield('content')
    </div>

</body>
</html>
