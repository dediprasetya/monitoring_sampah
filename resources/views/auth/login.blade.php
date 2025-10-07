<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring Sampah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url('{{ asset('assets/images/bg-login.jpg') }}') no-repeat center center fixed;
            background-size: cover;
        }

        .login-container {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.6);
            max-width: 400px;
            margin: 8% auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .form-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-text-link {
            font-size: 0.9rem;
        }

        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 38px;
            color: #666;
        }

        .position-relative {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h4 class="form-title">Sistem Monitoring Tempat Sampah</h4>
            <h4 class="form-title"></h4>

            {{-- Tampilkan pesan error --}}
            @if ($errors->has('login'))
                <div class="alert alert-danger">
                    {{ $errors->first('login') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            @if($error != $errors->first('login'))
                                <li>{{ $error }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Pengguna</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" required>
                    <span class="password-toggle" onclick="togglePassword()">👁️</span>
                </div>

                <div class="mb-3 d-flex justify-content-between">
                    <a href="#" class="form-text-link">Lupa Password?</a>
                    <a href="#" class="form-text-link">Registrasi Admin</a>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Masuk</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            password.type = password.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
