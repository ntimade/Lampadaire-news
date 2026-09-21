@php
    $footerCategories = \App\Models\Category::where(['status' => 1, 'show_at_nav' => 1, 'language' => getLangauge()])->get();
@endphp

<footer class="site-footer">
    <img src="{{ asset('admin/assets/img/cameroon-flag-map.svg') }}" class="site-footer__watermark" alt="">

    <div class="container">
        <div class="row site-footer__top">
            <div class="col-lg-4 col-md-6 col-12 site-footer__col">
                <a href="{{ route('home') }}" class="site-footer__brand">
                    <span class="site-footer__brand-icon">
                        @if (!empty($footerInfo->logo))
                            <img src="{{ asset($footerInfo->logo) }}" alt="{{ $settings['site_name'] ?? config('app.name') }}">
                        @else
                            <x-lamp-icon />
                        @endif
                    </span>
                </a>
                <p class="site-footer__desc">
                    {{ $footerInfo->description ?? "Média culturel, économique et rassembleur — l'actualité du Cameroun racontée avec rigueur : culture, business, finance et vie de la nation." }}
                </p>

                @if ($socialLinks->count())
                    <ul class="site-footer__socials list-inline">
                        @foreach ($socialLinks as $link)
                            <li class="list-inline-item">
                                <a href="{{ $link->url }}" target="_blank" rel="noopener">
                                    <i class="{{ $link->icon }}"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="col-lg-2 col-md-6 col-6 site-footer__col">
                <h4 class="site-footer__title">Catégories</h4>
                <ul class="site-footer__links">
                    @forelse ($footerCategories as $cat)
                        <li><a href="{{ route('news', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                    @empty
                        <li><a href="{{ route('news') }}">Toutes les actualités</a></li>
                    @endforelse
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6 site-footer__col">
                <h4 class="site-footer__title">Le site</h4>
                <ul class="site-footer__links">
                    <li><a href="{{ route('about') }}">À propos</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                    <li><a href="{{ route('news') }}">Toutes les actualités</a></li>
                    @auth
                        <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('footer-logout-form').submit();">Déconnexion</a></li>
                        <form id="footer-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    @else
                        <li><a href="{{ route('login') }}">Connexion</a></li>
                        <li><a href="{{ route('register') }}">Créer un compte</a></li>
                    @endauth
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-12 site-footer__col">
                <h4 class="site-footer__title">Newsletter</h4>
                <p class="site-footer__desc">Recevez l'essentiel de l'actualité directement par email.</p>
                <form id="footer-newsletter-form" class="site-footer__newsletter">
                    @csrf
                    <input type="email" name="email" placeholder="Votre email" required>
                    <button type="submit"><i class="fa fa-paper-plane"></i></button>
                </form>
                <div id="footer-newsletter-message" class="site-footer__newsletter-msg"></div>
            </div>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="container">
            <p class="mb-0">
                {!! $footerInfo->copyright ?? '&copy; ' . date('Y') . ' ' . ($settings['site_name'] ?? config('app.name')) . '. Tous droits réservés.' !!}
            </p>
        </div>
    </div>
</footer>

<style>
    .site-footer { position: relative; overflow: hidden; background: linear-gradient(180deg, #1a0f06 0%, #2b1400 100%); color: rgba(255,255,255,0.75); padding-top: 56px; }
    .site-footer__watermark {
        position: absolute; right: -40px; bottom: -60px; width: 320px; height: auto;
        opacity: 0.06; filter: brightness(0) invert(1); pointer-events: none; z-index: 0;
        animation: footerDrift 22s ease-in-out infinite; transform-origin: 60% 60%;
    }
    @keyframes footerDrift {
        0%, 100% { transform: rotate(-2deg) scale(1); }
        50% { transform: rotate(2deg) scale(1.04); }
    }
    .site-footer .container { position: relative; z-index: 1; }
    .site-footer__top { padding-bottom: 32px; }
    .site-footer__col { margin-bottom: 28px; }
    .site-footer__brand { display: inline-flex; align-items: center; gap: 10px; font-size: 1.4rem; font-weight: 800; color: #fff; margin-bottom: 14px; text-decoration: none; }
    .site-footer__brand:hover { color: #fff; text-decoration: none; }
    .site-footer__brand img { max-height: 42px; }
    .site-footer__brand-icon {
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0; border-radius: 14px;
        background: #fff;
        padding: 12px 18px;
        box-shadow: 0 8px 22px rgba(0,0,0,0.35);
        max-width: 100%;
    }
    .site-footer__brand-icon svg { width: 80px; height: 80px; }
    .site-footer__brand-icon img { height: 110px; width: auto; display: block; }
    .site-footer__desc { font-size: 0.88rem; line-height: 1.7; color: rgba(255,255,255,0.6); }
    .site-footer__title { color: #fff; font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 18px; }
    .site-footer__links { list-style: none; padding: 0; margin: 0; }
    .site-footer__links li { margin-bottom: 10px; }
    .site-footer__links a { color: rgba(255,255,255,0.65); font-size: 0.9rem; text-decoration: none; transition: color .15s; }
    .site-footer__links a:hover { color: #f0740b; text-decoration: none; }

    .site-footer__socials { padding: 0; margin: 18px 0 0; }
    .site-footer__socials a {
        display: flex; align-items: center; justify-content: center; width: 36px; height: 36px;
        border-radius: 50%; background: rgba(255,255,255,0.08); color: #fff; transition: background .15s, transform .15s;
    }
    .site-footer__socials a:hover { background: #f0740b; transform: translateY(-2px); }

    .site-footer__newsletter { display: flex; margin-top: 14px; }
    .site-footer__newsletter input {
        flex: 1; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.06); color: #fff;
        border-radius: 8px 0 0 8px; padding: 10px 14px; font-size: 0.88rem; outline: none;
    }
    .site-footer__newsletter input::placeholder { color: rgba(255,255,255,0.4); }
    .site-footer__newsletter button {
        border: none; background: #f0740b; color: #fff; padding: 0 16px; border-radius: 0 8px 8px 0; cursor: pointer;
        transition: background .15s;
    }
    .site-footer__newsletter button:hover { background: #d3660a; }
    .site-footer__newsletter-msg { font-size: 0.8rem; margin-top: 8px; }

    .site-footer__bottom { border-top: 1px solid rgba(255,255,255,0.08); padding: 18px 0; margin-top: 8px; }
    .site-footer__bottom p { text-align: center; font-size: 0.82rem; color: rgba(255,255,255,0.45); }
</style>

<script>
    (function () {
        var form = document.getElementById('footer-newsletter-form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = form.querySelector('input[name="email"]').value;
            var msg = document.getElementById('footer-newsletter-message');
            fetch('{{ route('subscribe-newsletter') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                msg.textContent = data.message || '';
                msg.style.color = data.status === 'success' ? '#4caf50' : '#ff8a80';
                if (data.status === 'success') { form.reset(); }
            })
            .catch(function () {
                msg.textContent = 'Une erreur est survenue.';
                msg.style.color = '#ff8a80';
            });
        });
    })();
</script>
