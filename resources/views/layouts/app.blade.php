<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Tempat Sampah</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        #sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: white;
            transition: transform 0.3s ease;
            z-index: 1001;
        }

        #sidebar h5 {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #444;
        }

        #sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
        }

        #sidebar a:hover, #sidebar a.active {
            background-color: #34495e;
        }

        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: #2980b9;
            color: white;
            z-index: 1002;
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white;
        }

        .content-area {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
            background-color: #f5f5f5;
        }

        /* Responsive Sidebar (slide from left) */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -220px;
                height: 100%;
                transform: translateX(0);
            }

            #sidebar.active {
                left: 0;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
            }

            .overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar -->
    <div id="sidebar">
        <h5>🗑️ Monitoring</h5>
        <a href="{{ route('dashboard') }}">📊 Pemantauan Realtime</a>
        <a href="{{ route('grafik') }}">📈 Grafik</a>
        <a href="{{ route('riwayat') }}">🕓 Riwayat</a>

        @if(auth()->user()->role === 'admin')
            <a href="{{ route('nonfuzzy') }}">🧠 Non-Fuzzy</a>
            <a href="{{ route('users.index') }}">👤 Manajemen User</a>
            <a href="{{ route('export.excel') }}">📥 Export Excel</a>
        @endif

        <a href="{{ route('logout') }}" class="text-danger">🚪 Logout</a>
    </div>

    <!-- Main content -->
    <div class="main">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <button class="btn btn-outline-light me-2 d-md-none" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand" href="#">Monitoring Sampah</a>

                <div class="ms-auto">
                    @auth
                        <span class="text-white me-3"><i class="bi bi-person-circle"></i> {{ auth()->user()->name }}</span>
                        <a class="btn btn-outline-light btn-sm" href="{{ route('logout') }}">Logout</a>
                    @endauth
                </div>
            </div>
        </nav>

        <div class="content-area">
            @yield('content')
        </div>
    </div>
</div>

<!-- Bootstrap & Sidebar Toggle Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleBtn = document.getElementById('toggleSidebar');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('show');
    });
</script>
</body>
</html>
