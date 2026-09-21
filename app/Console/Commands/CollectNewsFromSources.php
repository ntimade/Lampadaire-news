<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CollectedArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CollectNewsFromSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll the RSS sources in config/news_sources.php and stage new items — categorised by their own RSS tag when recognised, with photo download — as CollectedArticle rows for editorial review (nothing is auto-published).';

    /**
     * How many of a feed's most recent items to look at per run — keeps a
     * first-ever run (or a feed that goes stale for a while) from dumping
     * its entire back-catalogue into the review queue at once.
     */
    protected int $maxItemsPerFeed = 6;

    /**
     * Geographic cascade threshold: once a category has this many *new*
     * items (from ANY source, resolved by actual topic — see below) in this
     * run, the remaining, broader sources configured for that category are
     * skipped — Cameroun d'abord, puis régional/Afrique, puis le monde,
     * seulement en complément.
     */
    protected int $minPerCategory = 4;

    /**
     * Where downloaded photos land, relative to public/ — same convention
     * as News::image so a converted draft can reuse the path as-is.
     */
    protected string $imageDir = 'frontend/assets/images/collected';

    /**
     * Some sources (Actu Cameroun's main feed, RFI's regional feeds) mix
     * every topic together rather than being pre-filtered per rubrique —
     * blindly filing all of their items under whichever category they're
     * configured for in news_sources.php would badly mis-sort content (a
     * "Politique" item from a "Culture"-configured feed, etc). Every item's
     * own <category> tag is matched against this table first; only when it
     * doesn't match anything here does the item fall back to the category
     * the feed is configured under.
     */
    protected array $categoryKeywordMap = [
        'sport' => 'sport',
        'politique' => 'politique-faits-divers',
        'faits divers' => 'politique-faits-divers',
        'societe' => 'politique-faits-divers',
        'sante' => 'sante-prevention',
        'bien-etre' => 'sante-prevention',
        'culture' => 'culture',
        'art' => 'culture',
        'musique' => 'culture',
        'economie' => 'finance-et-bourse',
        'bourse' => 'finance-et-bourse',
        'finance' => 'finance-et-bourse',
        'business' => 'business',
        'entreprise' => 'business',
        'religion' => 'religion-spiritualite',
        'international' => 'international',
        'monde' => 'international',
        'geopolitique' => 'international',
        'diplomatie' => 'international',
    ];

    /**
     * Second-pass fallback for feeds that carry no <category> tag at all
     * (Jeune Afrique's main feed, notably) — a coarse title-keyword check so
     * an obvious political/diplomatic story doesn't get filed as "Business"
     * just because that's the only category its feed happens to be
     * configured under. Deliberately narrow: only very distinctive words,
     * to avoid false positives on a genuinely business/economy headline.
     */
    protected array $titleKeywordMap = [
        'président' => 'politique-faits-divers',
        'ministre' => 'politique-faits-divers',
        'élection' => 'politique-faits-divers',
        'putsch' => 'politique-faits-divers',
        'mutinerie' => 'politique-faits-divers',
        'frontière' => 'politique-faits-divers',
        'gouvernement' => 'politique-faits-divers',
        'armée' => 'politique-faits-divers',
        'opposant' => 'politique-faits-divers',
        'cemac' => 'politique-faits-divers',
        'beac' => 'politique-faits-divers',
        'diplomat' => 'international',
        'match' => 'sport',
        'championnat' => 'sport',
        'footballeur' => 'sport',
        'sélectionneur' => 'sport',
        'épidémie' => 'sante-prevention',
        'vaccin' => 'sante-prevention',
        'hôpital' => 'sante-prevention',
        'bitcoin' => 'finance-et-bourse',
        'crypto' => 'finance-et-bourse',
        'bourse' => 'finance-et-bourse',

        // Religions traditionnelles africaines / kémitisme : aucun flux RSS
        // dédié et fiable trouvé (candidats testés — mvett.com, afrikhepri.org,
        // grioo.com, kemetic.fr, egyptos.net, africaspirituality.com — tous en
        // échec de connexion, 404, ou pas un vrai flux XML). En attendant,
        // ces mots-clés récupèrent quand même un tel sujet s'il apparaît dans
        // une des sources généralistes déjà en place (Actu Cameroun, RFI
        // Afrique, Jeune Afrique...) plutôt que de le laisser dans la
        // rubrique par défaut du flux.
        'vaudou' => 'religion-spiritualite',
        'vodun' => 'religion-spiritualite',
        'animisme' => 'religion-spiritualite',
        'animiste' => 'religion-spiritualite',
        'kemet' => 'religion-spiritualite',
        'kémite' => 'religion-spiritualite',
        'kémitisme' => 'religion-spiritualite',
        'orisha' => 'religion-spiritualite',
        'féticheur' => 'religion-spiritualite',
        'religions traditionnelles' => 'religion-spiritualite',
        'culte des ancêtres' => 'religion-spiritualite',
    ];

    public function handle(): int
    {
        $sources = config('news_sources', []);
        $totalNew = 0;

        // Tracked globally (not reset per category) so an item redirected to
        // a different rubrique by its own tag still counts toward that
        // rubrique's cascade threshold, even when it came from a feed
        // configured under a different category key.
        $addedByCategoryId = [];

        foreach ($sources as $categorySlug => $feeds) {
            $category = Category::where('slug', $categorySlug)->first();

            if (! $category) {
                $this->warn("Rubrique inconnue, ignorée : {$categorySlug}");
                continue;
            }

            $addedByCategoryId[$category->id] ??= 0;

            foreach ($feeds as $feed) {
                if ($addedByCategoryId[$category->id] >= $this->minPerCategory) {
                    $this->line("{$category->name} — {$feed['name']} : ignorée, déjà {$addedByCategoryId[$category->id]} article(s) via une source plus locale");
                    continue;
                }

                $count = $this->collectFeed($feed['url'], $feed['name'], $category->id, $addedByCategoryId);
                $totalNew += $count;
                $this->line("{$category->name} — {$feed['name']} : {$count} nouvel(le)(s) article(s)");
            }
        }

        $this->info("Terminé. {$totalNew} article(s) ajouté(s) à la file de relecture.");

        return self::SUCCESS;
    }

    /**
     * @param  array<int,int>  $addedByCategoryId  category_id => running count this run, updated by reference.
     */
    protected function collectFeed(string $url, string $sourceName, int $defaultCategoryId, array &$addedByCategoryId): int
    {
        try {
            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; LeLampadaireBot/1.0)',
            ])->get($url);

            if (! $response->successful()) {
                Log::warning("news:collect — {$sourceName} a répondu {$response->status()}");
                return 0;
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response->body());
            libxml_use_internal_errors(false);

            if ($xml === false || ! isset($xml->channel->item)) {
                Log::warning("news:collect — flux invalide ou vide : {$sourceName}");
                return 0;
            }

            // Plain foreach on purpose: collect() on a SimpleXMLElement node-list
            // does not reliably iterate every sibling <item>, it can silently
            // collapse down to just the first one.
            $added = 0;
            $seen = 0;

            foreach ($xml->channel->item as $item) {
                if ($seen >= $this->maxItemsPerFeed) {
                    break;
                }
                $seen++;

                $link = trim((string) $item->link);

                if (empty($link)) {
                    continue;
                }

                $hash = hash('sha256', $link);

                if (CollectedArticle::where('source_url_hash', $hash)->exists()) {
                    continue;
                }

                $publishedAt = null;
                if (! empty((string) $item->pubDate)) {
                    try {
                        $publishedAt = \Carbon\Carbon::parse((string) $item->pubDate);
                    } catch (\Throwable $e) {
                        $publishedAt = null;
                    }
                }

                [$categoryId, $confidence] = $this->resolveCategoryId((string) $item->category, (string) $item->title, $defaultCategoryId);

                $imageUrl = $this->extractImageUrl($item);
                $localImage = $imageUrl ? $this->downloadImage($imageUrl) : null;

                CollectedArticle::create([
                    'category_id' => $categoryId,
                    'category_confidence' => $confidence,
                    'source_name' => $sourceName,
                    'source_url' => $link,
                    'source_url_hash' => $hash,
                    'title' => Str::limit(strip_tags((string) $item->title), 250, ''),
                    'summary' => Str::limit(strip_tags((string) $item->description), 600),
                    'image' => $localImage,
                    'published_at' => $publishedAt,
                    'fetched_at' => now(),
                    'status' => 'new',
                ]);

                $addedByCategoryId[$categoryId] = ($addedByCategoryId[$categoryId] ?? 0) + 1;
                $added++;
            }

            return $added;
        } catch (\Throwable $e) {
            Log::warning("news:collect — échec sur {$sourceName} ({$url}) : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Match the item's own <category> tag (e.g. "Politique", "Sport",
     * "Société") against $categoryKeywordMap. Falls back to the feed's
     * configured category when the tag is empty, unmapped, or doesn't
     * correspond to a rubrique that exists on the site.
     *
     * Returns [category_id, confidence]. Confidence matters downstream:
     * Gemini's free-tier classify() quota is only 20 requests/day per key
     * (discovered the hard way — a blanket reclassify-everything pass blew
     * through it in minutes), so AutoPublishCollectedNews only spends it on
     * 'keyword'/'default' items instead of every article. A real <category>
     * tag match is trustworthy enough on its own.
     */
    protected function resolveCategoryId(string $rawTag, string $title, int $defaultCategoryId): array
    {
        $normalizedTag = Str::of($rawTag)->lower()->ascii()->__toString();

        foreach ($this->categoryKeywordMap as $keyword => $slug) {
            if ($normalizedTag !== '' && str_contains($normalizedTag, $keyword)) {
                $categoryId = Category::where('slug', $slug)->value('id');
                if ($categoryId) {
                    return [$categoryId, 'tag'];
                }
            }
        }

        // No usable <category> tag (e.g. Jeune Afrique's main feed) —
        // fall back to a coarse title-keyword check before giving up.
        $normalizedTitle = Str::of($title)->lower()->ascii()->__toString();

        foreach ($this->titleKeywordMap as $keyword => $slug) {
            $normalizedKeyword = Str::of($keyword)->lower()->ascii()->__toString();
            if ($normalizedTitle !== '' && str_contains($normalizedTitle, $normalizedKeyword)) {
                $categoryId = Category::where('slug', $slug)->value('id');
                if ($categoryId) {
                    return [$categoryId, 'keyword'];
                }
            }
        }

        return [$defaultCategoryId, 'default'];
    }

    /**
     * Look for a photo on the item — RSS's standard <enclosure> first, then
     * the common Media RSS extensions (<media:thumbnail>, <media:content>)
     * that RFI and most other real-world feeds use instead.
     */
    protected function extractImageUrl(\SimpleXMLElement $item): ?string
    {
        if (isset($item->enclosure)) {
            $type = (string) $item->enclosure['type'];
            $url = (string) $item->enclosure['url'];
            if (! empty($url) && ($type === '' || str_starts_with($type, 'image/'))) {
                return $url;
            }
        }

        $media = $item->children('http://search.yahoo.com/mrss/');

        if (isset($media->thumbnail) && ! empty((string) $media->thumbnail['url'])) {
            return (string) $media->thumbnail['url'];
        }

        if (isset($media->content) && ! empty((string) $media->content['url'])) {
            return (string) $media->content['url'];
        }

        return null;
    }

    /**
     * Download the photo locally (never hotlinked) so it behaves exactly
     * like a normal uploaded News image. Returns the relative path stored
     * on the model, or null if the fetch fails for any reason.
     */
    protected function downloadImage(string $imageUrl): ?string
    {
        try {
            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; LeLampadaireBot/1.0)',
            ])->get($imageUrl);

            if (! $response->successful()) {
                return null;
            }

            $contentType = $response->header('Content-Type');
            $extension = match (true) {
                str_contains($contentType, 'jpeg') => 'jpg',
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'webp') => 'webp',
                str_contains($contentType, 'gif') => 'gif',
                default => pathinfo(parse_url($imageUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg',
            };

            $relativeDir = $this->imageDir;
            $absoluteDir = public_path($relativeDir);

            if (! is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0755, true);
            }

            $filename = now()->format('Ymd') . '_' . Str::random(16) . '.' . $extension;
            $relativePath = $relativeDir . '/' . $filename;

            file_put_contents(public_path($relativePath), $response->body());

            return $relativePath;
        } catch (\Throwable $e) {
            Log::warning("news:collect — échec du téléchargement de l'image ({$imageUrl}) : " . $e->getMessage());
            return null;
        }
    }
}
