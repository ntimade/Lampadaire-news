<?php

namespace App\Console\Commands;

use App\Services\GeminiArticleWriter;
use App\Services\NewspaperEditionBuilder;
use Illuminate\Console\Command;

/**
 * Pre-warms News::print_excerpt for whichever articles today's PDF
 * newspaper edition would actually use, via the dedicated journal Gemini
 * key — so a reader downloading the journal never waits on (or times out
 * on) a live Gemini call. Safe to run often: an article with an excerpt
 * already cached is skipped instantly, so this only ever spends the
 * quota on genuinely new articles.
 */
class GeneratePrintExcerpts extends Command
{
    protected $signature = 'news:generate-print-excerpts';

    protected $description = "Pré-génère (Gemini, clé dédiée) le résumé imprimé des articles de l'édition du journal PDF.";

    /**
     * A handful per run is plenty — the edition only ever holds 8 articles
     * total, and this runs on the same 30-minute schedule as the RSS
     * pipeline, so a fresh edition catches up within an hour or two without
     * risking the dedicated key's 20/day quota on a single run.
     */
    protected int $maxPerRun = 6;

    public function handle(NewspaperEditionBuilder $editionBuilder, GeminiArticleWriter $writer): int
    {
        $articles = $editionBuilder->articlesIn($editionBuilder->build())
            ->whereNull('print_excerpt')
            ->take($this->maxPerRun);

        if ($articles->isEmpty()) {
            $this->info("Rien à faire — l'édition actuelle a déjà son résumé imprimé.");
            return self::SUCCESS;
        }

        $done = 0;

        foreach ($articles as $article) {
            $plainContent = trim(strip_tags($article->content));
            if ($plainContent === '') {
                continue;
            }

            $excerpt = $writer->writeForPrint($article->title, mb_substr($plainContent, 0, 6000));

            if ($excerpt === null) {
                $this->warn("Échec — #{$article->id} : {$article->title}");
                continue;
            }

            $article->print_excerpt = $excerpt;
            $article->save();
            $done++;
            $this->line("Résumé généré — #{$article->id} : {$article->title}");

            // Gentle pacing between calls, same convention as the other
            // Gemini-calling commands in this project.
            sleep(1);
        }

        $this->info("{$done}/{$articles->count()} résumé(s) généré(s).");

        return self::SUCCESS;
    }
}
