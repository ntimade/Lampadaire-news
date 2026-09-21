<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLanguageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lang' => ['required', 'string', 'max:10', 'unique:languages,lang'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:languages,slug'],
            'default' => ['required', 'in:0,1'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
