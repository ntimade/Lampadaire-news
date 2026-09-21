@props(['placement', 'title' => null, 'titleClass' => 'border_section'])

@php
    $ads = \App\Models\Advertisement::active($placement)->get();
@endphp

@if ($ads->count() > 0)
    @if ($title)
        <h4 class="{{ $titleClass }}">{{ $title }}</h4>
    @endif
    <div class="ad-peek-carousel-wrap ad-peek--{{ $placement }}">
        <div class="ad-peek-carousel">
            @foreach ($ads as $item)
                <div class="ad-peek-item">
                    <span class="ad-peek-disclosure">Publicité</span>
                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer sponsored" class="ad-peek-link">
                        <img src="{{ asset($item->image) }}" alt="{{ $item->title }}" class="img-fluid ad-peek-img">
                    </a>
                    @if (!empty($item->description))
                        <p class="ad-peek-desc">{{ $item->description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .ad-peek-carousel-wrap {
            margin: 24px 0;
            padding: 12px;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
        }
        .ad-peek-carousel { overflow: hidden; }
        .ad-peek-carousel .slick-list { overflow: hidden; }
        .ad-peek-item { padding: 0 2px; }
        .ad-peek-disclosure {
            display: inline-block; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px;
            color: #9aa0ac; background: #f1f1f4; padding: 2px 8px; border-radius: 4px; margin-bottom: 6px;
        }
        .ad-peek-link { display: block; border-radius: 8px; overflow: hidden; }
        /* Cap the height so a small or portrait upload can't blow the slot
           out to full container width × its own ratio (a 412×291 banner was
           rendering ~780px tall and shoving the whole homepage down).
           object-fit keeps it undistorted when clamped. */
        .ad-peek-img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 280px;
            object-fit: contain;
            object-position: center;
        }
        .ad-peek--home_top_bar .ad-peek-img { max-height: 150px; }
        .ad-peek--side_bar .ad-peek-img { max-height: 320px; }
        .ad-peek-desc { font-size: 0.8rem; color: #6c757d; margin: 8px 0 0; }
    </style>
@endif
