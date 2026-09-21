<?php

namespace App\Http\Requests;

use App\Models\Advertisement;
use Illuminate\Foundation\Http\FormRequest;

class AdminAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'placement' => ['required', 'string', 'in:' . implode(',', array_keys(Advertisement::PLACEMENTS))],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => [$this->isMethod('post') && ! $this->route('advertisement') ? 'required' : 'nullable', 'image', 'max:4096'],
            'url' => ['required', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
