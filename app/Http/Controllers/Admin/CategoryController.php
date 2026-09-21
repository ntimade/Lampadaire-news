<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:category index,admin'])->only(['index']);
        $this->middleware(['permission:category create,admin'])->only(['create', 'store']);
        $this->middleware(['permission:category update,admin'])->only(['edit', 'update']);
        $this->middleware(['permission:category delete,admin'])->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::all();
        return view('admin.category.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = Language::all();
        return view('admin.category.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'language' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'show_at_nav' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $category = new Category();
        $category->language = $request->language;
        $category->name = $request->name;
        $category->slug = \Str::slug($request->name);
        $category->show_at_nav = $request->show_at_nav;
        $category->status = $request->status;
        $category->save();

        toast(__('admin.Created Successfully'), 'success')->width('350');

        return redirect()->route('admin.category.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $languages = Language::all();
        return view('admin.category.edit', compact('category', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'language' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'show_at_nav' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $category = Category::findOrFail($id);
        $category->language = $request->language;
        $category->name = $request->name;
        $category->slug = \Str::slug($request->name);
        $category->show_at_nav = $request->show_at_nav;
        $category->status = $request->status;
        $category->save();

        toast(__('admin.Updated Successfully'), 'success')->width('350');

        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return response(['status' => 'success', 'message' => __('admin.Deleted Successfully!')]);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => __('admin.something went wrong!')]);
        }
    }
}
