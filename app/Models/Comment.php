<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'num_replies',
        'like_emoji',
        'dislike',
        'angry',

    ];

}
