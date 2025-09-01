<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseRouter
{
    protected static $connection = null;

    /**
     * Get active connection.
     * Cloud is master; local is read-only fallback.
     */
    public static function getConnection(): string
    {
        if (self::$connection) return self::$connection;

        try {
            DB::connection('cloud')->getPdo();
            return self::$connection = 'cloud';
        } catch (\Exception $e) {
            return self::$connection = 'local';
        }
    }

    /**
     * Safe sync from cloud to local only.
     * Avoids overwriting cloud data from local.
     */
    public static function syncToLocal(string $table)
    {
        $cloudData = DB::connection('cloud')->table($table)->get();

        DB::connection('local')->transaction(function () use ($cloudData, $table) {
            foreach ($cloudData as $row) {
                $rowArray = (array)$row;

                // If row exists, only update if cloud row is newer
                $existing = DB::connection('local')->table($table)->where('id', $row->id)->first();
                if ($existing) {
                    if (isset($rowArray['updated_at']) && isset($existing->updated_at)) {
                        $cloudUpdated = Carbon::parse($rowArray['updated_at']);
                        $localUpdated = Carbon::parse($existing->updated_at);
                        if ($cloudUpdated->gt($localUpdated)) {
                            DB::connection('local')->table($table)->where('id', $row->id)->update($rowArray);
                        }
                    }
                } else {
                    DB::connection('local')->table($table)->insert($rowArray);
                }
            }
        });
    }

    /**
     * Read query helper (dynamic fallback)
     */
    public static function query(string $table)
    {
        $connection = self::getConnection();
        return DB::connection($connection)->table($table);
    }
}