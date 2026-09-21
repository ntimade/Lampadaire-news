<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSocialLinkStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
