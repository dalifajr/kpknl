<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $error }} | SSO KPKNL Palembang</title>

    <!-- Google Fonts: Outfit (Display/Headings) & Inter (Body/Labels) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --md-sys-color-primary: #3B5FE5;
            --md-sys-color-surface: #FBF8FF;
            --md-sys-color-surface-container-low: #F5F3FB;
            --md-sys-color-error-container: #FFDAD6;
            --md-sys-color-on-error-container: #410002;
            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--md-sys-color-surface);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }
        .error-card {
            background-color: var(--md-sys-color-surface-container-low);
            border: none;
            border-radius: 28px;
            box-shadow: 0 16px 40px rgba(27, 27, 33, 0.08);
            max-width: 480px;
            width: 100%;
            padding: 2.75rem 2rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="error-card">
        <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; background-color: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container);">
            <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2" style="font-family: var(--font-heading);">{{ $error }}</h4>
        <p class="text-muted fs-7 mb-4">{{ $message }}</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4 fw-bold py-2.5">
            <i class="fa-solid fa-house me-1"></i> Kembali ke Dashboard SSO
        </a>
    </div>

</body>
</html>

