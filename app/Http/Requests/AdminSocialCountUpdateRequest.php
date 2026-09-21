<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSocialCountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string'],
            'icon' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048'],
            'fan_count' => ['required', 'string', 'max:255'],
            'fan_type' => ['required', 'string', 'max:255'],
            'button_text' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
