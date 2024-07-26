<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting\ApplicationSetting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;

class ApplicationSettings extends Component
{

    public $settings; 

    public function mount() {

        $this->settings = ApplicationSetting::all();
    }

    public function toggleService($id) {

        $setting = ApplicationSetting::findOrFail($id);

        $setting->status = $setting->status === 'on' ? 'off' : 'on';
        $setting->save();

        session()->flash('success', 'Status Successfully Updated.');

        // Use Livewire's redirect method
        return redirect()->route('application_settings');

    }


    public function runPaymentLookUp() {

        try {
       
        $today = now()->toDateString();

        $checkTransaction = PaymentTransactions::whereDate('created_at', $today)
             ->whereIn('status', ['started', 'processing'])
            ->chunk(5, function ($paymentLogs) use (&$paymentData) {

                foreach ($paymentLogs as $paymentLog) {

                    $this->processPaymentLog($paymentLog);
        
                    // $flutterData = [
                    //     'SECKEY' =>  env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
                    //     "txref" => $paymentLog->transaction_id
                    // ];

                    // $flutterUrl = env("FLUTTER_WAVE_URL");

                    // $iresponse = Http::post($flutterUrl, $flutterData);
                    // $flutterResponse = $iresponse->json(); 

                    // if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'successful') {


                    //     if ($paymentLog->status == "processing") {
                    //         $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                    //             'providerRef' => $flutterResponse['data']['flwref'],
                    //         ]);
                    //         session()->flash('success', 'FLUTTERWAVE Verification Was Successful');
                      
                           
                    //     } else if ($paymentLog->status == "started") {
                    //         $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                    //             'providerRef' => $flutterResponse['data']['flwref'],
                    //             'status' => 'processing'
                    //         ]);
                    //         session()->flash('success', 'FLUTTERWAVE Transaction is set to processing');

                    //     } else {
                    //         session()->flash('error', 'FLUTTERWAVE Verification failed or unknown');
                    //     }


                    // } elseif (isset($flutterResponse['status']) && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'failed') {
    
                              
                    //         $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                    //             'providerRef' => $flutterResponse['data']['flwref'],
                    //             'status' => 'failed'
                    //         ]);
                    //         session()->flash('success', 'FLUTTERWAVE Transaction is set to provider reference updated');
                    // }else {

                    //     session()->flash('error', $flutterResponse);

                    // }
        




                }

            });

        }catch(\Exception $e){

           // Session::put('error', $e->getMessage());
            session()->flash('error', $e->getMessage());
            Log::error('Error in Payment LookUp: ' . $e->getMessage());
        }


    }




    private function processPaymentLog($paymentLog) {
        $flutterData = [
            'SECKEY' => env("FLUTTER_POLARIS_KEY"),
            "txref" => $paymentLog->transaction_id
        ];
    
        $flutterUrl = env("FLUTTER_WAVE_URL");
    
        try {
            $iresponse = Http::post($flutterUrl, $flutterData);
            $flutterResponse = $iresponse->json();
    
            if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'successful' ) {
                $this->handleSuccessfulResponse($paymentLog, $flutterResponse);
            } elseif (isset($flutterResponse['status']) && $flutterResponse['data']['status'] == 'failed') {
                $this->updatePaymentLog($paymentLog, $flutterResponse['data']['flwref'], 'failed');
                session()->flash('success', 'FLUTTERWAVE Transaction is set to provider reference updated');
            } else {
                session()->flash('error', 'Unknown response from FLUTTERWAVE: ' . json_encode($flutterResponse));
                Log::error('Unknown response from FLUTTERWAVE: ' . json_encode($flutterResponse));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error in FLUTTERWAVE API call: ' . $e->getMessage());
            Log::error('Error in FLUTTERWAVE API call: ' . $e->getMessage());
        }
    }



    
    private function handleSuccessfulResponse($paymentLog, $flutterResponse) {
        if ($paymentLog->status == "processing") {
            $this->updatePaymentLog($paymentLog, $flutterResponse['data']['flwref']);
            session()->flash('success', 'FLUTTERWAVE Verification Was Successful');
        } elseif ($paymentLog->status == "started") {
            $this->updatePaymentLog($paymentLog, $flutterResponse['data']['flwref'], 'processing');
            session()->flash('success', 'FLUTTERWAVE Transaction is set to processing');
        } else {
            session()->flash('error', 'FLUTTERWAVE Verification failed or unknown');
            Log::error('FLUTTERWAVE Verification failed or unknown for transaction ID: ' . $paymentLog->transaction_id);
        }
    }
    
    private function updatePaymentLog($paymentLog, $providerRef, $status = null) {
        $updateData = ['providerRef' => $providerRef];
        if ($status) {
            $updateData['status'] = $status;
        }
        PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update($updateData);
    }



    public function render()
    {
        return view('livewire.application-settings');
    }
}
