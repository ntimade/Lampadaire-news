<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Category;
use App\Models\CollectedArticle;
use App\Models\News;
use App\Services\GeminiArticleWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AutoPublishCollectedNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:auto-publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expand every "new" collected RSS item into a full article via Gemini (per-category key) and publish it live immediately — no human review. Explicitly requested; the site normally requires admin approval, this bypasses it on purpose for this pipeline only.';

    /**
     * Author of record for every article this command publishes — lets an
     * admin tell auto-published content apart from human-written articles
     * at a glance in "Tous les articles".
     */
    protected string $botEmail = 'veille-auto@lelampadaire.local';

    public function handle(GeminiArticleWriter $writer): int
    {
        $bot = Admin::where('email', $this->botEmail)->first();

        if (! $bot) {
            $this->error("Compte auteur '{$this->botEmail}' introuvable — exécutez la création une fois avant d'activer cette commande.");
            return self::FAILURE;
        }

        $items = CollectedArticle::with('category')->where('status', 'new')->get();
        $published = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $category = $item->category;

            if (! $category) {
                $skipped++;
                continue;
            }

            // classify() always shares the single default key across every
            // item (unlike expand(), which spreads across 8 per-category
            // keys) AND that key's free-tier quota is only 20 requests/DAY
            // — not per-minute (found the hard way: a blanket reclassify
            // pass exhausted it in one go). So it's only worth spending on
            // items whose category guess is already known to be shaky —
            // a real <category> tag match ('tag' confidence) is reliable
            // enough to skip re-checking.
            if ($item->category_confidence !== 'tag') {
                $suggestedSlug = $writer->classify($item->title, $item->summary ?? '');
                if ($suggestedSlug && $suggestedSlug !== $category->slug) {
                    $reclassified = Category::where('slug', $suggestedSlug)->first();
                    if ($reclassified) {
                        $this->line("Reclassé : « {$item->title} » {$category->name} -> {$reclassified->name}");
                        $category = $reclassified;
                    }
                }
                sleep(2);
            }

            $expanded = $writer->expand($item->title, $item->summary ?? '', $item->source_name, $category->slug);

            if (! $expanded) {
                // Left as 'new' on purpose — retried automatically on the next run
                // (missing key, quota hit, or a truncated generation this time).
                $skipped++;
                continue;
            }

            $body = collect(preg_split('/\r?\n\r?\n/', trim($expanded)))
                ->filter()
                ->map(fn ($paragraph) => '<p>' . e(trim($paragraph)) . '</p>')
                ->implode('');

            $content = $body
                . '<p><em>Source : <a href="' . e($item->source_url) . '" target="_blank" rel="noopener">'
                . e($item->source_name) . '</a></em></p>';

            $news = new News();
            $news->language = $category->language ?? 'fr';
            $news->category_id = $category->id;
            $news->auther_id = $bot->id;
            $news->image = $item->image ?: "frontend/assets/images/category-placeholders/{$category->slug}.jpg";
            $news->title = $item->title;
            $news->slug = Str::slug($item->title) . '-' . Str::random(5);
            $news->content = $content;
            $news->show_at_slider = 0;
            $news->show_at_popular = 0;
            // Curated journal selection: a handful of stale flags from
            // earlier manual testing were locking the whole edition to just
            // those 3 articles (see NewspaperController — "any curated
            // article exists" switches the query to *only* curated ones).
            // Every fresh auto-published article opts in so the journal
            // actually reflects the live site instead of stale test data.
            $news->show_at_journal = 1;
            $news->is_journal_lead = 0;
            // Published immediately — the explicitly-requested behaviour for
            // this pipeline. Every other creation path in the app still
            // requires admin approval; only this automated route bypasses it.
            $news->status = 1;
            $news->is_approved = 1;
            $news->published_at = now();
            $news->save();

            $item->update([
                'status' => 'converted',
                'converted_news_id' => $news->id,
                'category_id' => $category->id,
            ]);

            $published++;
            $this->line("Publié : [{$category->name}] {$item->title}");
        }

        $this->info("Terminé. {$published} article(s) publié(s) automatiquement, {$skipped} laissé(s) en attente (pas de clé/erreur, retenté au prochain passage).");

        return self::SUCCESS;
    }
}
