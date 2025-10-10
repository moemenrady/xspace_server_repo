<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use App\Models\SystemAction;
use App\Enums\SystemActionType;

class LogUserLoggedOut
{
    public function handle(Logout $event): void
    {
        $user = $event->user;
        if (!$user) return;

        $ip = request()->ip();
        $userAgent = request()->header('User-Agent');

        $recent = SystemAction::where('user_id', $user->id)
            ->where('action', SystemActionType::LOGOUT)
            ->where('ip', $ip)
            ->where('meta->user_agent', $userAgent)
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recent) {
            Log::info('Skipped duplicate logout log for user '.$user->id);
            return;
        }

        SystemAction::log([
            'user_id' => $user->id,
            'action'  => SystemActionType::LOGOUT,
            'ip'      => $ip,
            'source'  => 'web',
            'note'    => 'User logged out',
            'meta'    => [
                'user_agent' => $userAgent,
            ],
        ]);
    }
}
