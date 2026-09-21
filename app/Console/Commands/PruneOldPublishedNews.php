<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

class PruneOldPublishedNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:prune {--dry-run : List what would be deleted without deleting anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DESTRUCTIVE, explicitly requested: permanently delete EVERY published article older than 48h — including hand-written ones, no exception. Le Lampadaire is meant to only ever show ~48h of history.';

    protected int $retentionHours = 48;

    public function handle(): int
    {
        $cutoff = now()->subHours($this->retentionHours);

        $stale = News::where('status', 1)
            ->where('is_approved', 1)
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($stale->isEmpty()) {
            $this->info('Rien à supprimer — aucun article publié de plus de ' . $this->retentionHours . 'h.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("[Simulation] {$stale->count()} article(s) SERAIENT supprimé(s) :");
            foreach ($stale as $news) {
                $this->line("  #{$news->id} — {$news->title} (publié le {$news->created_at->format('d/m/Y H:i')})");
            }
            return self::SUCCESS;
        }

        foreach ($stale as $news) {
            $imagePath = $news->image;
            $news->tags()->detach();
            $news->delete();
            $this->deleteImageIfOrphan($imagePath);
            $this->line("Supprimé : #{$news->id} — {$news->title}");
        }

        $this->info("{$stale->count()} article(s) supprimé(s) (plus de {$this->retentionHours}h).");

        return self::SUCCESS;
    }

    /**
     * Best-effort cleanup of an expired article's image — never fatal.
     *
     * Only ever deletes a *per-article* image: an RSS-downloaded picture
     * (frontend/assets/images/collected/…) or an admin upload (storage/…).
     * Everything else under frontend/assets/ is a shared theme/brand asset —
     * most importantly the category-placeholders/*.jpg fallbacks, which are
     * reused by dozens of articles at once. Deleting one of those because a
     * single article expired used to silently break the image on every other
     * article still pointing at it.
     */
    protected function deleteImageIfOrphan(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $isPerArticleImage = str_contains($relativePath, 'frontend/assets/images/collected/')
            || str_starts_with($relativePath, 'storage/');

        if (! $isPerArticleImage) {
            return;
        }

        // Called after the article row is already deleted, so any remaining
        // match means another article still uses this exact file — leave it.
        if (News::where('image', $relativePath)->exists()) {
            return;
        }

        $absolute = public_path($relativePath);

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }
}
