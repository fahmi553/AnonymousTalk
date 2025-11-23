<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrustScoreLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';

    protected $table = 'trust_score_logs';

    protected $fillable = [
        'user_id',
        'action_type',
        'score_change',
        'reason',
        'timestamp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
