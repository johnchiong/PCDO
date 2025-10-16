<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SyncLogger
{
    protected static function bootSyncLogger()
    {
        static::created(function ($model) {
            self::writeSyncLog('create', $model, null);
        });

        static::updated(function ($model) {
            $changes = [];
            foreach ($model->getChanges() as $field => $new) {
                $old = $model->getOriginal($field);
                if ($old != $new) {
                    $changes[$field] = ['before' => $old, 'after' => $new];
                }
            }
            self::writeSyncLog('update', $model, $changes);
        });

        static::deleted(function ($model) {
            self::writeSyncLog('delete', $model, null);
        });
    }

    protected static function writeSyncLog($operation, $model, $changes)
    {
        $user = Auth::user();
        $source = config('database.default');

        DB::connection($source)->table('sync_logs')->insert([
            'table_name' => $model->getTable(),
            'operation' => $operation,
            'record_id' => $model->id,
            'user_id' => $user?->id ?? 0,
            'user_name' => $user?->name ?? 'system',
            'changes' => json_encode($changes ?? $model->attributesToArray(), JSON_UNESCAPED_UNICODE),
            'source' => $source,
            'executed_at' => now(),
        ]);
    }
}
