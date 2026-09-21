<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Admin;
use App\Models\Category;
use App\Models\FooterInfo;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $language = Language::firstOrCreate(
            ['lang' => 'fr'],
            ['name' => 'Français', 'slug' => 'fr', 'default' => 1, 'status' => 1]
        );

        // Featured pillars appear directly in the main nav; the rest live under the "More" dropdown
        // to avoid an overcrowded, uneven-wrapping menu bar.
        $pillars = [
            'Culture' => 1,
            'Finance et Bourse' => 1,
            'Business' => 1,
            'Santé & prévention' => 0,
            'Politique & faits divers' => 0,
            'Religion & spiritualité' => 0,
            'International' => 0,
            'Sport' => 0,
        ];

        foreach ($pillars as $pillar => $showAtNav) {
            Category::firstOrCreate(
                ['language' => $language->lang, 'slug' => \Str::slug($pillar)],
                ['name' => $pillar, 'show_at_nav' => $showAtNav, 'status' => 1]
            );
        }

        $permissions = [
            'news index', 'news create', 'news update', 'news delete', 'news all-access',
            'languages index', 'languages create', 'languages update', 'languages delete',
            'advertisement index', 'advertisement create', 'advertisement update', 'advertisement delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdminRole->syncPermissions($permissions);

        // Rédacteur : peut créer/modifier des articles, mais ne les publie pas directement —
        // ses articles restent "en attente" jusqu'à validation par un Super Admin
        // (voir NewsController::store, qui n'auto-approuve que Super Admin / "news all-access").
        $editorRole = Role::firstOrCreate(['name' => 'Rédacteur', 'guard_name' => 'admin']);
        $editorRole->syncPermissions(['news index', 'news create', 'news update']);

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@newsportal.test'],
            [
                'image' => '',
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 1,
            ]
        );

        if (! $admin->hasRole('Super Admin')) {
            $admin->assignRole($superAdminRole);
        }

        FooterInfo::firstOrCreate(
            ['language' => $language->lang],
            [
                'logo' => '',
                'description' => "Média culturel, économique et rassembleur — l'actualité du Cameroun racontée avec rigueur : culture, business, finance et vie de la nation.",
                'copyright' => '&copy; ' . date('Y') . ' ' . config('app.name') . '. Tous droits réservés.',
            ]
        );

        if (Ad::count() === 0) {
            Ad::create([
                'home_top_bar_ad' => '',
                'home_middle_ad' => '',
                'view_page_ad' => '',
                'news_page_ad' => '',
                'side_bar_ad' => '',
                'home_top_bar_ad_status' => 0,
                'home_middle_ad_status' => 0,
                'view_page_ad_status' => 0,
                'news_page_ad_status' => 0,
                'side_bar_ad_status' => 0,
            ]);
        }
    }
}
