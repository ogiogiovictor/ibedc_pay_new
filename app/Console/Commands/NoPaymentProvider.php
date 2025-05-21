<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use App\Services\PolarisLogService;

class NoPaymentProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:no-payment-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('***** FLUTTERWAVE BEGIN LOOP IMPLEMENTATION *************');

            $today = now()->toDateString();

            PaymentTransactions::whereDate('created_at',  $today)
                ->whereIn('status', ['failed', 'cancelled', 'started'])
              //  ->whereNot('provider', 'Wallet')
               // ->where('provider', '!=', 'Wallet')
                ->chunk(5, function ($paymentLogs) {
                    foreach ($paymentLogs as $paymentLog) {
                        // List of provider keys to try
                        $providers = [
                            'FCMB' => env('FLUTTER_FCMB_KEY'),
                            'Polaris' => env('FLUTTER_POLARIS_KEY')
                        ];

                        $transactionProcessed = false;

                        foreach ($providers as $providerName => $providerKey) {
                            $this->info("***** TRYING PROVIDER: {$providerName} *************");

                            $flutterData = [
                                'SECKEY' => $providerKey,
                                "txref" => $paymentLog->transaction_id,
                            ];

                            $flutterUrl = env("FLUTTER_WAVE_URL");

                            $iresponse = Http::post($flutterUrl, $flutterData);
                            $flutterResponse = $iresponse->json();

                            if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success") {

                                $dataStatus = $flutterResponse['data']['status'] ?? null;

                                if ($dataStatus == 'successful') {
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

                                    $this->info('***** ONE TRANSACTION UPDATED AS SUCCESSFUL*******' . $paymentLog->transaction_id );
                                    $transactionProcessed = true;
                                    break; // Exit the loop as the transaction is successful
                                } elseif (in_array($dataStatus, ['failed', 'cancelled'])) {
                                    PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                        'providerRef' => $flutterResponse['data']['flwref'] ?? null,
                                        'status' => $dataStatus,
                                    ]);

                                    (new PolarisLogService)->processLogs(
                                        $paymentLog->transaction_id,
                                        $paymentLog->meter_no,
                                        $paymentLog->account_number,
                                        $flutterResponse
                                    );

                                    $this->info("***** ONE TRANSACTION UPDATED AS {$dataStatus} *************");
                                    $transactionProcessed = true;
                                    break; // Exit the loop as the transaction is resolved
                                }
                            }
                        }

                        if (!$transactionProcessed) {
                            $this->info('***** TRANSACTION NOT PROCESSED :: ALL PROVIDERS FAILED *************');
                        }
                    }
                });
        } catch (\Exception $e) {
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error Failed LookUp: ' . $e->getMessage());
        }
    }
}
