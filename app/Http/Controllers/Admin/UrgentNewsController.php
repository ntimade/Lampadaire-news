<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrgentNews;
use Illuminate\Http\Request;

class UrgentNewsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:urgent news index,admin'])->only(['index']);
        $this->middleware(['permission:urgent news create,admin'])->only(['create', 'store']);
        $this->middleware(['permission:urgent news update,admin'])->only(['edit', 'update', 'toggle']);
        $this->middleware(['permission:urgent news delete,admin'])->only(['destroy']);
    }

    /**
     * Dedicated "Urgent" ticker items — deliberately separate from the News
     * model. Writing a flash for the breaking bar no longer requires
     * creating a full article (category, image, body...); the rédacteur
     * just writes the text and ticks "Mettre en ligne". Only checked items
     * are ever queried by the public ticker (see the frontend.
     * home-components.breaking-news view composer in AppServiceProvider).
     */
    public function index()
    {
        $urgentNews = UrgentNews::with('admin')->orderByDesc('id')->get();
        return view('admin.urgent-news.index', compact('urgentNews'));
    }

    public function create()
    {
        return view('admin.urgent-news.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
        ]);

        UrgentNews::create([
            'text' => $request->text,
            'link' => $request->link,
            'is_published' => $request->boolean('is_published'),
            'admin_id' => auth()->guard('admin')->id(),
        ]);

        toast(__('admin.Created Successfully!'), 'success');

        return redirect()->route('admin.urgent-news.index');
    }

    public function edit(string $id)
    {
        $urgentNews = UrgentNews::findOrFail($id);
        return view('admin.urgent-news.edit', compact('urgentNews'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
        ]);

        $urgentNews = UrgentNews::findOrFail($id);
        $urgentNews->text = $request->text;
        $urgentNews->link = $request->link;
        $urgentNews->is_published = $request->boolean('is_published');
        $urgentNews->save();

        toast(__('admin.Update Successfully!'), 'success');

        return redirect()->route('admin.urgent-news.index');
    }

    /**
     * Quick inline "Mettre en ligne" toggle from the listing table, without
     * a full edit round-trip — mirrors NewsController::toggleNewsStatus.
     */
    public function toggle(Request $request)
    {
        $urgentNews = UrgentNews::findOrFail($request->id);
        $urgentNews->is_published = $request->status;
        $urgentNews->save();

        return response(['status' => 'success', 'message' => __('admin.Updated successfully!')]);
    }

    public function destroy(string $id)
    {
        $urgentNews = UrgentNews::findOrFail($id);
        $urgentNews->delete();

        return response(['status' => 'success', 'message' => __('admin.Deleted Successfully!')]);
    }
}
