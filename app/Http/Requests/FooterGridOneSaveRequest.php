<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FooterGridOneSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
