<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use App\Models\SystemAction;
use App\Enums\SystemActionType;

class LogUserLoggedIn
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip();
        $userAgent = request()->header('User-Agent');

        // فحص تكرار: لو فيه نفس الـ action بنفس اليوزر وip و user_agent اتعمل خلال آخر 5 ثواني -> تجاهل
        $recent = SystemAction::where('user_id', $user->id)
            ->where('action', SystemActionType::LOGIN)
            ->where('ip', $ip)
            ->where('meta->user_agent', $userAgent)
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recent) {
            Log::info('Skipped duplicate login log for user '.$user->id);
            return;
        }

        SystemAction::log([
            'user_id' => $user->id,
            'action'  => SystemActionType::LOGIN,
            'ip'      => $ip,
            'source'  => 'web',
            'note'    => 'User logged in',
            'meta'    => [
                'user_agent' => $userAgent,
            ],
        ]);
    }
}
