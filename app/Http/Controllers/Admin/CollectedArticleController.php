<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CollectedArticle;
use App\Models\News;
use App\Services\GeminiArticleWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class CollectedArticleController extends Controller
{
    public function __construct()
    {
        // Same gate as the sidebar link — converting a source into a draft
        // article is an editorial decision reserved for full-access editors.
        $this->middleware(function ($request, $next) {
            if (! canAccess(['news all-access'])) {
                abort(404);
            }
            return $next($request);
        });
    }

    /**
     * List the RSS "veille" queue — external items staged for editorial
     * review. Nothing here is ever auto-published: an editor either
     * converts an item into a draft article (to rewrite and publish
     * normally) or dismisses it.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'new');

        $collectedArticles = CollectedArticle::with('category')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'new' => CollectedArticle::where('status', 'new')->count(),
            'converted' => CollectedArticle::where('status', 'converted')->count(),
            'dismissed' => CollectedArticle::where('status', 'dismissed')->count(),
        ];

        return view('admin.collected-articles.index', compact('collectedArticles', 'status', 'counts'));
    }

    /**
     * Turn a staged item into a real draft News row — unpublished
     * (status/is_approved = 0) and pre-filled with a clear source
     * attribution, so an editor must actively rewrite and approve it
     * before it can go live. Never copies the source text as the final
     * article body verbatim.
     */
    public function convert(CollectedArticle $collectedArticle, GeminiArticleWriter $writer)
    {
        if ($collectedArticle->status !== 'new') {
            toast('Cet article a déjà été traité.', 'warning')->width('330');
            return redirect()->back();
        }

        $category = $collectedArticle->category ?? Category::first();

        // Only called here — on an editor's explicit "Convertir" click, one
        // article at a time — never during the automated RSS poll, so usage
        // stays far under Gemini's free-tier daily quota. Returns null on
        // any failure (missing key, network error, empty response); the
        // draft falls back to the plain summary so conversion never breaks.
        $expanded = $writer->expand(
            $collectedArticle->title,
            $collectedArticle->summary ?? '',
            $collectedArticle->source_name,
            $category->slug ?? null
        );

        if ($expanded) {
            $body = collect(preg_split('/\r?\n\r?\n/', trim($expanded)))
                ->filter()
                ->map(fn ($paragraph) => '<p>' . e(trim($paragraph)) . '</p>')
                ->implode('');

            $draftContent = "<p><em>Brouillon rédigé automatiquement à partir d'une source externe — à relire avant publication.</em></p>"
                . $body
                . '<p>Source : <a href="' . e($collectedArticle->source_url) . '" target="_blank" rel="noopener">'
                . e($collectedArticle->source_name) . '</a></p>';
        } else {
            $draftContent = "<p><em>Brouillon généré à partir d'une source externe — à réécrire avant publication.</em></p>"
                . '<p>' . e($collectedArticle->summary) . '</p>'
                . '<p>Source : <a href="' . e($collectedArticle->source_url) . '" target="_blank" rel="noopener">'
                . e($collectedArticle->source_name) . '</a></p>';
        }

        // Assigned as bare properties (not News::create()) to match the rest
        // of the codebase — the News model has no $fillable declared, so
        // mass-assignment is guarded by default and create() would throw.
        $news = new News();
        $news->language = $category->language ?? 'fr';
        $news->category_id = $category->id;
        $news->auther_id = Auth::guard('admin')->id();
        $news->image = $collectedArticle->image ?: "frontend/assets/images/category-placeholders/{$category->slug}.jpg";
        $news->title = $collectedArticle->title;
        $news->slug = \Str::slug($collectedArticle->title) . '-' . \Str::random(5);
        $news->content = $draftContent;
        $news->show_at_slider = 0;
        $news->show_at_popular = 0;
        $news->show_at_journal = 0;
        $news->is_journal_lead = 0;
        $news->status = 0;
        $news->is_approved = 0;
        $news->save();

        $collectedArticle->update([
            'status' => 'converted',
            'converted_news_id' => $news->id,
        ]);

        toast('Brouillon créé — à compléter puis publier depuis "Articles".', 'success')->width('330');

        return redirect()->route('admin.news.edit', $news->id);
    }

    /**
     * Dismiss a staged item — not relevant, keeps the queue clean.
     */
    public function dismiss(CollectedArticle $collectedArticle)
    {
        $collectedArticle->update(['status' => 'dismissed']);

        toast('Écarté de la file de relecture.', 'success')->width('330');

        return redirect()->back();
    }

    /**
     * Run the RSS poll immediately instead of waiting for the next
     * scheduled run — handy right after adding/editing a source.
     */
    public function collectNow()
    {
        Artisan::call('news:collect');

        toast('Veille relancée : ' . trim(Artisan::output()), 'success')->width('380');

        return redirect()->route('admin.veille.index');
    }
}
