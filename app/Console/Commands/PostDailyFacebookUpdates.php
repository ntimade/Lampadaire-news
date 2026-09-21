<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\News;
use App\Services\FacebookPoster;
use Illuminate\Console\Command;

/**
 * Posts to the Le Lampadaire Facebook Page, spread across the day.
 *
 * Runs hourly rather than once a day so the day's posts land spread across
 * many hours instead of bursting all at once (looks less "bot-spammed" on
 * the Page, and a transient failure just gets retried next hour) — each run
 * posts at most ONE article, for the first still-unfulfilled rubrique slot
 * in the day's rotation.
 *
 * Each rubrique gets $dailyPostTargets[slug] posts per day (default 1) —
 * politique-faits-divers and sport are weighted to 2, at the user's request
 * ("deux fois plus de poste que les autres rubriques" for launch), so on a
 * normal day 10 posts go out total instead of 8. Adjust the weights below
 * as the Page's real engagement data comes in.
 *
 * Per slot, in priority order:
 *   1. An article a human editor explicitly flagged "Publier sur Facebook"
 *      (News::post_to_facebook) and that hasn't been posted yet.
 *   2. Otherwise, the most recent active article in that rubrique that
 *      hasn't been posted yet — bot-authored content is fine here, this is
 *      only ever a fallback when nobody curated a pick.
 * A rubrique with no eligible article left is simply skipped that day.
 *
 * IMPORTANT — token durability: config('services.facebook.page_access_token')
 * must be a Page access token derived from a *long-lived* user token, or it
 * expires with the short-lived one it came from (typically ~1-2 hours after
 * being copied from the Graph API Explorer). To make it durable:
 *   1. https://developers.facebook.com/apps/{app-id}/settings/basic/ → copy
 *      the App Secret.
 *   2. Exchange the short-lived user token for a 60-day one:
 *      GET https://graph.facebook.com/v19.0/oauth/access_token
 *          ?grant_type=fb_exchange_token&client_id={app-id}
 *          &client_secret={app-secret}&fb_exchange_token={short-lived-token}
 *   3. Re-derive the Page token from that long-lived user token via
 *      GET /me/accounts?access_token={long-lived-user-token} — a Page token
 *      derived this way doesn't expire (expires_at: 0).
 */
class PostDailyFacebookUpdates extends Command
{
    protected $signature = 'facebook:post-daily';

    protected $description = "Publie sur la Page Facebook, un article à la fois, jusqu'à ce que chaque rubrique ait atteint son quota du jour.";

    protected array $categorySlugsInOrder = [
        'politique-faits-divers',
        'finance-et-bourse',
        'business',
        'sante-prevention',
        'culture',
        'religion-spiritualite',
        'international',
        'sport',
    ];

    /**
     * Posts per rubrique per day. A slug not listed here defaults to 1
     * (see $this->targetFor()).
     */
    protected array $dailyPostTargets = [
        'politique-faits-divers' => 2,
        'sport' => 2,
    ];

    protected function targetFor(string $slug): int
    {
        return $this->dailyPostTargets[$slug] ?? 1;
    }

    public function handle(FacebookPoster $poster): int
    {
        if (empty(config('services.facebook.page_id')) || empty(config('services.facebook.page_access_token'))) {
            $this->warn('Facebook non configuré (FACEBOOK_PAGE_ID / FACEBOOK_PAGE_ACCESS_TOKEN manquant) — rien à faire.');
            return self::SUCCESS;
        }

        $categories = Category::whereIn('slug', $this->categorySlugsInOrder)->get()->keyBy('slug');

        // One pass through every rubrique first (so each gets covered
        // before any doubles up), then a second pass for the ones weighted
        // above 1 — natural effect: with politique-faits-divers/sport at 2,
        // hours 1-8 cover all 8 rubriques once, hours 9-10 give those two
        // their second post.
        $slots = $this->categorySlugsInOrder;
        foreach ($this->categorySlugsInOrder as $slug) {
            for ($extra = 1; $extra < $this->targetFor($slug); $extra++) {
                $slots[] = $slug;
            }
        }

        foreach ($slots as $slug) {
            $category = $categories->get($slug);
            if (! $category) {
                continue;
            }

            $target = $this->targetFor($slug);
            $postedTodayCount = News::where('category_id', $category->id)
                ->whereDate('posted_to_facebook_at', now()->toDateString())
                ->count();

            if ($postedTodayCount >= $target) {
                continue;
            }

            $article = News::where('category_id', $category->id)
                ->activeEntries()
                ->whereNull('posted_to_facebook_at')
                ->where('post_to_facebook', 1)
                ->orderByDesc('id')
                ->first();

            $viaCuration = (bool) $article;

            if (! $article) {
                $article = News::where('category_id', $category->id)
                    ->activeEntries()
                    ->whereNull('posted_to_facebook_at')
                    ->orderByDesc('id')
                    ->first();
            }

            if (! $article) {
                $this->line("{$category->name} — aucun article disponible ({$postedTodayCount}/{$target} aujourd'hui), ignoré.");
                continue;
            }

            $source = $viaCuration ? 'choix éditeur' : 'sélection auto';

            if ($poster->post($article)) {
                $article->posted_to_facebook_at = now();
                $article->save();
                $this->info("{$category->name} ({$source}, " . ($postedTodayCount + 1) . "/{$target}) — publié : {$article->title}");
            } else {
                $this->warn("{$category->name} ({$source}) — échec de publication : {$article->title}");
            }

            // One post per run — the next slot in rotation waits for the
            // next scheduled run, spreading the day's posts out.
            return self::SUCCESS;
        }

        $this->info("Toutes les rubriques ont déjà atteint leur quota Facebook du jour.");
        return self::SUCCESS;
    }
}
