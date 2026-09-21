<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\UrgentNews;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $defaults = [
                'site_name' => config('app.name'),
                'site_logo' => '',
                'site_favicon' => '',
                'site_color' => '#f0740b',
                'site_seo_title' => config('app.name'),
                'site_seo_description' => '',
                'site_seo_keywords' => '',
                'site_microsoft_api_host' => '',
                'site_microsoft_api_key' => '',
                'breaking_bar_enabled' => '1',
            ];

            $view->with('settings', array_merge($defaults, Setting::pluck('value', 'key')->all()));
        });

        // Dedicated "Urgent" ticker items — decoupled from the News model on
        // purpose (see App\Models\UrgentNews). Only items an editor has
        // explicitly ticked "Mettre en ligne" ever reach the public bar.
        View::composer('frontend.home-components.breaking-news', function ($view) {
            $urgentNews = UrgentNews::where('is_published', 1)
                ->orderByDesc('id')
                ->take(10)
                ->get();

            $view->with('urgentNews', $urgentNews);
        });
    }
}
