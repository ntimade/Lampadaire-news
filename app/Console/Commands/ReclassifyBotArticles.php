<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Category;
use App\Models\News;
use App\Services\GeminiArticleWriter;
use Illuminate\Console\Command;

class ReclassifyBotArticles extends Command
{
    /**
     * One-off audit/repair command: news:auto-publish now re-checks the
     * category via Gemini before publishing, but everything it published
     * before that fix inherited whatever the RSS-tag/title-keyword guess
     * said — which mis-filed several real stories (politics under
     * "Business", conservation under "Finance et Bourse", a football
     * transfer under "International"...). This corrects the backlog using
     * the same classify() call, without touching already-correct articles.
     *
     * @var string
     */
    protected $signature = 'news:reclassify-bot-articles';

    protected string $botEmail = 'veille-auto@lelampadaire.local';

    public function handle(GeminiArticleWriter $writer): int
    {
        $bot = Admin::where('email', $this->botEmail)->first();

        if (! $bot) {
            $this->error("Compte '{$this->botEmail}' introuvable.");
            return self::FAILURE;
        }

        $articles = News::with('category')->where('auther_id', $bot->id)->get();
        $corrected = 0;
        $unchanged = 0;
        $failed = 0;

        foreach ($articles as $news) {
            $suggestedSlug = $writer->classify($news->title, strip_tags($news->content));

            if (! $suggestedSlug) {
                $failed++;
                // classify() always shares the single default key across
                // every article (unlike expand(), which spreads across 8) —
                // a burst of calls can hit its per-minute rate limit. A
                // short pause before the next one lets it recover instead
                // of failing the rest of the whole backlog outright.
                sleep(3);
                continue;
            }

            sleep(1);

            if ($news->category && $suggestedSlug === $news->category->slug) {
                $unchanged++;
                continue;
            }

            $newCategory = Category::where('slug', $suggestedSlug)->first();

            if (! $newCategory) {
                $failed++;
                continue;
            }

            $oldName = $news->category->name ?? '—';
            $news->category_id = $newCategory->id;
            $news->save();

            $this->line("#{$news->id} {$oldName} -> {$newCategory->name} : {$news->title}");
            $corrected++;
        }

        $this->info("Terminé. {$corrected} article(s) recatégorisé(s), {$unchanged} déjà correct(s), {$failed} échec(s) (retentables plus tard).");

        return self::SUCCESS;
    }
}
