<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\CollectedArticle;
use App\Models\Language;
use App\Models\News;
use App\Models\SocialLink;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index() : View
    {
        $publishedNews = News::where(['status' => 1, 'is_approved' => 1])->count();
        $pendingNews = News::where(['status' => 1, 'is_approved' => 0])->count();
        $Categories = Category::count();
        $languages = Language::count();
        $roles = Role::count();
        $permissions = Permission::count();
        $socials = SocialLink::count();
        $subscribers = Subscriber::count();

        $pendingNewsList = News::with(['category', 'auther'])
            ->where('is_approved', 0)
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $recentNewsList = News::with(['category', 'auther'])
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $totalViews = News::sum('views');
        $collectedNewCount = CollectedArticle::where('status', 'new')->count();

        return view('admin.dashboard.index', compact(
            'publishedNews',
            'pendingNews',
            'Categories',
            'languages',
            'roles',
            'permissions',
            'socials',
            'subscribers',
            'pendingNewsList',
            'recentNewsList',
            'totalViews',
            'collectedNewCount'
        ));
    }
}
