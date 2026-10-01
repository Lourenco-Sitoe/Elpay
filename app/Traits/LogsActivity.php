<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Schema;

trait LogsActivity
{
    protected function logActivity(string $action, ?string $description = null, $userId = null): void
    {
        try {
            if (! class_exists(ActivityLog::class)) {
                return;
            }
            $data = [
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip' => request()->ip(),
            ];
            if (Schema::hasColumn('activity_logs', 'url')) {
                $data['url'] = request()->fullUrl();
            }
            ActivityLog::create($data);
        } catch (\Throwable) {
            // Nunca quebrar o fluxo por causa do log.
        }
    }
}
