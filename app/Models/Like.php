<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'comment_id',
        'user_id',
        'is_post',
        'like_type'
    ];
}
