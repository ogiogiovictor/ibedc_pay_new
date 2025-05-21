<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use App\Services\PolarisLogService;

class FailedTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:failed-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All Failed Transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('***** FLUTTERWAVE BEGIN LOOP IMPLEMENTATION *************');

            $today = now()->toDateString();

            PaymentTransactions::whereDate('created_at', $today)
                ->whereIn('status', ['failed', 'cancelled'])
                ->chunk(5, function ($paymentLogs) {
                    foreach ($paymentLogs as $paymentLog) {
                        // Determine the provider key for each payment log
                        $providerKey = match ($paymentLog->provider) {
                            'FCMB' => env('FLUTTER_FCMB_KEY'),
                            'Polaris' => env('FLUTTER_POLARIS_KEY'),
                            default => env('FLUTTER_POLARIS_KEY'), // Use a default key if provider is not specified
                        };

                        $flutterData = [
                            'SECKEY' => $providerKey,
                            "txref" => $paymentLog->transaction_id,
                        ];

                        $flutterUrl = env("FLUTTER_WAVE_URL");

                        $iresponse = Http::post($flutterUrl, $flutterData);
                        $flutterResponse = $iresponse->json();

                        $this->info('***** AWAITING RESPONSE FROM FLUTTERWAVE *************');

                        if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {

                            PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'status' => 'processing',
                            ]);

                            (new PolarisLogService)->processLogs(
                                $paymentLog->transaction_id,
                                $paymentLog->meter_no,
                                $paymentLog->account_number,
                                $flutterResponse
                            );

                            $this->info('***** ONE TRANSACTION UPDATED :: A TRANSACTION HAS BEEN UPDATED  AS SUCCESSFUL *************');
                        } elseif (
                            isset($flutterResponse['status']) &&
                            isset($flutterResponse['data']['status']) &&
                            $flutterResponse['data']['status'] == 'failed'
                        ) {
                            PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'status' => 'failed',
                            ]);

                            (new PolarisLogService)->processLogs(
                                $paymentLog->transaction_id,
                                $paymentLog->meter_no,
                                $paymentLog->account_number,
                                $flutterResponse
                            );
                            $this->info('***** ONE TRANSACTION UPDATED :: A TRANSACTION HAS BEEN UPDATED  AS FAILED *************');
                        } elseif (
                            isset($flutterResponse['status']) &&
                            isset($flutterResponse['data']['status']) &&
                            $flutterResponse['data']['status'] == 'cancelled'
                        ) {
                            PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'status' => 'cancelled',
                            ]);

                            (new PolarisLogService)->processLogs(
                                $paymentLog->transaction_id,
                                $paymentLog->meter_no,
                                $paymentLog->account_number,
                                $flutterResponse
                            );
                            $this->info('***** ONE TRANSACTION UPDATED :: A TRANSACTION HAS BEEN UPDATED  AS CANCELLED *************');
                        } else {
                            $this->info('***** ONE TRANSACTION FAILED :: THE TRANSACTION HAS NO STATUS *************');
                        }
                    }
                });
        } catch (\Exception $e) {
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error Failed LookUp: ' . $e->getMessage());
        }
    }
}
