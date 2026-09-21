<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLanguageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('language') ?? $this->route('id');

        return [
            'lang' => ['required', 'string', 'max:10', 'unique:languages,lang,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:languages,slug,' . $id],
            'default' => ['required', 'in:0,1'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
