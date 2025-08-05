<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorSqlConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:sql-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor and kill idle SQL Server user sessions exceeding a threshold';

    /**
     * Threshold for number of user sessions before cleanup starts
     */
    protected $threshold = 200;

    public function handle()
    {
        try {
            // ✅ Get the current SPID to avoid killing our own connection
            $currentSession = DB::connection('sqlsrv')
                ->selectOne("SELECT @@SPID AS session_id");
            $currentSpid = $currentSession->session_id;

            // ✅ Count total user sessions
            $count = DB::connection('sqlsrv')->selectOne("
                SELECT COUNT(*) AS total 
                FROM sys.dm_exec_sessions 
                WHERE is_user_process = 1
            ")->total;

            $this->info("Current user sessions: {$count}");

            if ($count <= $this->threshold) {
                $this->info("Session count is within safe limit ({$this->threshold}). No action taken.");
                return;
            }

            $this->warn("Session count exceeded threshold ({$this->threshold}). Cleaning idle sessions...");

            // ✅ Fetch idle (sleeping) user sessions excluding current SPID
            $sessions = DB::connection('sqlsrv')->select("
                SELECT s.session_id
                FROM sys.dm_exec_sessions s
                WHERE s.is_user_process = 1
                AND s.session_id <> ?
                AND s.status = 'sleeping'
            ", [$currentSpid]);

            foreach ($sessions as $session) {
                try {
                    DB::connection('sqlsrv')->statement("KILL {$session->session_id}");
                    $this->info("Killed idle session ID: {$session->session_id}");
                } catch (\Exception $ex) {
                    $this->warn("Could not kill session {$session->session_id}: " . $ex->getMessage());
                    Log::warning("SQL Monitor: Failed to kill session {$session->session_id}. Error: {$ex->getMessage()}");
                }
            }

            $this->info("Idle session cleanup completed.");

        } catch (\Throwable $e) {
            Log::error("SQL Monitor Error: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
        }
    }
}
