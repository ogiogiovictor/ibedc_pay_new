<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NAC\PendingAccountCreation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\NAC\UploadHouses;
use App\Models\EMS\BusinessUnit;
use App\Models\EMS\Undertaking;
use App\Models\NAC\DSS;
use App\Models\NAC\ServiceAreaCode;
use Illuminate\Support\Facades\Http;
use App\Jobs\CustomerAccountJob;
use App\Models\NAC\ContinueAccountCreation;
use Illuminate\Support\Facades\Mail;
use App\Services\IbedcPayLogService;
use Illuminate\Support\Facades\Log;
use App\Models\NAC\AccoutCreaction;
use App\Models\EMS\ZoneCustomers;

class ResyncAccountNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resync-account-number';

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
        $this->info('🔄 Starting EMS Account Creation process...');

      $pendingAccounts = UploadHouses::where("status", 4)->where("duplicate", "0")->orderby('created_at', 'desc')->get();
     //  $pendingAccounts =  UploadHouses::where("status", 4)->where("account_no", "11/21/82/3551-01")->orderby('created_at', 'desc')->get();
    //    $pendingAccounts = UploadHouses::where("status", 4)
    //     ->whereIn("account_no", [
    //         '28/01/45/0066-01',
    //         '28/01/14/0092-01',
    //         '28/04/91/0055-01',
    //         '28/04/30/0054-01',
    //         '28/04/73/0123-01',
    //         '28/04/35/0090-01',
    //         '28/04/51/0089-01',
    //         '28/01/27/0086-01',
    //         '28/04/12/0172-01',
    //         '28/01/26/0053-01',
    //         '28/01/33/0069-01',
    //         '28/04/47/0079-01',
    //         '28/04/15/0107-01',
    //         '28/04/60/0031-01'
    //     ])
    //     ->orderBy('created_at', 'desc')
    //     ->get();

       
        if ($pendingAccounts->isEmpty()) {
            $this->info('✅ No pending accounts found.');
            return;
        }

        

        foreach ($pendingAccounts as $pending) {


            //Check if the account number exist in EMS Zone Customer
            $zoneCustomer =  ZoneCustomers::where("AccountNo", $pending->account_no)->first();
            //$zoneCustomer =  ZoneCustomers::where("AccountNo", "11/10/87/0071-01")->first();


           // $this->warn("⚠️ Customer Data from EMS ". json_encode($zoneCustomer));
            $this->warn("Business Hub ". $pending->business_hub);

            

            if(!$zoneCustomer){

                $this->info("Account Number does not exist {$pending->account_no} creating account.");

                 $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/save/customer/114/FX321G9D"; // live API

                $landlordInfo = ContinueAccountCreation::where("tracking_id", $pending->tracking_id)->first();

                // ✅ Use organisation_name if it's not null or empty, else use landlord_othernames
                if (!empty($landlordInfo->organisational_name)) {
                    $fname = "";
                    $sname = $landlordInfo->organisational_name; // No surname for organisations
                } else {
                    $fname = $landlordInfo->landlord_othernames;
                    $sname = $landlordInfo->landlord_surname;
                }

            

                 //$this->info("Account Number {$pending->account_no} Landlord Name. First Name: {$fname} Surname: {$sname}");

                 $servicecode =  $this->getAvailableServiceCode($pending);

                 // 🔥 Skip account if BUID is missing
                if (!$servicecode || empty($servicecode->BUID)) {
                    $this->warn("❌ BUID is NULL for Service Centre {$pending->service_center} - Skipping account: {$pending->account_no}");
                    continue;
                }

                 $lat = substr($pending->latitude ?? '', 0, 10);
                 $lng = substr($pending->longitude ?? '', 0, 10);

                  $data = [
                    "accountNo" => $pending->account_no,
                    "meterNo" => "",
                    "surname" =>  $sname,
                    "firstName" => $fname,
                    "otherNames" => "",
                    "email" => $landlordInfo->landlord_email ?? '',
                    "serviceAddress1" => ($pending->house_no ?? '') . ' ' . ($pending->full_address ?? ''),
                    "serviceAddress2" => $pending->business_hub ?? '',
                    "serviceAddressCity" => $pending->service_center ?? '',
                    "serviceAddressState" => $pending->region ?? '',
                    "tariffID" => 1, //$pending->tarrif,
                    "arrears" => '',
                    "mobile" => $landlordInfo->landlord_telephone,
                    "gisCoordinate" => ($lat ?? '') . ',' . ($lng ?? ''),
                    "buid" => $servicecode->BUID ?? '',
                    "distributionID" => $pending->dss,
                    "accessGroup" => "Administrator"
                ];

                  $this->warn("Payload ". json_encode($data));


                $this->info("Service Code {$servicecode->BUID } populating.");

                $this->warn("⚠️ Customer Data To Create on EMS ". json_encode($data));

                $response = Http::post($url, $data);

                $this->warn("⚠️ Save Customer Response ". json_encode($response->json()));

            
                // Check if account number is already assigned to another customer
                $responseData = $response->json();

                  if (isset($responseData['code']) && $responseData['code'] == '400' ) {

                     $this->info("Payload: " . json_encode($data));
                     $this->info("Response: " . json_encode($response->json()));
                    $this->info("Status: " . $response->status());
                        
                  }

                  if (isset($responseData['code']) && $responseData['code'] == '200' ) {

                     $this->info("Successful Response with Account Creation: " . json_encode($response->json()));

                     
                    // 🔥 UPDATE duplication count once account is successfully created/sent
                    UploadHouses::where('id', $pending->id)->update([
                        'duplicate' => 5
                    ]);
                        
                  }


                  //Check for successful account creation

            }

            $this->info("Account Number {$pending->account_no} already exist.");

        }


    }



      private function getAvailableServiceCode($uploadHouses)
    {

        $businessHub = trim($uploadHouses->business_hub);
        $serviceCenter = trim($uploadHouses->service_center);


      //  dd($businessHub, $serviceCenter);

         // Normalize business hub value
        if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($businessHub, 'Ijebu Igbo') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($serviceCenter, 'Ijebu jesa') === 0) {
            $serviceCenter = 'IJEBU JESA';
        }

        // if (strcasecmp($businessHub, 'Ilesa') === 0) {
        //     $businessHub = 'ILESHA';
        // }

        //This is the problem wiht oke-soda comment later
         if (strcasecmp($businessHub, 'Ile-ife') === 0) {
            $businessHub = 'ILE-IFE';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'AGO IWOYE') === 0) {
            $serviceCenter = 'AGO-IWOYE';
        }

        // Normalize business hub value
        if (strcasecmp($serviceCenter, 'ITA-OSHIN') === 0) {
            $serviceCenter = 'ITA OSHIN';
        }

        if (strcasecmp($serviceCenter, 'IMOWO II') === 0) {
            $serviceCenter = 'IMOWO 2 ';
        }

        if (strcasecmp($serviceCenter, 'ILORA') === 0) {
            $serviceCenter = 'ILORA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IJEBU IGBO') === 0) {
            $serviceCenter = 'IJEBU-IGBO';
        }

          // Normalize business hub value
        if (strcasecmp($serviceCenter, 'BODE-OLUDE') === 0) {
            $serviceCenter = 'BODE OLUDE';
        }


           // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IDIROKO ROAD') === 0) {
            $serviceCenter = 'IDIROKO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE AGBO') === 0) {
            $serviceCenter = 'OKE-AGBO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE OWA') === 0) {
            $serviceCenter = 'OKE-OWA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE OWA') === 0) {
            $serviceCenter = 'OKE-OWA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'Oke-soda') === 0) {
            $serviceCenter = 'oke-soda';
        }

        return ServiceAreaCode::whereRaw('LOWER(TRIM(Service_Centre)) = ?', [strtolower($serviceCenter)])
            ->whereRaw('LOWER(TRIM(BHUB)) = ?', [strtolower($businessHub)])
            ->where('number_of_customers', '<=', 1000)
            ->first();

    }


}
