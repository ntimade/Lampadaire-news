@php
    $imgPath = function (?string $relativePath): ?string {
        if (empty($relativePath)) {
            return null;
        }
        $absolute = public_path($relativePath);

        return file_exists($absolute) ? str_replace('\\', '/', $absolute) : null;
    };

    $byline = function ($article) {
        $author = e($article->auther->name ?? 'Rédaction');
        $date = \Carbon\Carbon::parse($article->created_at)->locale('fr')->translatedFormat('j F Y');
        return 'Par <b>' . $author . '</b> — ' . $date;
    };

    // Prefer the Gemini-cleaned print_excerpt (see
    // GeminiArticleWriter::writeForPrint, generated lazily and cached by
    // NewspaperController) over the raw article body — the raw HTML can
    // carry stray RSS boilerplate or abrupt mid-thought sentences that no
    // amount of $sentenceLimit truncation can fix on its own.
    $printText = function ($article): string {
        return $article->print_excerpt ?: strip_tags($article->content);
    };

    // Str::limit cuts at a fixed character count regardless of where that
    // lands — mid-sentence, mid-word — which reads as broken in a printed
    // excerpt. This cuts at the last complete sentence that fits instead,
    // only falling back to a word-boundary + "…" when no sentence ending is
    // found early enough to be worth keeping (would otherwise throw away
    // most of the character budget on a single long sentence).
    $sentenceLimit = function (string $text, int $maxChars): string {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        $truncated = mb_substr($text, 0, $maxChars);

        $lastEnd = null;
        foreach (['. ', '! ', '? ', '." ', '!" ', '?" '] as $needle) {
            $pos = mb_strrpos($truncated, $needle);
            if ($pos !== false) {
                $end = $pos + mb_strlen(rtrim($needle)) - 1; // keep the punctuation, drop the trailing space
                $lastEnd = $lastEnd === null ? $end : max($lastEnd, $end);
            }
        }

        if ($lastEnd !== null && $lastEnd > $maxChars * 0.4) {
            return mb_substr($truncated, 0, $lastEnd + 1);
        }

        $lastSpace = mb_strrpos($truncated, ' ');
        $safe = $lastSpace !== false ? mb_substr($truncated, 0, $lastSpace) : $truncated;

        return rtrim($safe, ' ,;:') . '…';
    };

    // Same colour family as the site's category-placeholder images
    // (public/frontend/assets/images/category-placeholders) — a reader
    // flipping between the PDF and the website sees the same colour per
    // rubrique either way.
    $categoryColors = [
        'culture' => '#7c3aad',
        'finance-et-bourse' => '#16825a',
        'business' => '#d97706',
        'sante-prevention' => '#0d9488',
        'politique-faits-divers' => '#7a1f1f',
        'religion-spiritualite' => '#3730a3',
        'international' => '#1d4e89',
        'sport' => '#dc2626',
    ];
    $colorFor = fn (?string $slug) => $categoryColors[$slug] ?? '#7a3a08';

    $masthead_logo = $imgPath('frontend/assets/images/logo-full.png');

    // Classic-broadsheet type system: a display serif for headlines (the way
    // NYT/Le Monde-style redesigns use Cheltenham/Marianne), a workhorse text
    // serif for body copy (readable at small sizes, unlike a display face),
    // and a condensed grotesque for kickers/meta — the sans-serif-on-serif
    // contrast newspapers have used for section labels since hot metal days.
    $fontsDir = 'frontend/assets/fonts/';
    $fonts = [
        'playfairBold'       => $imgPath($fontsDir . 'PlayfairDisplay-Bold.ttf'),
        'playfairBlack'      => $imgPath($fontsDir . 'PlayfairDisplay-Black.ttf'),
        'playfairBoldItalic' => $imgPath($fontsDir . 'PlayfairDisplay-BoldItalic.ttf'),
        'ptSerifRegular'     => $imgPath($fontsDir . 'PTSerif-Regular.ttf'),
        'ptSerifBold'        => $imgPath($fontsDir . 'PTSerif-Bold.ttf'),
        'ptSerifItalic'      => $imgPath($fontsDir . 'PTSerif-Italic.ttf'),
        'oswaldMedium'       => $imgPath($fontsDir . 'Oswald-Medium.ttf'),
        'oswaldSemiBold'     => $imgPath($fontsDir . 'Oswald-SemiBold.ttf'),
        'oswaldBold'         => $imgPath($fontsDir . 'Oswald-Bold.ttf'),
    ];

    $totalArticles = 1 + $teasers->count();
    foreach ($pages as $p) {
        foreach ($p['sections'] as $s) {
            $totalArticles += 1 + $s['grid']->count();
        }
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    @if ($fonts['playfairBold'])
        @font-face { font-family: 'Playfair Display'; font-weight: 700; font-style: normal; src: url('{{ $fonts['playfairBold'] }}') format('truetype'); }
    @endif
    @if ($fonts['playfairBlack'])
        @font-face { font-family: 'Playfair Display'; font-weight: 900; font-style: normal; src: url('{{ $fonts['playfairBlack'] }}') format('truetype'); }
    @endif
    @if ($fonts['playfairBoldItalic'])
        @font-face { font-family: 'Playfair Display'; font-weight: 700; font-style: italic; src: url('{{ $fonts['playfairBoldItalic'] }}') format('truetype'); }
    @endif
    @if ($fonts['ptSerifRegular'])
        @font-face { font-family: 'PT Serif'; font-weight: 400; font-style: normal; src: url('{{ $fonts['ptSerifRegular'] }}') format('truetype'); }
    @endif
    @if ($fonts['ptSerifBold'])
        @font-face { font-family: 'PT Serif'; font-weight: 700; font-style: normal; src: url('{{ $fonts['ptSerifBold'] }}') format('truetype'); }
    @endif
    @if ($fonts['ptSerifItalic'])
        @font-face { font-family: 'PT Serif'; font-weight: 400; font-style: italic; src: url('{{ $fonts['ptSerifItalic'] }}') format('truetype'); }
    @endif
    @if ($fonts['oswaldMedium'])
        @font-face { font-family: 'Oswald'; font-weight: 500; font-style: normal; src: url('{{ $fonts['oswaldMedium'] }}') format('truetype'); }
    @endif
    @if ($fonts['oswaldSemiBold'])
        @font-face { font-family: 'Oswald'; font-weight: 600; font-style: normal; src: url('{{ $fonts['oswaldSemiBold'] }}') format('truetype'); }
    @endif
    @if ($fonts['oswaldBold'])
        @font-face { font-family: 'Oswald'; font-weight: 700; font-style: normal; src: url('{{ $fonts['oswaldBold'] }}') format('truetype'); }
    @endif

    @page { margin: 24px 26px; background-color: #f1efe7; }
    * { box-sizing: border-box; }
    html, body { background-color: #f1efe7; }
    body {
        font-family: 'PT Serif', Georgia, 'Times New Roman', serif;
        color: #111;
        font-size: 10px;
        line-height: 1.4;
    }

    .page { page-break-after: always; }
    .page-no-break { page-break-after: avoid; }

    /* ---------- Masthead (cover only) ---------- */
    .masthead-rule-top { border-top: 1px solid #111; border-bottom: 1px solid #111; height: 3px; margin-bottom: 2px; }
    .masthead-wrap { text-align: center; padding: 8px 0 6px; }
    .masthead-logo-full { height: 118px; width: auto; }
    .meta-table { width: 100%; border-collapse: collapse; margin: 0 0 14px; }
    .meta-table td {
        font-family: 'Oswald', 'Arial Narrow', Arial, sans-serif;
        font-weight: 500; font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px;
        border-top: 3px solid #f0740b; border-bottom: 1px solid #111; padding: 5px 0; width: 33.33%;
    }
    .meta-right { text-align: right; }
    .meta-center { text-align: center; }

    /* ---------- Cover lead ---------- */
    .lead-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .lead-eyebrow {
        font-family: 'Oswald', 'Arial Narrow', Arial, sans-serif;
        font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;
        padding-bottom: 3px;
    }
    .lead-headline {
        font-family: 'Playfair Display', Georgia, 'Times New Roman', serif;
        font-size: 28px; font-weight: 900; text-transform: uppercase; line-height: 1.1;
        padding-bottom: 8px; border-bottom: 2px solid #111;
    }
    .lead-body-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 18px; }
    .lead-image-cell { width: 280px; vertical-align: top; padding-right: 16px; }
    .lead-image-box { width: 280px; height: 200px; overflow: hidden; background: #b9b3a4; border: 1px solid #111; }
    .lead-image-box img { width: 280px; height: 200px; object-fit: cover; display: block; }
    .lead-text-cell { vertical-align: top; }
    .lead-byline { font-family: 'PT Serif', Georgia, serif; font-size: 8.5px; font-style: italic; color: #444; margin-bottom: 6px; }
    .lead-excerpt {
        font-family: 'PT Serif', Georgia, 'Times New Roman', serif;
        font-size: 11.5px; line-height: 16px; text-align: justify;
        height: 320px; max-height: 320px; overflow: hidden; word-wrap: break-word;
    }

    /* ---------- Cover teasers ---------- */
    .teaser-table { width: 100%; border-collapse: collapse; }
    .teaser-cell { width: 33.33%; vertical-align: top; border-top: 3px solid #111; padding: 10px 12px 0; }
    .teaser-cell + .teaser-cell { border-left: 1px solid #111; }
    .teaser-tag {
        display: inline-block; color: #fff; font-family: 'Oswald', Arial, sans-serif;
        font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 3px 8px; margin-bottom: 6px;
    }
    .teaser-headline {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 700; font-size: 13px;
        line-height: 15px; height: 45px; overflow: hidden; margin-bottom: 5px;
    }
    .teaser-excerpt { font-size: 8.5px; line-height: 12px; height: 72px; overflow: hidden; color: #333; }

    /* ---------- Cover bottom banner ---------- */
    .cover-banner {
        margin-top: 18px; background: #111; color: #fff; text-align: center;
        padding: 10px 0; font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: 13px;
    }

    /* ---------- Inner page ---------- */
    .page-title {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 900; font-size: 22px;
        text-transform: uppercase; letter-spacing: 0.5px; text-align: center;
        padding-bottom: 8px; margin-bottom: 16px; border-bottom: 3px double #111;
    }
    .section-band {
        color: #fff; font-family: 'Oswald', Arial, sans-serif; font-weight: 600;
        font-size: 13px; text-transform: uppercase; letter-spacing: 1px;
        padding: 6px 12px; margin-bottom: 12px;
    }
    .section-band:not(:first-child) { margin-top: 22px; }

    .feature-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .feature-image-cell { width: 230px; vertical-align: top; padding-right: 14px; }
    .feature-image-box { width: 230px; height: 230px; overflow: hidden; background: #b9b3a4; border: 1px solid #111; }
    .feature-image-box img { width: 230px; height: 230px; object-fit: cover; display: block; }
    .feature-text-cell { vertical-align: top; }
    .feature-headline {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 800; font-size: 17px;
        line-height: 1.2; margin-bottom: 6px;
    }
    .feature-byline { font-family: 'PT Serif', Georgia, serif; font-size: 8px; font-style: italic; color: #444; margin-bottom: 5px; }
    .feature-excerpt {
        font-family: 'PT Serif', Georgia, serif; font-size: 10.5px; line-height: 15px; text-align: justify;
        height: 240px; max-height: 240px; overflow: hidden; word-wrap: break-word;
    }

    /* Secondary grid — column rules, not boxed cards (see culture pass). */
    .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
    .grid-cell { width: 33.33%; vertical-align: top; border-top: 1px solid #111; border-left: 1px solid #111; padding: 10px 12px 0; height: 280px; overflow: hidden; }
    .grid-cell:first-child { border-left: none; padding-left: 0; }
    .grid-cell:last-child { padding-right: 0; }
    .grid-headline {
        font-family: 'Playfair Display', Georgia, serif; font-weight: 700; font-size: 12.5px;
        line-height: 15px; margin-bottom: 6px; height: 45px; max-height: 45px; overflow: hidden; word-wrap: break-word;
    }
    .grid-image-box { width: 100%; height: 90px; overflow: hidden; background: #b9b3a4; margin-bottom: 6px; }
    .grid-image-box img { width: 100%; height: 90px; object-fit: cover; display: block; }
    .grid-byline { font-family: 'PT Serif', Georgia, serif; font-size: 7px; font-style: italic; color: #555; margin-bottom: 4px; }
    .grid-excerpt {
        font-family: 'PT Serif', Georgia, serif; font-size: 9px; line-height: 12.5px; text-align: justify;
        height: 112px; max-height: 112px; overflow: hidden; word-wrap: break-word;
    }

    /* ---------- Footer ---------- */
    .footer-note {
        font-family: 'Oswald', 'Arial Narrow', Arial, sans-serif;
        margin-top: 10px; border-top: 3px double #111; padding-top: 6px; text-align: center;
        font-size: 8px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; color: #555;
    }
</style>
</head>
<body>

@php $leadImg = $lead ? $imgPath($lead->image) : null; @endphp

{{-- ============================ PAGE 1 — UNE ============================ --}}
<div class="page {{ empty($pages) ? 'page-no-break' : '' }}">
    <div class="masthead-rule-top"></div>
    <div class="masthead-wrap">
        @if ($masthead_logo)
            <img src="{{ $masthead_logo }}" class="masthead-logo-full">
        @else
            <span style="font-size:44px;font-weight:bold;">{{ $settings['site_name'] }}</span>
        @endif
    </div>

    <table class="meta-table">
        <tr>
            <td>{{ \Carbon\Carbon::now()->locale('fr')->translatedFormat('l j F Y') }}</td>
            <td class="meta-center">N&deg; {{ \Carbon\Carbon::now()->dayOfYear }} &mdash; Prix&nbsp;: gratuit</td>
            <td class="meta-right">Édition du jour &mdash; {{ $totalArticles }} articles</td>
        </tr>
    </table>

    @if ($lead)
        <table class="lead-table">
            <tr><td>
                <div class="lead-eyebrow" style="color: {{ $colorFor($lead->category->slug ?? null) }};">{{ $lead->category->name ?? 'Une' }}</div>
                <div class="lead-headline">{{ $lead->title }}</div>
            </td></tr>
        </table>

        <table class="lead-body-table">
            <tr>
                <td class="lead-image-cell">
                    <div class="lead-image-box">
                        @if ($leadImg)
                            <img src="{{ $leadImg }}">
                        @endif
                    </div>
                </td>
                <td class="lead-text-cell">
                    <div class="lead-byline">{!! $byline($lead) !!}</div>
                    <div class="lead-excerpt">{!! $sentenceLimit($printText($lead), 900) !!}</div>
                </td>
            </tr>
        </table>
    @endif

    @if ($teasers->count())
        <table class="teaser-table">
            <tr>
                @foreach ($teasers as $teaser)
                    @php $teaserImg = $imgPath($teaser->image); @endphp
                    <td class="teaser-cell">
                        <span class="teaser-tag" style="background: {{ $colorFor($teaser->category->slug ?? null) }};">{{ $teaser->category->name ?? '' }}</span>
                        <div class="teaser-headline">{{ $teaser->title }}</div>
                        <div class="teaser-excerpt">{!! $sentenceLimit($printText($teaser), 150) !!}</div>
                    </td>
                @endforeach
                @for ($i = $teasers->count(); $i < 3; $i++)
                    <td class="teaser-cell"></td>
                @endfor
            </tr>
        </table>
    @endif

    <div class="cover-banner">« L'actualité éclairée — Le Lampadaire »</div>
</div>

{{-- ======================== INNER THEMED PAGES ======================== --}}
@foreach ($pages as $page)
    <div class="page {{ $loop->last ? 'page-no-break' : '' }}">
        <div class="page-title">{{ $page['title'] }}</div>

        @foreach ($page['sections'] as $section)
            @php
                $category = $section['category'];
                $feature = $section['feature'];
                $featureImg = $imgPath($feature->image);
                $color = $colorFor($category->slug);
            @endphp

            <div class="section-band" style="background: {{ $color }};">{{ $category->name }}</div>

            <table class="feature-table">
                <tr>
                    <td class="feature-image-cell">
                        <div class="feature-image-box">
                            @if ($featureImg)
                                <img src="{{ $featureImg }}">
                            @endif
                        </div>
                    </td>
                    <td class="feature-text-cell">
                        <div class="feature-headline">{{ $feature->title }}</div>
                        <div class="feature-byline">{!! $byline($feature) !!}</div>
                        <div class="feature-excerpt">{!! $sentenceLimit($printText($feature), 700) !!}</div>
                    </td>
                </tr>
            </table>

            @if ($section['grid']->count())
                <table class="grid-table">
                    <tr>
                        @foreach ($section['grid'] as $article)
                            @php $gridImg = $imgPath($article->image); @endphp
                            <td class="grid-cell">
                                <div class="grid-headline">{{ $article->title }}</div>
                                <div class="grid-image-box">
                                    @if ($gridImg)
                                        <img src="{{ $gridImg }}">
                                    @endif
                                </div>
                                <div class="grid-byline">{!! $byline($article) !!}</div>
                                <div class="grid-excerpt">{!! $sentenceLimit($printText($article), 220) !!}</div>
                            </td>
                        @endforeach
                        @for ($i = $section['grid']->count(); $i < 3; $i++)
                            <td class="grid-cell"></td>
                        @endfor
                    </tr>
                </table>
            @endif
        @endforeach

        <div class="footer-note">
            {{ $settings['site_name'] }} &mdash; Édition générée le {{ \Carbon\Carbon::now()->locale('fr')->translatedFormat('j F Y \à H\hi') }}
        </div>
    </div>
@endforeach

@if (! $lead && empty($pages))
    <div class="page page-no-break">
        <p style="text-align:center; margin-top:40px;">Aucun article disponible pour cette édition.</p>
    </div>
@endif

</body>
</html>
