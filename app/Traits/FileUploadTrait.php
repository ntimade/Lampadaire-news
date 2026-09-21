<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{
    /**
     * Store an uploaded file on the public disk and return its public path.
     * Falls back to $oldPath when no file was uploaded for this field.
     */
    public function handleFileUpload(Request $request, string $field, ?string $oldPath = '')
    {
        if (! $request->hasFile($field)) {
            return $oldPath ?? '';
        }

        $path = $request->file($field)->store('uploads/'.$field, 'public');

        return 'storage/'.$path;
    }

    /**
     * Delete a previously uploaded file given its public path.
     */
    public function deleteFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $relative = preg_replace('#^storage/#', '', $path);

        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
