<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use FileUploadTrait;

    /**
     * Display the resource.
     */
    public function index()
    {
        return view('admin.setting.index');
    }

    /**
     * Update the general settings (site name, logo, favicon).
     */
    public function updateGeneralSetting(Request $request)
    {
        $this->setSetting('site_name', $request->site_name);

        if ($request->hasFile('site_logo')) {
            $this->setSetting('site_logo', $this->handleFileUpload($request, 'site_logo'));
        }

        if ($request->hasFile('site_favicon')) {
            $this->setSetting('site_favicon', $this->handleFileUpload($request, 'site_favicon'));
        }

        $this->setSetting('breaking_bar_enabled', $request->breaking_bar_enabled == 1 ? '1' : '0');

        toast(__('admin.Updated Successfully'), 'success')->width('350');

        return redirect()->back();
    }

    /**
     * Update the SEO settings.
     */
    public function updateSeoSetting(Request $request)
    {
        $this->setSetting('site_seo_title', $request->site_seo_title);
        $this->setSetting('site_seo_description', $request->site_seo_description);
        $this->setSetting('site_seo_keywords', $request->site_seo_keywords);

        toast(__('admin.Updated Successfully'), 'success')->width('350');

        return redirect()->back();
    }

    /**
     * Update the appearance settings.
     */
    public function updateAppearanceSetting(Request $request)
    {
        $this->setSetting('site_color', $request->site_color);

        toast(__('admin.Updated Successfully'), 'success')->width('350');

        return redirect()->back();
    }

    /**
     * Update the Microsoft API settings.
     */
    public function updateMicrosoftApiSetting(Request $request)
    {
        $this->setSetting('site_microsoft_api_host', $request->site_microsoft_api_host);
        $this->setSetting('site_microsoft_api_key', $request->site_microsoft_api_key);

        toast(__('admin.Updated Successfully'), 'success')->width('350');

        return redirect()->back();
    }

    private function setSetting(string $key, $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
