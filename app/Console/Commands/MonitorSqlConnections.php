<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class MonitorSqlConnections extends Command
{
    protected $signature = 'monitor:sql-connections';
    protected $description = 'Monitor and kill idle SQL Server user sessions exceeding a threshold';

    protected $threshold = 200; // session limit
    protected $alertEmails = ['victor.ogiogio@ibedc.com', 'adebayo.oyebamiji@ibedc.com']; // ✅ multiple recipients

    public function handle()
    {
        try {
            // ✅ Get current SPID
            $currentSpid = DB::connection('sqlsrv')
                ->selectOne("SELECT @@SPID AS session_id")->session_id;

            // ✅ Count current sessions
            $count = DB::connection('sqlsrv')->selectOne("
                SELECT COUNT(*) AS total 
                FROM sys.dm_exec_sessions 
                WHERE is_user_process = 1
            ")->total;

            $this->info("📊 Current SQL user sessions: {$count}");
            $this->safeLog("SQL Monitor → Current sessions: {$count}");

            if ($count <= $this->threshold) {
                $this->info("✅ Session count is safe.");
                return;
            }

            $this->warn("⚠️ Threshold ({$this->threshold}) exceeded! Initiating cleanup...");

            // ✅ Send alert email
            $this->sendAlertEmail($count);

            // ✅ Kill idle sessions
            $sessions = DB::connection('sqlsrv')->select("
                SELECT s.session_id
                FROM sys.dm_exec_sessions s
                WHERE s.is_user_process = 1
                AND s.session_id <> ?
                AND s.status = 'sleeping'
            ", [$currentSpid]);

            foreach ($sessions as $s) {
                try {
                    DB::connection('sqlsrv')->statement("KILL {$s->session_id}");
                    $this->info("💀 Killed idle session: {$s->session_id}");
                } catch (\Exception $ex) {
                    $this->warn("Could not kill session {$s->session_id}: {$ex->getMessage()}");
                }
            }

            // ✅ Pause jobs to reduce load
            $this->pauseJobs();

            // ✅ Restart Apache to stabilize PHP-FPM
            $this->restartApache();

            $this->info("✅ Cleanup completed.");

        } catch (\Throwable $e) {
            $this->safeLog("Monitor SQL Error: " . $e->getMessage());
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    /**
     * ✅ Safe logging to /tmp (avoids Laravel log permission errors)
     */
    protected function safeLog($message)
    {
        file_put_contents('/tmp/sql_monitor.log', date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
    }

    /**
     * ✅ Send alert email to admins
     */
    protected function sendAlertEmail($count)
    {
        try {
            foreach ($this->alertEmails as $email) {
                Mail::raw("⚠️ SQL Monitor Alert: {$count} active sessions detected. Cleanup initiated.", 
                    fn($msg) => $msg->to($email)->subject('SQL Server Session Alert'));
            }
            $this->info("📧 Alert email sent to admins.");
        } catch (\Exception $e) {
            $this->safeLog("Email send failed: " . $e->getMessage());
        }
    }

    /**
     * ✅ Pause Laravel queues to reduce DB load
     */
    protected function pauseJobs()
    {
        try {
            $this->info("⏸ Pausing Laravel queues...");
            (new Process(['php', 'artisan', 'queue:pause']))->run();
        } catch (\Exception $e) {
            $this->safeLog("Queue pause failed: " . $e->getMessage());
        }
    }

    /**
     * ✅ Restart Apache service (Linux)
     */
    protected function restartApache()
    {
        try {
            $this->info("🔄 Restarting Apache...");
            (new Process(['sudo', 'systemctl', 'restart', 'apache2']))->run();
        } catch (\Exception $e) {
            $this->safeLog("Apache restart failed: " . $e->getMessage());
        }
    }
}
