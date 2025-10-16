<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local and cloud databases';

    /**
     * Table Orders
     */
    protected $cacheKey = 'table_order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::connection('mysql_cloud')->select('SELECT 1');
        } catch (\Throwable $e) {
            $this->error('Needs network: cannot reach cloud database.');

            return 1;
        }

        $local = DB::connection('sqlite_local');
        $cloud = DB::connection('mysql_cloud');

        $tablesOrder = $this->getTableOrder($cloud);

        $lastSyncCloud = Cache::get('last_sync_cloud_to_local', '1970-01-01 00:00:00');
        $lastSyncLocal = Cache::get('last_sync_local_to_cloud', '1970-01-01 00:00:00');

        foreach ($tablesOrder as $table) {
            if ($table === 'migrations') {
                continue;
            }

            // Cloud -> Local
            $cloudLogs = $cloud->table('sync_logs')
                ->where('table_name', $table)
                ->where('executed_at', '>', $lastSyncCloud)
                ->orderBy('executed_at')
                ->get();

            $cloudLogsFiltered = $this->filterByRowUpdate($cloudLogs, $local, $table);
            $this->apply($cloudLogsFiltered, $local);

            // Local -> Cloud
            $localLogs = $local->table('sync_logs')
                ->where('table_name', $table)
                ->where('executed_at', '>', $lastSyncLocal)
                ->orderBy('executed_at')
                ->get();

            $localLogsFiltered = $this->filterByRowUpdate($localLogs, $cloud, $table);
            $this->apply($localLogsFiltered, $cloud);
        }

        // Update last sync timestamp
        if (! empty($cloudLogs)) {
            Cache::put('last_sync_cloud_to_local', $cloudLogs->max('executed_at'));
        }
        if (! empty($localLogs)) {
            Cache::put('last_sync_local_to_cloud', $localLogs->max('executed_at'));
        }

        $this->info('Sync completed successfully.');

        return 0;
    }

    protected function getTableOrder($connection)
    {
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $tables = DB::connection($connection->getName())
                ->select("SELECT name FROM sqlite_master WHERE type='table'");
            $tables = array_map(fn ($t) => $t->name, $tables);

        } else { // MySQL
            $database = env('DB_DATABASE');
            $tablesRaw = DB::connection($connection->getName())
                ->select('SHOW TABLES');
            $tables = array_map(fn ($t) => array_values((array) $t)[0], $tablesRaw);
        }

        // Build dependency map
        $dependencies = [];
        if ($driver === 'sqlite') {
            foreach ($tables as $table) {
                $fks = DB::connection($connection->getName())->select("PRAGMA foreign_key_list($table)");
                foreach ($fks as $fk) {
                    $dependencies[$table][] = $fk->table;
                }
            }

        } else { // MySQL
            $database = env('DB_DATABASE');
            $fks = DB::connection($connection->getName())->select('
                SELECT TABLE_NAME, REFERENCED_TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL
            ', [$database]);
            foreach ($fks as $fk) {
                $dependencies[$fk->TABLE_NAME][] = $fk->REFERENCED_TABLE_NAME;
            }
        }
        $schemaHash = md5(json_encode($dependencies));

        // Size Comparison
        $cached = Cache::get($this->cacheKey);
        if ($cached && isset($cached['hash']) && $cached['hash'] === $schemaHash) {
            return $cached['order'];
        }

        // Topological sorting
        $ordered = $this->topologicalSort($dependencies);

        Cache::put($this->cacheKey, [
            'hash' => $schemaHash,
            'order' => $ordered,
        ], 86400);

        return $ordered;
    }

    protected function topologicalSort($nodes)
    {
        $sorted = [];
        $visited = [];

        $visit = function ($node) use (&$visit, &$nodes, &$sorted, &$visited) {
            if (isset($visited[$node])) {
                return;
            }
            $visited[$node] = true;
            if (isset($nodes[$node])) {
                foreach ($nodes[$node] as $dep) {
                    $visit($dep);
                }
            }
            $sorted[] = $node;
        };

        foreach (array_keys($nodes) as $node) {
            $visit($node);
        }

        return array_reverse(array_unique($sorted));
    }

    protected function filterByRowUpdate($logs, $target, $table)
    {
        if ($logs->isEmpty()) {
            return collect();
        }

        $ids = $logs->pluck('record_id')->all();

        // Batch fetch existing rows
        $existingRows = $target->table($table)
            ->whereIn('id', $ids)
            ->select('id', 'updated_at')
            ->get()
            ->keyBy('id');

        return $logs->filter(function ($log) use ($existingRows) {
            $rowUpdated = isset($existingRows[$log->record_id])
                ? strtotime($existingRows[$log->record_id]->updated_at ?? 0)
                : 0;

            return strtotime($log->executed_at) > $rowUpdated;
        });
    }

    protected function apply($logs, $target)
    {
        foreach ($logs as $log) {
            $table = $log->table_name;
            $id = $log->record_id;
            $data = json_decode($log->changes, true) ?? [];
            $row = $target->table($table)->where('id', $id)->first();
            $lt = $row?->updated_at ? strtotime($row->updated_at) : 0;
            $rt = strtotime($log->executed_at);
            if ($lt >= $rt) {
                continue;
            }
            if ($log->operation === 'create' || $log->operation === 'update') {
                if ($row) {
                    $target->table($table)->where('id', $id)->update($data);
                } else {
                    $data['id'] = $id;
                    $target->table($table)->insert($data);
                }
            } elseif ($log->operation === 'delete') {
                $target->table($table)->where('id', $id)->delete();
            }
            $target->table('sync_logs')->insert((array) $log);
        }
    }
}
