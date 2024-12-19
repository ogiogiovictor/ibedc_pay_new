<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting\ApplicationSetting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function optimizationClean() {


        $tableName = 'mdwibedc.transactions_in';  // Update with the actual table name

     
          // Include current date and time in the filename
          $fileName = 'transaction_in'. date('Y_m_d_His') . '.csv';
          $filePath = storage_path('app/public/' . $fileName);
  
          session()->flash('info', "Exporting data from $tableName to CSV...");
          
          // Step 1: Connect to the specified database and retrieve data
          $data = DB::connection("middleware2")->table($tableName)->get();
          //$data = DB::table($tableName)->get();
  
          if ($data->isEmpty()) {
            session()->flash('success', "No data found in $tableName to export.");
          } else {
              $csvContent = $this->convertToCSV($data);
  
              // Save the CSV content to the file
              Storage::disk('public')->put($fileName, $csvContent);
              session()->flash('info', "Data exported successfully to $filePath");
          }
  
          // Step 2: Truncate the table
         // DB::table($tableName)->truncate();
          DB::connection("middleware2")->table($tableName)->truncate();
          session()->flash('success', "Optimization Successful.");
  
    }


      /**
     * Convert data to CSV format.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @return string
     */
    private function convertToCSV($data)
    {
        $csvContent = '';

        // Get headers from the first row's keys
        $headers = array_keys((array)$data->first());
        $csvContent .= implode(',', $headers) . "\n";

        // Loop through each row and convert it to CSV format
        foreach ($data as $row) {
            $csvContent .= implode(',', array_map('strval', (array)$row)) . "\n";
        }

        return $csvContent;
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



    public function mgtOptimization() {

        $tableName = 'management_transactions';  // Update with the actual table name

     
          // Include current date and time in the filename
          $fileName = 'management_transact_'. date('Y_m_d_His') . '.csv';
          $filePath = storage_path('app/public/' . $fileName);
  
          session()->flash('info', "Exporting data from $tableName to CSV...");
          
          // Step 1: Connect to the specified database and retrieve data
          $data = DB::connection("castingmvp")->table($tableName)->get();
          //$data = DB::table($tableName)->get();
  
          if ($data->isEmpty()) {
            session()->flash('success', "No data found in $tableName to export.");
          } else {
              $csvContent = $this->convertToCSV($data);
  
              // Save the CSV content to the file
              Storage::disk('public')->put($fileName, $csvContent);
              session()->flash('info', "Data exported successfully to $filePath");
          }
  
          // Step 2: Truncate the table
         // DB::table($tableName)->truncate();
          DB::connection("castingmvp")->table($tableName)->truncate();
          session()->flash('success', "MVP Processing Successful.");

    }



    public function render()
    {
        return view('livewire.application-settings');
    }
}
