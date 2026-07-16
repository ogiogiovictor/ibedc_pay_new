<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkSMSSending extends Command
{
    protected $signature = 'app:bulk-sms-sending
                            {csv : CSV filename inside storage/app/bulk-sms/ (e.g. customers.csv)}
                            {--message= : Inline SMS body template}
                            {--message-file= : Path to a .txt file containing the SMS body template (defaults to storage/app/bulk-sms/message.txt)}
                            {--dry-run : Preview messages without actually sending}
                            {--delay=500 : Milliseconds to wait between each SMS (default: 500)}';

    protected $description = 'Send bulk SMS from a CSV file stored in storage/app/bulk-sms/';

    private string $smsUrl    = 'https://app.smartsmssolutions.com/io/api/client/v1/sms/';
    private string $storageDir = 'bulk-sms';

    public function handle(): int
    {
        $csvFile  = $this->argument('csv');
        $dryRun   = $this->option('dry-run');
        $delay    = (int) $this->option('delay');

        $csvPath = storage_path("app/{$this->storageDir}/{$csvFile}");

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");
            $this->line("Place your CSV files inside:  storage/app/bulk-sms/");
            return self::FAILURE;
        }

        // --- Resolve message template ---
        $template = $this->resolveTemplate();

        if (! $template) {
            return self::FAILURE;
        }

        // --- Parse CSV ---
        $rows = $this->parseCsv($csvPath);

        if (empty($rows)) {
            $this->error('CSV file is empty or could not be parsed. Ensure the first row is headers.');
            return self::FAILURE;
        }

        // --- Summary before sending ---
        $this->newLine();
        $this->line('<info>CSV file  :</info> ' . basename($csvPath));
        $this->line('<info>Records   :</info> ' . count($rows));
        $this->line('<info>Template  :</info> ' . $template);
        $this->newLine();

        if ($dryRun) {
            $this->warn('--- DRY RUN: No SMS will be sent ---');
        } elseif (! $this->confirm("Send SMS to " . count($rows) . " recipient(s)?", true)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed  = 0;
        $bar     = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $index => $row) {
            $name  = trim($row['name']          ?? $row['customer_name'] ?? '');
            $phone = trim($row['phone']         ?? $row['phone_number']  ?? '');
            $meter = trim($row['meter']         ?? $row['meter_no']      ?? '');
            $token = trim($row['token']         ?? '');

            if (! $phone) {
                $this->newLine();
                $this->warn("Row " . ($index + 2) . ": missing phone number — skipped.");
                $failed++;
                $bar->advance();
                continue;
            }

            $message = strtr($template, [
                '{name}'  => $name,
                '{phone}' => $phone,
                '{meter}' => $meter,
                '{token}' => $token,
            ]);

            if ($dryRun) {
                $this->newLine();
                $this->line("  [ROW " . ($index + 2) . "] To: {$phone}");
                $this->line("           Msg: {$message}");
                $success++;
                $bar->advance();
                continue;
            }

            try {
                $response = Http::asForm()->post($this->smsUrl, [
                    'token'   => env('SMS_TOKEN'),
                    'sender'  => 'IBEDC',
                    'to'      => $phone,
                    'message' => $message,
                    'type'    => 0,
                    'routing' => 3,
                ]);

                if ($response->successful()) {
                    $success++;
                    Log::info("BulkSMS sent", ['phone' => $phone, 'meter' => $meter]);
                } else {
                    $failed++;
                    Log::warning("BulkSMS failed", [
                        'phone'  => $phone,
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error("BulkSMS exception for {$phone}: " . $e->getMessage());
            }

            if ($delay > 0) {
                usleep($delay * 1000);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done.  Sent: {$success}  |  Failed/Skipped: {$failed}");

        return self::SUCCESS;
    }

    private function resolveTemplate(): ?string
    {
        // 1. Inline --message flag takes priority
        if ($this->option('message')) {
            return trim($this->option('message'));
        }

        // 2. --message-file flag or default message.txt
        $filePath = $this->option('message-file')
            ?? storage_path("app/{$this->storageDir}/message.txt");

        if (file_exists($filePath)) {
            $content = trim(file_get_contents($filePath));
            if ($content) {
                $this->info("Using message body from: {$filePath}");
                return $content;
            }
        }

        // 3. Ask interactively
        $this->warn("No message template found. Edit  storage/app/bulk-sms/message.txt  or use --message.");
        $template = $this->ask('Enter SMS body (use {name}, {phone}, {meter}, {token} as placeholders)');

        return $template ? trim($template) : null;
    }

    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');

        if (! $handle) {
            return [];
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);
            return [];
        }

        // Normalise headers: lowercase, trim, spaces → underscores
        $headers = array_map(
            fn($h) => strtolower(trim(str_replace([' ', '-'], '_', $h))),
            $headers
        );

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, $line);
            }
        }

        fclose($handle);

        return $rows;
    }
}
