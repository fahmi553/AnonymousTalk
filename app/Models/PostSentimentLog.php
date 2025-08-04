<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSentimentLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'post_id',
        'sentiment_score',
        'result',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
