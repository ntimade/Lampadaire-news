<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectedArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'source_name',
        'source_url',
        'source_url_hash',
        'title',
        'summary',
        'image',
        'published_at',
        'fetched_at',
        'status',
        'converted_news_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function convertedNews()
    {
        return $this->belongsTo(News::class, 'converted_news_id');
    }

    public function scopeNewOnly($query)
    {
        return $query->where('status', 'new');
    }
}
