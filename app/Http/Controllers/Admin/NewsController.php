<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminNewsCreateRequest;
use App\Http\Requests\AdminNewsUpdateRequest;
use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use App\Models\Tag;
use App\Traits\FileUploadTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    use FileUploadTrait;

    public function __construct()
    {
        $this->middleware(['permission:news index,admin'])->only(['index', 'copyNews']);
        $this->middleware(['permission:news create,admin'])->only(['create', 'store']);
        $this->middleware(['permission:news update,admin'])->only(['edit', 'update']);
        $this->middleware(['permission:news delete,admin'])->only(['destroy']);
        $this->middleware(['permission:news all-access,admin'])->only(['toggleNewsStatus']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::all();
        return view('admin.news.index', compact('languages'));
    }

    public function pendingNews(): View
    {
        $languages = Language::all();
        return view('admin.pending-news.index', compact('languages'));
    }


    /**
     * Fetch category depending on language
     */
    public function fetchCategory(Request $request)
    {
        $categories = Category::where('language', $request->lang)->get();
        return $categories;
    }

    function approveNews(Request $request): Response
    {
        $news = News::findOrFail($request->id);
        $wasApproved = $news->is_approved == 1;
        $news->is_approved = $request->is_approve;

        // Stamp the real publication moment the first time an article is approved,
        // so listings/the newspaper can order by true publish order instead of
        // created_at (which only reflects when the draft was first saved).
        if ($news->is_approved == 1 && ! $wasApproved && empty($news->published_at)) {
            $news->published_at = now();
        }

        $news->save();

        return response(['status' => 'success', 'message' => __('admin.Updated Successfully')]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = Language::all();
        return view('admin.news.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminNewsCreateRequest $request)
    {
        /** Handle image */
        $imagePath = $this->handleFileUpload($request, 'image');

        $news = new News();
        $news->language = $request->language;
        $news->category_id = $request->category;
        $news->auther_id = Auth::guard('admin')->user()->id;
        $news->image = $imagePath;
        $news->title = $request->title;
        $news->slug = \Str::slug($request->title);
        $news->content = $request->content;
        $news->meta_title = $request->meta_title;
        $news->meta_description = $request->meta_description;
        $news->show_at_slider = $request->show_at_slider == 1 ? 1 : 0;
        $news->show_at_popular = $request->show_at_popular == 1 ? 1 : 0;
        $news->show_at_journal = $request->show_at_journal == 1 ? 1 : 0;
        $news->is_journal_lead = $request->is_journal_lead == 1 ? 1 : 0;
        $news->post_to_facebook = $request->post_to_facebook == 1 ? 1 : 0;
        $news->status = $request->status == 1 ? 1 : 0;
        $news->is_approved = getRole() == 'Super Admin' || checkPermission('news all-access') ? 1 : 0;
        $news->published_at = $news->is_approved == 1 ? now() : null;
        $news->save();

        // Only one article can be the front-page lead at a time.
        if ($news->is_journal_lead == 1) {
            News::where('id', '!=', $news->id)->update(['is_journal_lead' => 0]);
        }

        $tagIds = [];

        foreach ($this->parseTags($request->tags) as $tag) {
            $tagIds[] = Tag::firstOrCreate(['name' => $tag, 'language' => $news->language])->id;
        }

        $news->tags()->attach($tagIds);


        toast(__('admin.Created Successfully!'), 'success')->width('330');

        return redirect()->route('admin.news.index');
    }

    /**
     * Change toggle status of news
     */
    public function toggleNewsStatus(Request $request)
    {
        try {
            $news = News::findOrFail($request->id);
            $news->{$request->name} = $request->status;
            $news->save();

            return response(['status' => 'success', 'message' => __('admin.Updated successfully!')]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $languages = Language::all();
        $news = News::findOrFail($id);
        
        if(!canAccess(['news all-access'])){
            if($news->auther_id != auth()->guard('admin')->user()->id){
                return abort(404);
            }
        }

        $categories = Category::where('language', $news->language)->get();

        return view('admin.news.edit', compact('languages', 'news', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminNewsUpdateRequest $request, string $id)
    {

        $news = News::findOrFail($id);

        $isOwnArticle = $news->auther_id == auth()->guard('admin')->user()->id;
        $canEditAny = getRole() == 'Super Admin' || checkPermission('news all-access');

        if (! $isOwnArticle && ! $canEditAny) {
            return abort(404);
        }

        /** Handle image */
        $imagePath = $this->handleFileUpload($request, 'image');

        $news->language = $request->language;
        $news->category_id = $request->category;
        $news->image = !empty($imagePath) ? $imagePath : $news->image;
        $news->title = $request->title;
        $news->slug = \Str::slug($request->title);

        // The cached print_excerpt (see NewspaperController) was written
        // for the *old* body — an edit here would otherwise leave the PDF
        // newspaper silently showing stale text forever.
        if ($news->content !== $request->content) {
            $news->print_excerpt = null;
        }
        $news->content = $request->content;
        $news->meta_title = $request->meta_title;
        $news->meta_description = $request->meta_description;
        $news->show_at_slider = $request->show_at_slider == 1 ? 1 : 0;
        $news->show_at_popular = $request->show_at_popular == 1 ? 1 : 0;
        $news->show_at_journal = $request->show_at_journal == 1 ? 1 : 0;
        $news->is_journal_lead = $request->is_journal_lead == 1 ? 1 : 0;
        $news->post_to_facebook = $request->post_to_facebook == 1 ? 1 : 0;
        $news->status = $request->status == 1 ? 1 : 0;
        $news->save();

        // Only one article can be the front-page lead at a time.
        if ($news->is_journal_lead == 1) {
            News::where('id', '!=', $news->id)->update(['is_journal_lead' => 0]);
        }

        /** detach previous tags from the pivot table (does not delete the shared Tag records) */
        $news->tags()->detach();

        $tagIds = [];

        foreach ($this->parseTags($request->tags) as $tag) {
            $tagIds[] = Tag::firstOrCreate(['name' => $tag, 'language' => $news->language])->id;
        }

        $news->tags()->attach($tagIds);


        toast(__('admin.Update Successfully!'), 'success')->width('330');

        return redirect()->route('admin.news.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $news = News::findOrFail($id);
        $this->deleteFile($news->image);
        $news->tags()->detach();
        $news->delete();

        return response(['status' => 'success', 'message' => __('admin.Deleted Successfully!')]);
    }

    /**
     * Copy news
     */
    public function copyNews(string $id)
    {
        $news = News::findOrFail($id);
        $copyNews = $news->replicate();
        $copyNews->save();

        toast(__('admin.Copied Successfully!'), 'success');

        return redirect()->back();
    }

    /**
     * Split a comma-separated tags string into trimmed, non-empty, unique tag names.
     */
    private function parseTags(?string $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $tags)))));
    }
}
