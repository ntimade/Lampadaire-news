<?php

use App\Models\Language;
use App\Models\Setting;
use PhpParser\Node\Expr\Cast\String_;

/** format news tags */

function formatTags(array $tags): String
{
   return implode(',', $tags);
}

/**
 * asset() with a cache-busting "?v=<file mtime>" suffix.
 *
 * The theme's CSS/JS are plain static files (no build step / mix-manifest),
 * so after a redeploy a browser keeps serving the previously cached copy
 * until it happens to revalidate. Appending the file's mtime gives each
 * change a fresh URL, so edits actually reach visitors.
 */
function asset_v(string $path): string
{
    $absolute = public_path($path);
    $version = is_file($absolute) ? filemtime($absolute) : null;

    return asset($path) . ($version ? '?v=' . $version : '');
}

/**
 * Get the active language code.
 *
 * Validates the session value against the languages actually configured on
 * the site before trusting it — otherwise a stale session (e.g. from before
 * the site was locked to French-only) could keep forcing English forever,
 * even though no English option exists in the admin anymore.
 */
function getLangauge(): string
{
    $sessionLanguage = session('language');

    if ($sessionLanguage && Language::where('lang', $sessionLanguage)->where('status', 1)->exists()) {
        return $sessionLanguage;
    }

    $language = Language::where('default', 1)->where('status', 1)->first();

    if ($language) {
        setLanguage($language->lang);

        return $language->lang;
    }

    // Last-resort fallback: the site's only real content language is
    // French, so default to it instead of English.
    setLanguage('fr');

    return 'fr';
}

/** set language code in session */
function setLanguage(string $code): void
{
    session(['language' => $code]);
}

/** Truncate text */

function truncate(string $text, int $limit = 45): String
{
    return \Str::limit($text, $limit, '...');
}

/** Convert a number in K format */

function convertToKFormat(int $number): String
{
    if($number < 1000){
        return $number;
    }elseif($number < 1000000){
        return round($number / 1000, 1) . 'K';
    }else {
        return round($number / 1000000, 1). 'M';
    }
}

/** Make Sidebar Active */

function setSidebarActive(array $routes): ?string
{
    foreach($routes as $route){
        if(request()->routeIs($route)){
            return 'active';
        }
    }
    return '';
}

/** get Setting */

function getSetting($key){
    $data = Setting::where('key', $key)->first();
    return $data->value;
}

/** check permission */

function canAccess(array $permissions){

   $permission = auth()->guard('admin')->user()->hasAnyPermission($permissions);
   $superAdmin = auth()->guard('admin')->user()->hasRole('Super Admin');

   if($permission || $superAdmin){
    return true;
   }else {
    return false;
   }

}

/** get admin role */

function getRole(){
    $role = auth()->guard('admin')->user()->getRoleNames();
    return $role->first();
}

/** check user permission */

function checkPermission(string $permission){
    return auth()->guard('admin')->user()->hasPermissionTo($permission);
}
