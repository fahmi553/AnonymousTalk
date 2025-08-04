<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $primaryKey = 'badge_id';

    protected $fillable = [
        'badge_name',
        'description',
        'trust_threshold',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges', 'badge_id', 'user_id')
            ->withTimestamps()
            ->withPivot('awarded_at');
    }

    public function awardedUsers()
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }
}
