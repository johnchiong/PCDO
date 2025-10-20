<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function status()
    {
        $lastSync = Cache::get('last_sync_time');

        return response()->json([
            'online' => $this->checkCloud(),
            'last_sync' => $lastSync ? Carbon::parse($lastSync)->toIso8601String() : null,
            'syncing' => Cache::get('sync_in_progress', false),
        ]);
    }

    public function trigger()
    {
        if (Cache::get('sync_in_progress')) {
            return response()->json(['message' => 'Sync already running'], 409);
        }

        Cache::put('sync_in_progress', true, 600);
        dispatch(function () {
            try {
                Artisan::call('sync:database');
            } finally {
                Cache::forget('sync_in_progress');
            }
        });

        return response()->json(['message' => 'Sync started']);
    }

    protected function checkCloud(): bool
    {
        try {
            DB::connection('mysql_cloud')->select('SELECT 1');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
