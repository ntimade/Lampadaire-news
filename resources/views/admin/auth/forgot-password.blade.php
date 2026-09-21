<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Mot de passe oublié &mdash; Le Lampadaire</title>
  <style>
    body { background: linear-gradient(160deg, #fff3e6 0%, #ffffff 60%); }
    .login-brand-badge {
      width: 90px; height: 90px; border-radius: 50%;
      background: linear-gradient(135deg, #f0740b, #ff9a3d);
      color: #fff; display: flex; align-items: center; justify-content: center;
      font-size: 2rem; font-weight: 700; margin: 0 auto;
      box-shadow: 0 6px 18px rgba(240, 116, 11, 0.35);
    }
    .login-brand-badge--logo { background: #fff; padding: 8px; }
    .login-brand-badge--logo .login-brand-badge-icon { width: 100%; height: 100%; object-fit: contain; }
    .card-primary { border-top: 3px solid #f0740b; }
    .login-brand-badge-icon { width: 44px; height: 44px; }
  </style>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('admin/assets/css/components.css') }}">
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
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="login-brand text-center">
              <div class="login-brand-badge login-brand-badge--logo"><img src="{{ asset('admin/assets/img/logo-icon.png') }}" class="login-brand-badge-icon" alt="Le Lampadaire"></div>
              <h4 class="mt-3 mb-0">Le Lampadaire</h4>
              <p class="text-muted">Espace d'administration</p>
            </div>

            <div class="card card-primary">
              <div class="card-header"><h4>{{ __('admin.Forgot Password') }}</h4></div>

              <div class="card-body">
                <p >{{ __('admin.Forgot your password? No problem. We got you.') }}</p>
                @if (session()->has('success'))
                    <i><b style="color:green">{{ session()->get('success') }}</b></i>
                @endif
                <form method="POST" action="{{ route('admin.forgot-password.send') }}" class="needs-validation" novalidate="">
                    @csrf
                  <div class="form-group">
                    <label for="email">{{ __('admin.Email') }}</label>
                    <input id="email" type="email" class="form-control" name="email" tabindex="1" required autofocus>
                    @error('email')
                        <code>{{ $message }}</code>
                    @enderror
                    <div class="invalid-feedback">
                      {{ __('admin.Please fill in your email') }}
                    </div>
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                      {{ __('admin.Send Link') }}
                    </button>
                  </div>
                </form>


              </div>
            </div>

            <div class="simple-footer">
              {{ __('admin.Copyright') }} &copy; {{ __('admin.WebSolutionUs 2023') }}
            </div>
          </div>
        </div>
      </div>
    </section>
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
