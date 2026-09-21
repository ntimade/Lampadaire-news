<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Connexion &mdash; {{ $settings['site_name'] ?? config('app.name') }}</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">

  <style>
    :root { --brand: #f0740b; }
    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

    .auth-wrapper { min-height: 100vh; display: flex; flex-wrap: wrap; }

    .auth-visual {
      position: relative;
      flex: 1 1 56%;
      min-height: 340px;
      overflow: hidden;
      background:
        radial-gradient(circle at 20% 15%, rgba(255,154,61,0.35), transparent 55%),
        linear-gradient(150deg, #2b1400 0%, #6e2f00 42%, var(--brand) 100%);
      display: flex; align-items: center; justify-content: center; padding: 48px 32px;
    }

    .auth-visual::after {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(0deg, rgba(0,0,0,0.35), transparent 45%);
      pointer-events: none;
    }

    .watermark { position: absolute; color: #fff; opacity: 0.10; pointer-events: none; z-index: 0; filter: drop-shadow(0 0 25px rgba(0,0,0,0.25)); }
    .watermark-mic { font-size: 220px; top: 8%; left: -40px; animation: floatY 7s ease-in-out infinite; }
    .watermark-news { font-size: 150px; bottom: 6%; left: 8%; opacity: 0.09; animation: floatY 9s ease-in-out infinite 1.2s; }
    .watermark-quote { font-size: 110px; top: 12%; right: 6%; opacity: 0.09; animation: floatY 8s ease-in-out infinite 0.5s; }
    .watermark-cameroon {
      right: -20px; bottom: -30px; width: 300px; height: auto;
      opacity: 0.16; filter: brightness(0) invert(1);
      animation: driftRotate 22s ease-in-out infinite; transform-origin: 60% 60%;
    }

    @keyframes floatY { 0%, 100% { transform: translateY(0) rotate(-2deg); } 50% { transform: translateY(-22px) rotate(3deg); } }
    @keyframes driftRotate { 0%, 100% { transform: rotate(-2deg) scale(1); } 50% { transform: rotate(2deg) scale(1.04); } }

    .brand-content { position: relative; z-index: 1; max-width: 420px; color: #fff; text-align: center; }
    .brand-badge {
      width: 84px; height: 84px; border-radius: 22px;
      background: linear-gradient(135deg, #ffb066, var(--brand));
      color: #fff; display: flex; align-items: center; justify-content: center;
      font-size: 1.9rem; font-weight: 800; margin: 0 auto 22px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.35); letter-spacing: 0.5px;
    }
    .brand-badge-icon { width: 42px; height: 42px; }
    .brand-badge--logo { background: #fff; padding: 8px; }
    .brand-badge--logo .brand-badge-icon { width: 100%; height: 100%; object-fit: contain; }
    .brand-content h1 { font-size: 1.9rem; font-weight: 800; margin-bottom: 10px; }
    .brand-content p { font-size: 1rem; opacity: 0.85; line-height: 1.6; margin-bottom: 0; }
    .brand-tagline {
      display: inline-flex; align-items: center; gap: 8px; margin-top: 26px;
      padding: 8px 18px; border: 1px solid rgba(255,255,255,0.35); border-radius: 50px;
      font-size: 0.82rem; letter-spacing: 0.5px; text-transform: uppercase; opacity: 0.9;
    }

    .auth-panel { flex: 1 1 44%; display: flex; align-items: center; justify-content: center; background: #f7f8fb; padding: 40px 24px; }
    .auth-card { width: 100%; max-width: 380px; }
    .auth-card h4.form-title { font-weight: 800; font-size: 1.5rem; margin-bottom: 4px; color: #1a1a1a; }
    .auth-card .form-subtitle { color: #8a8a8a; margin-bottom: 28px; font-size: 0.92rem; }

    .input-icon-group { position: relative; margin-bottom: 20px; }
    .input-icon-group i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #c0c0c0; font-size: 0.95rem; }
    .input-icon-group .form-control {
      padding: 12px 16px 12px 44px; height: auto; border-radius: 10px; border: 1px solid #e4e4e9;
      background: #fff; font-size: 0.95rem; transition: border-color .15s, box-shadow .15s;
    }
    .input-icon-group .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(240,116,11,0.12); }

    .forgot-link { float: right; font-size: 0.82rem; color: var(--brand); font-weight: 600; text-decoration: none; }
    .forgot-link:hover { text-decoration: underline; color: var(--brand); }

    .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }

    .btn-auth {
      background: linear-gradient(135deg, var(--brand), #ff9a3d); border: none; color: #fff;
      font-weight: 700; padding: 13px; border-radius: 10px; font-size: 0.98rem; width: 100%;
      box-shadow: 0 10px 20px rgba(240,116,11,0.28); transition: transform .15s, box-shadow .15s;
    }
    .btn-auth:hover { transform: translateY(-1px); box-shadow: 0 14px 24px rgba(240,116,11,0.35); color: #fff; }

    .auth-footer { text-align: center; margin-top: 28px; font-size: 0.86rem; color: #8a8a8a; }
    .auth-footer a { color: var(--brand); font-weight: 700; text-decoration: none; }
    .auth-footer a:hover { text-decoration: underline; }

    .back-home { position: absolute; top: 24px; left: 50%; transform: translateX(-50%); z-index: 1; font-size: 0.85rem; color: rgba(255,255,255,0.75); text-decoration: none; }
    .back-home:hover { color: #fff; }

    p.text-danger { font-size: 0.8rem; margin-top: 4px; margin-bottom: 0; }

    @media (max-width: 991px) {
      .auth-visual { flex-basis: 100%; min-height: 220px; padding: 32px 20px; }
      .brand-content h1 { font-size: 1.4rem; }
      .watermark-mic { font-size: 130px; }
      .watermark-news { font-size: 90px; }
      .watermark-cameroon { width: 190px; }
      .auth-panel { flex-basis: 100%; }
    }
  </style>
</head>

<body>
  <div class="auth-wrapper">

    <div class="auth-visual">
      <a href="{{ route('home') }}" class="back-home"><i class="fas fa-arrow-left mr-1"></i> Retour au site</a>

      <i class="fas fa-microphone-alt watermark watermark-mic"></i>
      <i class="fas fa-newspaper watermark watermark-news"></i>
      <i class="fas fa-comments watermark watermark-quote"></i>
      <img src="{{ asset('admin/assets/img/cameroon-flag-map.svg') }}" class="watermark watermark-cameroon" alt="">

      <div class="brand-content">
        <div class="brand-badge brand-badge--logo"><img src="{{ asset('frontend/assets/images/logo-icon.png') }}" class="brand-badge-icon" alt="Le Lampadaire"></div>
        <h1>{{ $settings['site_name'] ?? config('app.name') }}</h1>
        <p>Culture, business, finance &amp; actualités &mdash; l'information qui rassemble, racontée depuis le Cameroun.</p>
        <div class="brand-tagline"><i class="fas fa-broadcast-tower"></i> Espace lecteurs</div>
      </div>
    </div>

    <div class="auth-panel">
      <div class="auth-card">
        <h4 class="form-title">{{ __('frontend.Sign in') }}</h4>
        <p class="form-subtitle">Connectez-vous pour commenter et suivre vos sujets préférés</p>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="input-icon-group">
            <i class="fas fa-envelope"></i>
            <input class="form-control" placeholder="{{ __('frontend.Email') }}" type="text" name="email">
            @error('email')
              <p class="text-danger">{{ $message }}</p>
            @enderror
          </div>

          <div class="input-icon-group">
            <i class="fas fa-lock"></i>
            <input class="form-control" placeholder="{{ __('frontend.Password') }}" type="password" name="password">
          </div>

          <div class="remember-row">
            <label class="custom-control custom-checkbox mb-0">
              <input type="checkbox" name="remember" class="custom-control-input">
              <span class="custom-control-label"> {{ __('frontend.Remember') }} </span>
            </label>
            <a href="{{ route('password.request') }}" class="forgot-link">{{ __('frontend.Forgot password?') }}</a>
          </div>

          <button type="submit" class="btn-auth">{{ __('frontend.Login') }}</button>
        </form>

        <p class="auth-footer">{{ __('frontend.Dont have account?') }} <a href="{{ route('register') }}">{{ __('frontend.Sign up') }}</a></p>
      </div>
    </div>

  </div>
</body>
</html>
