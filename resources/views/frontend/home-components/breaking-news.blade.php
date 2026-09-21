@if (($settings['breaking_bar_enabled'] ?? '1') == '1' && $urgentNews->count() > 0)
<section class="breaking-bar">
    <div class="breaking-bar__badge">
        <i class="fa fa-bolt"></i>
        <span>Urgent</span>
    </div>

    <div class="breaking-bar__track-wrap">
        <div class="breaking-bar__track">
            @for ($i = 0; $i < 2; $i++)
                @foreach ($urgentNews as $item)
                    @if ($item->link)
                        <a href="{{ $item->link }}" class="breaking-bar__item">{{ $item->text }}</a>
                    @else
                        <span class="breaking-bar__item">{{ $item->text }}</span>
                    @endif
                    <span class="breaking-bar__sep">&#9679;</span>
                @endforeach
            @endfor
        </div>
    </div>
</section>

<style>
    .breaking-bar {
        display: flex;
        align-items: stretch;
        background: #f0740b;
        color: #fff;
        overflow: hidden;
        position: relative;
        min-height: 34px;
        padding: 0 !important;
        margin: 0 !important;
    }

    .breaking-bar__badge {
        display: flex;
        align-items: center;
        gap: 5px;
        background: #a34d00;
        padding: 7px 14px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
        white-space: nowrap;
        flex-shrink: 0;
        z-index: 1;
    }

    .breaking-bar__badge i {
        animation: breakingPulse 1s ease-in-out infinite;
    }

    @keyframes breakingPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.25); }
    }

    .breaking-bar__track-wrap {
        flex: 1;
        overflow: hidden;
        position: relative;
        mask-image: linear-gradient(90deg, transparent, #000 24px, #000 calc(100% - 24px), transparent);
        -webkit-mask-image: linear-gradient(90deg, transparent, #000 24px, #000 calc(100% - 24px), transparent);
    }

    .breaking-bar__track {
        display: flex;
        align-items: center;
        white-space: nowrap;
        width: max-content;
        animation: breakingScroll 28s linear infinite;
        padding: 7px 0;
    }

    .breaking-bar__track:hover {
        animation-play-state: paused;
    }

    .breaking-bar__item {
        color: #fff;
        font-weight: 600;
        font-size: 0.86rem;
        text-decoration: none;
        padding: 0 10px;
    }

    .breaking-bar__item:hover {
        color: #ffe1e1;
        text-decoration: underline;
    }

    .breaking-bar__sep {
        font-size: 5px;
        opacity: 0.6;
        vertical-align: middle;
    }

    @keyframes breakingScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    @media (max-width: 575px) {
        .breaking-bar__badge span { display: none; }
    }
</style>
@endif
