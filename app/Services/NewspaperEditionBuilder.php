<?php

namespace App\Services;

use App\Models\Category;
use App\Models\News;
use Illuminate\Support\Collection;

/**
 * Picks which articles make up "today's edition" (cover lead + teasers +
 * one themed inner page per rubrique group) — shared between
 * NewspaperController (renders the PDF) and GeneratePrintExcerpts (pre-warms
 * News::print_excerpt for those exact articles ahead of time, via the CLI
 * so it's never blocked by the web request's execution-time limit).
 *
 * Editors curate the edition explicitly via "Inclure dans le journal" /
 * "À la Une" on each article — when at least one is flagged, only flagged
 * articles are eligible; otherwise every active article is, so an edition
 * never comes out empty.
 */
class NewspaperEditionBuilder
{
    /**
     * Category slugs grouped into inner pages, one group per page — mirrors
     * a real multi-section newspaper (Politique, Économie, Santé, Culture,
     * Sport...) instead of one long undifferentiated feed. Order here is
     * the page order in the PDF.
     */
    protected array $pageGroups = [
        'Politique & Faits Divers' => ['politique-faits-divers'],
        'Finance & Bourse' => ['finance-et-bourse'],
        'Business' => ['business'],
        'Santé & Prévention' => ['sante-prevention'],
        'Culture' => ['culture'],
        'Religion & Spiritualité' => ['religion-spiritualite'],
        'International' => ['international'],
        'Sport' => ['sport'],
    ];

    /**
     * Hard cap on how many distinct articles the whole edition uses —
     * explicitly requested to keep each edition compact.
     */
    protected int $maxArticles = 8;

    /**
     * @return array{lead: ?News, teasers: Collection, pages: array}
     */
    public function build(): array
    {
        $baseQuery = News::with(['category', 'auther'])
            ->activeEntries()
            ->withLocalize();

        $isCurated = (clone $baseQuery)->where('show_at_journal', 1)->exists();

        $pool = (clone $baseQuery)
            ->when($isCurated, fn ($q) => $q->where('show_at_journal', 1))
            ->orderByDesc('is_journal_lead')
            ->orderByDesc('is_breaking_news')
            ->orderByDesc('show_at_popular')
            ->orderByDesc('show_at_slider')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->get();

        $usedIds = [];

        $take = function (int $categoryId, int $count) use ($pool, &$usedIds) {
            $remaining = $this->maxArticles - count($usedIds);
            if ($remaining <= 0) {
                return collect();
            }

            $picked = $pool->where('category_id', $categoryId)
                ->whereNotIn('id', $usedIds)
                ->take(min($count, $remaining));

            foreach ($picked as $item) {
                $usedIds[] = $item->id;
            }

            return $picked->values();
        };

        // Cover lead: the single most important story overall.
        $lead = $pool->whereNotIn('id', $usedIds)->first();
        if ($lead) {
            $usedIds[] = $lead->id;
        }

        // Cover teasers: one story each from up to 3 *other* categories, so
        // the front page previews the range of sections inside rather than
        // repeating the lead's own topic (capped by the remaining budget).
        $teaserCategoryIds = $pool->whereNotIn('id', $usedIds)
            ->pluck('category_id')
            ->unique()
            ->reject(fn ($id) => $lead && $id === $lead->category_id)
            ->take(min(3, max(0, $this->maxArticles - count($usedIds))));

        $teasers = collect();
        foreach ($teaserCategoryIds as $categoryId) {
            $teasers = $teasers->merge($take($categoryId, 1));
        }

        // One themed inner page per group in $pageGroups, each with a
        // feature story per category in the group plus a small grid.
        $categoriesBySlug = Category::whereIn('slug', collect($this->pageGroups)->flatten())
            ->get()
            ->keyBy('slug');

        $pages = [];
        foreach ($this->pageGroups as $pageTitle => $slugs) {
            if (count($usedIds) >= $this->maxArticles) {
                break;
            }

            $sections = [];

            foreach ($slugs as $slug) {
                $category = $categoriesBySlug->get($slug);
                if (! $category) {
                    continue;
                }

                $feature = $take($category->id, 1)->first();
                if (! $feature) {
                    continue;
                }

                $grid = $take($category->id, 3);

                $sections[] = [
                    'category' => $category,
                    'feature' => $feature,
                    'grid' => $grid,
                ];
            }

            if (! empty($sections)) {
                $pages[] = ['title' => $pageTitle, 'sections' => $sections];
            }
        }

        return ['lead' => $lead, 'teasers' => $teasers, 'pages' => $pages];
    }

    /**
     * Every article a built edition actually uses, flattened and
     * deduplicated — used to backfill News::print_excerpt ahead of time.
     */
    public function articlesIn(array $edition): Collection
    {
        $all = collect();

        if ($edition['lead']) {
            $all->push($edition['lead']);
        }

        $all = $all->merge($edition['teasers']);

        foreach ($edition['pages'] as $page) {
            foreach ($page['sections'] as $section) {
                $all->push($section['feature']);
                $all = $all->merge($section['grid']);
            }
        }

        return $all->unique('id')->values();
    }
}
