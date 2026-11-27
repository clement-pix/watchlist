<?php

namespace App\Services;

use App\Models\ModerationLog;
use App\Models\User;

class ModerationLogService
{
    public function record(User $moderator, string $action, ?User $target = null, ?string $targetType = null, string|int|null $targetId = null, ?string $reason = null, array $metadata = []): ModerationLog
    {
        return ModerationLog::create([
            'moderator_id' => $moderator->id,
            'target_user_id' => $target?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId === null ? null : (string) $targetId,
            'reason' => $reason,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}
