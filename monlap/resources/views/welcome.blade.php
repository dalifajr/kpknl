<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MonLap - Sistem Monitoring Laporan</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background-color: #F5F5F5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .login-card i {
            font-size: 64px;
            color: var(--primary);
            margin-bottom: 16px;
        }
        .login-card h1 {
            margin: 0 0 8px 0;
            color: var(--text-primary);
        }
        .login-card p {
            color: var(--text-secondary);
            margin-bottom: 32px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <i class="material-icons">assignment_turned_in</i>
        <h1>MonLap</h1>
        <p>Sistem Monitoring & Pelaporan Tugas KPKNL Palembang</p>
        


        <a href="{{ route('auth.redirect') }}" class="btn btn-primary ripple-surface" style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 12px; font-size: 16px; box-sizing: border-box; height: 48px;">
            <i class="material-icons" style="font-size: 20px; margin-right: 8px; color: white;">login</i>
            Login via SSO
        </a>
    </div>
    <!-- Global Toast Notification (Android 9 Style) -->
    @if(session('success') || session('error') || $errors->any())
    <div id="androidToast" class="android-toast">
        @if(session('success'))
            <i class="material-icons" style="color: #81C784;">check_circle</i>
            <span>{{ session('success') }}</span>
        @elseif(session('error'))
            <i class="material-icons" style="color: #E57373;">error</i>
            <span>{{ session('error') }}</span>
        @elseif($errors->any())
            <i class="material-icons" style="color: #E57373;">error</i>
            <span>{{ $errors->first() }}</span>
        @endif
    </div>
    
    <style>
        .android-toast {
            position: fixed;
            bottom: 32px;
            left: 32px;
            background-color: #323232;
            color: #FFFFFF;
            padding: 14px 24px;
            border-radius: 4px;
            box-shadow: 0 3px 5px -1px rgba(0,0,0,.2), 0 6px 10px 0 rgba(0,0,0,.14), 0 1px 18px 0 rgba(0,0,0,.12);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            opacity: 0;
            transform: translateY(20px);
            animation: slideUpFade 0.3s forwards, slideDownFade 0.3s forwards 4s;
        }

        @keyframes slideUpFade {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDownFade {
            to { opacity: 0; transform: translateY(20px); visibility: hidden; }
        }
    </style>
    @endif
</body>
</html>
