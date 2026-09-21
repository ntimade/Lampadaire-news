<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAdUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'home_top_bar_ad' => ['nullable', 'image', 'max:4096'],
            'home_middle_ad' => ['nullable', 'image', 'max:4096'],
            'view_page_ad' => ['nullable', 'image', 'max:4096'],
            'news_page_ad' => ['nullable', 'image', 'max:4096'],
            'side_bar_ad' => ['nullable', 'image', 'max:4096'],
            'home_top_bar_ad_url' => ['nullable', 'string', 'max:2048'],
            'home_middle_ad_url' => ['nullable', 'string', 'max:2048'],
            'view_page_ad_url' => ['nullable', 'string', 'max:2048'],
            'news_page_ad_url' => ['nullable', 'string', 'max:2048'],
            'side_bar_ad_url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
