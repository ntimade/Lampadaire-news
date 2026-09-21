<section class="hero-editorial">
    @if ($heroSlider->isNotEmpty())
    <div class="container">
        <div class="hero-editorial__grid">
            {{-- Main feature: a slider through every hero-flagged story, full-bleed photo. --}}
            <div class="hero-editorial__main">
                <div class="hero-editorial__feature-carousel">
                    @foreach ($heroSlider as $slide)
                        <a href="{{ route('news-details', $slide->slug) }}" class="hero-editorial__feature">
                            <img src="{{ asset($slide->image) }}" alt="">
                            <div class="hero-editorial__scrim"></div>
                            <div class="hero-editorial__caption">
                                <span class="hero-editorial__pill">{{ $slide->category->name ?? '' }}</span>
                                <h1>{{ truncate($slide->title, 110) }}</h1>
                                <div class="hero-editorial__meta">
                                    <span>{{ __('frontend.by') }} {{ $slide->auther->name }}</span>
                                    <span>&middot;</span>
                                    <span>{{ date('M d, Y', strtotime($slide->created_at)) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Rail: the next most important stories, as a compact "à la une" list. --}}
            <aside class="hero-editorial__rail">
                <h3 class="hero-editorial__rail-title">{{ __('frontend.popular post') }}</h3>
                @foreach ($heroSlider->skip(1)->take(4) as $slider)
                    <a href="{{ route('news-details', $slider->slug) }}" class="hero-editorial__rail-item">
                        <div class="hero-editorial__rail-thumb">
                            <img src="{{ asset($slider->image) }}" alt="">
                        </div>
                        <div class="hero-editorial__rail-text">
                            <span class="hero-editorial__rail-cat">{{ $slider->category->name ?? '' }}</span>
                            <h4>{{ truncate($slider->title, 62) }}</h4>
                        </div>
                    </a>
                @endforeach
            </aside>
        </div>
    </div>
    @endif

    <div class="container">
        <div class="hero-editorial__ad">
            <x-ad-carousel placement="home_top_bar" />
        </div>
    </div>
</section>

<style>
    .hero-editorial { padding: 22px 0 4px; }
    .hero-editorial__grid { display: flex; gap: 24px; align-items: stretch; }
    .hero-editorial__main { flex: 0 0 66%; max-width: 66%; }
    .hero-editorial__feature {
        position: relative; display: block; height: 460px; border-radius: 14px;
        overflow: hidden; text-decoration: none;
    }
    .hero-editorial__feature img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: transform .6s ease;
    }
    .hero-editorial__feature:hover img { transform: scale(1.045); }
    .hero-editorial__scrim {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0) 38%, rgba(0,0,0,0.55) 72%, rgba(0,0,0,0.88) 100%);
    }
    .hero-editorial__caption { position: absolute; left: 0; right: 0; bottom: 0; padding: 30px 34px; }
    .hero-editorial__pill {
        display: inline-block; background: var(--colorPrimary); color: #fff;
        font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px; padding: 6px 15px;
        border-radius: 30px; margin-bottom: 14px;
    }
    .hero-editorial__caption h1 {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 900;
        font-size: 2.1rem; line-height: 1.15; margin: 0 0 12px; color: #fff;
    }
    .hero-editorial__meta {
        font-family: 'Poppins', sans-serif; font-size: 13px; color: rgba(255,255,255,0.85);
        display: flex; gap: 8px;
    }

    /* Feature carousel: arrows sit high in the clear photo area so they never
       reach into the caption zone at the bottom (pill + up to 3-line headline
       + byline) — the same class of overlap bug the old hero carousel had. */
    .hero-editorial__feature-carousel { margin-bottom: 6px; }
    .hero-editorial__feature-carousel .slick-prev,
    .hero-editorial__feature-carousel .slick-next {
        top: 36% !important;
    }
    .hero-editorial__feature-carousel .slick-dots { bottom: -22px; }
    .hero-editorial__feature-carousel .slick-dots li button:before { color: #111; }
    .hero-editorial__feature-carousel .slick-dots li.slick-active button:before { color: var(--colorPrimary); opacity: 1; }
    .hero-editorial__feature-carousel .slick-slide > div { line-height: 0; }

    .hero-editorial__rail {
        flex: 1; min-width: 0; background: #fff; border: 1px solid #eee; border-radius: 14px;
        padding: 22px 22px 6px; box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    }
    .hero-editorial__rail-title {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 800; font-size: 19px;
        margin: 0 0 14px; padding-bottom: 12px; border-bottom: 2px solid #111;
        text-transform: capitalize;
    }
    .hero-editorial__rail-item {
        display: flex; gap: 12px; align-items: flex-start; text-decoration: none;
        color: inherit; padding: 14px 0; border-bottom: 1px solid #f0f0f0;
    }
    .hero-editorial__rail-item:last-child { border-bottom: none; }
    .hero-editorial__rail-thumb {
        flex: 0 0 76px; width: 76px; height: 62px; border-radius: 8px; overflow: hidden; background: #eee;
    }
    .hero-editorial__rail-thumb img {
        width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s ease;
    }
    .hero-editorial__rail-item:hover .hero-editorial__rail-thumb img { transform: scale(1.1); }
    .hero-editorial__rail-cat {
        display: block; font-family: 'Poppins', sans-serif; font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .5px; color: var(--colorPrimary); margin-bottom: 4px;
    }
    .hero-editorial__rail-text h4 {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 700; font-size: 14px;
        line-height: 1.3; margin: 0; color: #1a1a1a; transition: color .15s;
    }
    .hero-editorial__rail-item:hover h4 { color: var(--colorPrimary); }

    .hero-editorial__ad { margin-top: 22px; }

    @media screen and (max-width: 991px) {
        .hero-editorial__grid { flex-direction: column; }
        .hero-editorial__main, .hero-editorial__rail { flex: 1 1 100%; max-width: 100%; }
        .hero-editorial__feature { height: 360px; }
        .hero-editorial__caption h1 { font-size: 1.6rem; }
    }
</style>
