<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAdvertisementRequest;
use App\Models\Advertisement;
use App\Traits\FileUploadTrait;

class AdvertisementController extends Controller
{
    use FileUploadTrait;

    public function __construct()
    {
        $this->middleware(['permission:advertisement index,admin'])->only(['index']);
        $this->middleware(['permission:advertisement create,admin'])->only(['create', 'store']);
        $this->middleware(['permission:advertisement update,admin'])->only(['edit', 'update']);
        $this->middleware(['permission:advertisement delete,admin'])->only(['destroy']);
    }

    public function index()
    {
        $advertisements = Advertisement::orderBy('placement')->orderBy('sort_order')->get();
        $placements = Advertisement::PLACEMENTS;

        return view('admin.advertisement.index', compact('advertisements', 'placements'));
    }

    public function create()
    {
        $placements = Advertisement::PLACEMENTS;

        return view('admin.advertisement.create', compact('placements'));
    }

    public function store(AdminAdvertisementRequest $request)
    {
        $advertisement = new Advertisement();
        $advertisement->placement = $request->placement;
        $advertisement->title = $request->title;
        $advertisement->description = $request->description;
        $advertisement->image = $this->handleFileUpload($request, 'image');
        $advertisement->url = $request->url;
        $advertisement->sort_order = $request->sort_order ?? 0;
        $advertisement->status = $request->status == 1 ? 1 : 0;
        $advertisement->save();

        toast(__('admin.Created Successfully'), 'success')->width('350');

        return redirect()->route('admin.advertisement.index');
    }

    public function edit(string $id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $placements = Advertisement::PLACEMENTS;

        return view('admin.advertisement.edit', compact('advertisement', 'placements'));
    }

    public function update(AdminAdvertisementRequest $request, string $id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $advertisement->placement = $request->placement;
        $advertisement->title = $request->title;
        $advertisement->description = $request->description;
        $imagePath = $this->handleFileUpload($request, 'image');
        $advertisement->image = ! empty($imagePath) ? $imagePath : $advertisement->image;
        $advertisement->url = $request->url;
        $advertisement->sort_order = $request->sort_order ?? 0;
        $advertisement->status = $request->status == 1 ? 1 : 0;
        $advertisement->save();

        toast(__('admin.Updated Successfully'), 'success')->width('350');

        return redirect()->route('admin.advertisement.index');
    }

    public function destroy(string $id)
    {
        try {
            $advertisement = Advertisement::findOrFail($id);
            $this->deleteFile($advertisement->image);
            $advertisement->delete();

            return response(['status' => 'success', 'message' => __('admin.Deleted Successfully!')]);
        } catch (\Throwable $th) {
            return response(['status' => 'error', 'message' => __('admin.something went wrong!')]);
        }
    }
}
