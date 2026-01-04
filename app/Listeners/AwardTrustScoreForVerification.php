<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Models\TrustScoreLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardTrustScoreForVerification
{
    public function handle(Verified $event)
    {
        $user = $event->user;

        $alreadyRewarded = TrustScoreLog::where('user_id', $user->user_id)
                            ->where('action_type', 'email_verified')
                            ->exists();

        if (!$alreadyRewarded) {
            $user->applyTrustChange(10, 'Email Verification Bonus', 'email_verified');
        }
    }
}
