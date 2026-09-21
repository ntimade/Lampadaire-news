<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RSS "veille" sources — geographic cascade
    |--------------------------------------------------------------------------
    |
    | Public RSS feeds polled by `php artisan news:collect` to build the
    | editorial watch list (see CollectedArticle). Keyed by the site's
    | category slug so each feed's items land pre-sorted into the matching
    | rubrique.
    |
    | Within a category, sources are ordered as a geographic priority
    | cascade: Cameroon first, then regional/African, then global. The
    | command only moves on to the next source once the earlier one(s)
    | haven't produced enough *new* items this run (see $minPerCategory in
    | CollectNewsFromSources) — so Cameroon coverage is preferred whenever
    | there's enough of it, and broader sources only fill the gap.
    |
    | These are third-party feeds — collected items are never auto-published,
    | only staged for an editor to review and rewrite into a real article.
    |
    */

    // Cascade: Cameroun -> Afrique (Africultures) -> monde (RFI Culture, très
    // francocentrée) en dernier repli seulement.
    'culture' => [
        ['name' => 'Actu Cameroun', 'url' => 'https://actucameroun.com/feed/'],
        ['name' => 'Africultures', 'url' => 'https://africultures.com/feed/'],
        ['name' => 'RFI Culture', 'url' => 'https://www.rfi.fr/fr/culture/rss'],
    ],

    // Cascade: Cameroun -> bourses africaines -> monde/crypto en dernier repli.
    'finance-et-bourse' => [
        ['name' => 'Actu Cameroun (Économie)', 'url' => 'https://actucameroun.com/category/economie/feed/'],
        ['name' => 'Financial Afrik', 'url' => 'https://www.financialafrik.com/feed/'],
        ['name' => 'RFI Économie', 'url' => 'https://www.rfi.fr/fr/economie/rss'],
        ['name' => 'Le Figaro Bourse', 'url' => 'https://www.lefigaro.fr/rss/figaro_bourse.xml'],
        ['name' => 'Cryptoast', 'url' => 'https://cryptoast.fr/feed/'],
    ],

    'business' => [
        ['name' => 'Actu Cameroun (Économie)', 'url' => 'https://actucameroun.com/category/economie/feed/'],
        ['name' => 'Jeune Afrique', 'url' => 'https://www.jeuneafrique.com/feed/'],
    ],

    'sante-prevention' => [
        ['name' => 'Actu Cameroun (Société)', 'url' => 'https://actucameroun.com/category/societe/feed/'],
        ['name' => 'RFI Sciences & Santé', 'url' => 'https://www.rfi.fr/fr/sciences/rss'],
    ],

    'politique-faits-divers' => [
        ['name' => 'Actu Cameroun (Politique)', 'url' => 'https://actucameroun.com/category/politique/feed/'],
        ['name' => 'Actu Cameroun (Société)', 'url' => 'https://actucameroun.com/category/societe/feed/'],
        ['name' => 'RFI Afrique', 'url' => 'https://www.rfi.fr/fr/afrique/rss'],
    ],

    'international' => [
        ['name' => 'RFI Afrique', 'url' => 'https://www.rfi.fr/fr/afrique/rss'],
        ['name' => 'RFI Monde', 'url' => 'https://www.rfi.fr/fr/monde/rss'],
    ],

    'sport' => [
        ['name' => 'Actu Cameroun (Sport)', 'url' => 'https://actucameroun.com/category/sport/feed/'],
        ['name' => 'RFI Sport', 'url' => 'https://www.rfi.fr/fr/sports/rss'],
    ],

    // Religion.info covers all faiths (christianisme, islam, bouddhisme...)
    // internationally — kept first on purpose. Cath.ch is Catholic-only,
    // used only as a supplement given Cameroon's large Catholic population.
    // No dedicated, working RSS feed found for African traditional/animist
    // religions or Kemetism (mvett.com, afrikhepri.org, grioo.com,
    // kemetic.fr, egyptos.net, africaspirituality.com all tested — dead,
    // 404, or not a real feed) — such stories are still caught via
    // title-keyword fallback (see $titleKeywordMap in
    // CollectNewsFromSources) if they surface in any other configured source.
    'religion-spiritualite' => [
        ['name' => 'Religion.info', 'url' => 'https://religion.info/feed/'],
        ['name' => 'Cath.ch', 'url' => 'https://cath.ch/feed/'],
    ],

];
