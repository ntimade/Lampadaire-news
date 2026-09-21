<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrgentNews extends Model
{
    use HasFactory;

    protected $table = 'urgent_news';

    protected $fillable = [
        'text',
        'link',
        'is_published',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
