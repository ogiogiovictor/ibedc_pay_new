<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PolarisLogService;

class PaymentLookUpImproved extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:payment-look-up-improved';

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
            $this->info('***** FLUTTERWAVE VERIFY DAILY PAYMENT API :: Lookup Initiated --- *************');

            $today = now()->toDateString();
            $batchSize = 10;

            do {
                $paymentLogs = PaymentTransactions::whereDate('created_at', $today)
                    ->whereIn('status', ['started'])
                    ->where('response_status', '3')
                    ->take($batchSize)
                    ->get();

                if ($paymentLogs->isEmpty()) {
                    break;
                }

                

                foreach ($paymentLogs as $paymentLog) {

                    $providerKey = match ($paymentLog->provider) {
                        'FCMB' => env('FLUTTER_FCMB_KEY'),
                        'Polaris' => env('FLUTTER_POLARIS_KEY'),
                        default => env('FLUTTER_POLARIS_KEY'), // Use a default key if provider is not specified
                          };


                    $flutterData = [
                        'SECKEY' =>  $providerKey, // env("FLUTTER_POLARIS_KEY"),
                        "txref" => $paymentLog->transaction_id
                    ];
                    $flutterUrl = env("FLUTTER_WAVE_URL");

                    $response = Http::post($flutterUrl, $flutterData);
                    $flutterResponse = $response->json();

                    $this->info('***** Processing Payment via FLUTTERWAVE *************');
                    Log::info("BEFORE PAYMENT LOOKUP ". json_encode($flutterResponse));

                    //if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success") {
                    if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'successful') {

                        $this->processTransactionSuccess($paymentLog, $flutterResponse);
                    } elseif (isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'failed') {
                        $this->processTransactionFailure($paymentLog, $flutterResponse);
                    } else {
                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);
                    }

                    Log::error("Payment Response" . json_encode($flutterResponse));
                }

            } while ($paymentLogs->count() === $batchSize);

            $this->info('***** Lookup Process Completed *****');
        } catch (\Exception $e) {
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error in Payment LookUp: ' . $e->getMessage());
        }

    }

    private function processTransactionSuccess($paymentLog, $flutterResponse)
    {
        if ($paymentLog->status == "processing") {
            PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                'providerRef' => $flutterResponse['data']['flwref'],
            ]);
            $this->info('***** FLUTTERWAVE Verification Successful *****');
        } elseif ($paymentLog->status == "started") {
            PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                'providerRef' => $flutterResponse['data']['flwref'],
                'status' => 'processing'
            ]);
            $this->info('***** FLUTTERWAVE Transaction set to processing *****');
        }

        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no, $paymentLog->account_number, $flutterResponse);
    }

    private function processTransactionFailure($paymentLog, $flutterResponse)
    {
        PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
            'providerRef' => $flutterResponse['data']['flwref'],
            'status' => 'failed'
        ]);
        $this->info('***** FLUTTERWAVE TRANSACTION FAILED *****');

        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no, $paymentLog->account_number, $flutterResponse);
    }
}
