<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Kids Learning</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-card h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }
        .login-card p.subtitle {
            color: #888;
            font-size: 14px;
            margin-bottom: 28px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group .error {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 4px;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .remember-row label { font-size: 13px; color: #666; }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; }
        .alert-error {
            background: #fed7d7;
            color: #c53030;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>🔐 Admin Portal</h1>
        <p class="subtitle">KiddoQuest CBC — Content & Mission Management</p>

        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="form-group">
                <label for="email">Admin Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn-login">Sign In to Admin Dashboard</button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 13px; display: flex; flex-direction: column; gap: 8px;">
            @if(\App\Models\Admin::count() === 0)
                <a href="{{ route('admin.setup') }}" style="color: #ed8936; text-decoration: none; font-weight: 700;">⚡ Initial Setup: Create Super Admin Account →</a>
            @endif
            <a href="{{ url('/') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">← Back to KiddoQuest Home</a>
        </div>
    </div>
</body>
</html>