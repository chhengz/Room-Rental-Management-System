<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(string $action, Model $target, ?array $before = null, ?array $after = null): void
    {
        $request = request();

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'target_type' => $target::class,
            'target_id' => $target->getKey(),
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
