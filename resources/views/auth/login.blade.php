<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pendataan Advertorial</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            padding: 1rem;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            padding: 2.25rem;
            width: 100%;
            max-width: 420px;
        }

        .login-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: #1e3a5f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 1rem;
        }

        .form-label { font-weight: 600; font-size: 0.87rem; }
        .form-control { border-radius: 8px; border: 1px solid #e2e8f0; padding: 0.65rem 0.85rem; }
        .form-control:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49,130,206,0.12); }
        .input-group .btn { border: 1px solid #e2e8f0; border-radius: 0 8px 8px 0; background: #fff; color: #718096; }

        .btn-login {
            background: #1e3a5f;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.7rem;
            width: 100%;
        }

        .btn-login:hover { background: #2c5282; color: #fff; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-logo"><i class="bi bi-newspaper"></i></div>
        <h4 class="fw-bold text-center mb-1">Pendataan Advertorial</h4>
        <p class="text-muted text-center small mb-4">Silakan masuk untuk melanjutkan</p>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    <button type="button" class="btn" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-login">Masuk</button>
        </form>
    </div>

    <script>
        const pwd = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');

        document.getElementById('togglePassword').addEventListener('click', function () {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>
</html>