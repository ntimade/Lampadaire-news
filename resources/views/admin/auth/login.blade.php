<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Connexion &mdash; Le Lampadaire</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('admin/assets/css/components.css') }}">

  <style>
    :root {
      --brand: #f0740b;
      --brand-dark: #1a0f06;
    }

    * { box-sizing: border-box; }

    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .auth-wrapper {
      min-height: 100vh;
      display: flex;
      flex-wrap: wrap;
    }

    /* ---------- Left visual panel ---------- */
    .auth-visual {
      position: relative;
      flex: 1 1 56%;
      min-height: 340px;
      overflow: hidden;
      background:
        radial-gradient(circle at 20% 15%, rgba(255,154,61,0.35), transparent 55%),
        linear-gradient(150deg, #2b1400 0%, #6e2f00 42%, var(--brand) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px 32px;
    }

    .auth-visual::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(0deg, rgba(0,0,0,0.35), transparent 45%);
      pointer-events: none;
    }

    .watermark {
      position: absolute;
      color: #fff;
      opacity: 0.10;
      pointer-events: none;
      z-index: 0;
      filter: drop-shadow(0 0 25px rgba(0,0,0,0.25));
    }

    .watermark-mic {
      font-size: 220px;
      top: 8%;
      left: -40px;
      animation: floatY 7s ease-in-out infinite;
    }

    .watermark-news {
      font-size: 150px;
      bottom: 6%;
      left: 8%;
      opacity: 0.09;
      animation: floatY 9s ease-in-out infinite 1.2s;
    }

    .watermark-quote {
      font-size: 110px;
      top: 12%;
      right: 6%;
      opacity: 0.09;
      animation: floatY 8s ease-in-out infinite 0.5s;
    }

    .watermark-cameroon {
      right: -20px;
      bottom: -30px;
      width: 300px;
      height: auto;
      opacity: 0.16;
      filter: brightness(0) invert(1);
      animation: driftRotate 22s ease-in-out infinite;
      transform-origin: 60% 60%;
    }

    @keyframes floatY {
      0%, 100% { transform: translateY(0) rotate(-2deg); }
      50% { transform: translateY(-22px) rotate(3deg); }
    }

    @keyframes driftRotate {
      0%, 100% { transform: rotate(-2deg) scale(1); }
      50% { transform: rotate(2deg) scale(1.04); }
    }

    .brand-content {
      position: relative;
      z-index: 1;
      max-width: 420px;
      color: #fff;
      text-align: center;
    }

    .brand-badge {
      width: 84px; height: 84px; border-radius: 22px;
      background: linear-gradient(135deg, #ffb066, var(--brand));
      color: #fff; display: flex; align-items: center; justify-content: center;
      font-size: 1.9rem; font-weight: 800; margin: 0 auto 22px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.35);
      letter-spacing: 0.5px;
    }
    .brand-badge-icon { width: 42px; height: 42px; }
    .brand-badge--logo { background: #fff; padding: 8px; }
    .brand-badge--logo .brand-badge-icon { width: 100%; height: 100%; object-fit: contain; }

    .brand-content h1 {
      font-size: 1.9rem;
      font-weight: 800;
      margin-bottom: 10px;
      letter-spacing: 0.3px;
    }

    .brand-content p {
      font-size: 1rem;
      opacity: 0.85;
      line-height: 1.6;
      margin-bottom: 0;
    }

    .brand-tagline {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 26px;
      padding: 8px 18px;
      border: 1px solid rgba(255,255,255,0.35);
      border-radius: 50px;
      font-size: 0.82rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      opacity: 0.9;
    }

    /* ---------- Right form panel ---------- */
    .auth-panel {
      flex: 1 1 44%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f7f8fb;
      padding: 40px 24px;
    }

    .auth-card {
      width: 100%;
      max-width: 380px;
    }

    .auth-card h4.form-title {
      font-weight: 800;
      font-size: 1.5rem;
      margin-bottom: 4px;
      color: #1a1a1a;
    }

    .auth-card .form-subtitle {
      color: #8a8a8a;
      margin-bottom: 28px;
      font-size: 0.92rem;
    }

    .input-icon-group {
      position: relative;
      margin-bottom: 20px;
    }

    .input-icon-group i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #c0c0c0;
      font-size: 0.95rem;
    }

    .input-icon-group .form-control {
      padding: 12px 16px 12px 44px;
      height: auto;
      border-radius: 10px;
      border: 1px solid #e4e4e9;
      background: #fff;
      font-size: 0.95rem;
      transition: border-color .15s, box-shadow .15s;
    }

    .input-icon-group .form-control:focus {
      border-color: var(--brand);
      box-shadow: 0 0 0 3px rgba(240,116,11,0.12);
    }

    .input-icon-group label {
      font-size: 0.82rem;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
      display: block;
    }

    .forgot-link {
      float: right;
      font-size: 0.82rem;
      color: var(--brand);
      font-weight: 600;
      text-decoration: none;
    }

    .forgot-link:hover { text-decoration: underline; color: var(--brand); }

    .btn-auth {
      background: linear-gradient(135deg, var(--brand), #ff9a3d);
      border: none;
      color: #fff;
      font-weight: 700;
      padding: 13px;
      border-radius: 10px;
      font-size: 0.98rem;
      width: 100%;
      box-shadow: 0 10px 20px rgba(240,116,11,0.28);
      transition: transform .15s, box-shadow .15s;
    }

    .btn-auth:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 24px rgba(240,116,11,0.35);
      color: #fff;
    }

    .auth-footer {
      text-align: center;
      margin-top: 28px;
      font-size: 0.8rem;
      color: #a3a3a3;
    }

    .success-banner {
      background: #eafaf1;
      color: #1f9d55;
      border: 1px solid #cdf0dd;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 18px;
    }

    code.field-error {
      display: block;
      color: #e0433f;
      font-size: 0.8rem;
      margin-top: 4px;
    }

    @media (max-width: 991px) {
      .auth-visual { flex-basis: 100%; min-height: 220px; padding: 32px 20px; }
      .brand-content h1 { font-size: 1.4rem; }
      .watermark-mic { font-size: 130px; }
      .watermark-news { font-size: 90px; }
      .watermark-cameroon { width: 190px; }
      .auth-panel { flex-basis: 100%; }
    }
  </style>

<!-- Start GA -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-94034622-3');
</script>
<!-- /END GA --></head>

<body>
  <div id="app">
    <div class="auth-wrapper">

      <div class="auth-visual">
        <i class="fas fa-microphone-alt watermark watermark-mic"></i>
        <i class="fas fa-newspaper watermark watermark-news"></i>
        <i class="fas fa-comments watermark watermark-quote"></i>

        <img src="{{ asset('admin/assets/img/cameroon-flag-map.svg') }}" class="watermark watermark-cameroon" alt="">

        <div class="brand-content">
          <div class="brand-badge brand-badge--logo"><img src="{{ asset('admin/assets/img/logo-icon.png') }}" class="brand-badge-icon" alt="Le Lampadaire"></div>
          <h1>Le Lampadaire</h1>
          <p>Culture, business, finance &amp; actualités &mdash; l'information qui rassemble, racontée depuis le Cameroun.</p>
          <div class="brand-tagline"><i class="fas fa-broadcast-tower"></i> Espace rédaction &amp; administration</div>
        </div>
      </div>

      <div class="auth-panel">
        <div class="auth-card">
          <h4 class="form-title">{{ __('admin.Login') }}</h4>
          <p class="form-subtitle">Connectez-vous pour accéder au back-office</p>

          @if (session()->has('success'))
            <div class="success-banner">{{ session()->get('success') }}</div>
          @endif

          <form method="POST" action="{{ route('admin.handle-login') }}" class="needs-validation" novalidate="">
            @csrf
            <div class="input-icon-group">
              <label for="email">{{ __('admin.Email') }}</label>
              <i class="fas fa-envelope"></i>
              <input id="email" type="email" class="form-control" name="email" tabindex="1" required autofocus>
              @error('email')
                <code class="field-error">{{ $message }}</code>
              @enderror
              <div class="invalid-feedback">
                {{ __('admin.Please fill in your email') }}
              </div>
            </div>

            <div class="input-icon-group">
              <label for="password">
                {{ __('admin.Password') }}
                <a href="{{ route('admin.forgot-password') }}" class="forgot-link">{{ __('admin.Forgot Password?') }}</a>
              </label>
              <i class="fas fa-lock"></i>
              <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
              <div class="invalid-feedback">
                {{ __('admin.please fill in your password') }}
              </div>
            </div>

            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me">
                <label class="custom-control-label" for="remember-me">{{ __('admin.Remember Me') }}</label>
              </div>
            </div>

            <button type="submit" class="btn-auth" tabindex="4">
              {{ __('admin.Login') }}
            </button>
          </form>

          <div class="auth-footer">
            {{ __('admin.Copyright') }} &copy; {{ __('admin.WebSolutionUs 2023') }}
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <script src="{{ asset('admin/assets/js/stisla.js') }}"></script>

  <!-- Template JS File -->
  <script src="{{ asset('admin/assets/js/scripts.js') }}"></script>
  <script src="{{ asset('admin/assets/js/custom.js') }}"></script>
</body>
</html>
