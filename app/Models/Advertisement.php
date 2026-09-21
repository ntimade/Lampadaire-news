<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    public const PLACEMENTS = [
        'home_top_bar' => 'Barre du haut (accueil)',
        'home_middle' => 'Milieu de page (accueil)',
        'view_page' => "Page d'un article",
        'news_page' => 'Liste des actualités',
        'side_bar' => 'Barre latérale',
    ];

    protected $fillable = [
        'placement',
        'title',
        'description',
        'image',
        'url',
        'sort_order',
        'status',
    ];

    public function scopeActive($query, string $placement)
    {
        return $query->where(['placement' => $placement, 'status' => 1])
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }
}
