<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CustomerAccount;
use App\Models\EMS\ZoneCustomers;
use App\Models\ECMI\EcmiCustomers;

class UpdateCustomerInformation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-customer-information';

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
        $this->info('***** Begin Process to Update Customer Names *************');

       // Retrieve users whose email starts with 'default'
    $users = CustomerAccount::where('email', 'like', 'default%')->get();

    foreach ($users as $user) {
        // Perform your update logic or any other action here
         // Get meter number and account type from the user
         $meterNoPrimary = $user->meter_no_primary;
         $accountType = $user->account_type;
 
         $this->info("Processing user: {$user->email}, Meter No: {$meterNoPrimary}, Account Type: {$accountType}");
         if($accountType == "Prepaid") {

            $custoInfo = EcmiCustomers::where("MeterNo", $meterNoPrimary)->first();

            if ($custoInfo) {
                // Update user name with customer info
                $user->update([
                    'name' => $custoInfo->Surname . ' ' . $custoInfo->OtherNames,
                ]);

                $this->info("Name Difference - Prepaid: {$user->name}, Update Surname: {$custoInfo->Surname}, Update Lastname: {$custoInfo->OtherNames}");
            } else {
                $this->info("No matching customer found in ECMI for Meter No: {$meterNoPrimary}");
            }

         }

         if($accountType == "Postpaid") {
            $custoInfo = ZoneCustomers::where("AccountNo", $meterNoPrimary)->first();

            if ($custoInfo) {
                // Update user name with customer info
                $user->update([
                    'name' => $custoInfo->Surname . ' ' . $custoInfo->FirstName,
                ]);

                $this->info("Name Difference - Postpaid: {$user->name}, Update Surname: {$custoInfo->Surname}, Update Lastname: {$custoInfo->FirstName}");
            } else {
                $this->info("No matching customer found in Zone for Account No: {$meterNoPrimary}");
            }

           // $this->info("Name Difference- Postpaid: {$user->name}, Update Surname: {$custoInfo->Surname}, Update Lastname {$custoInfo->FirstName}");
         }

         
    }

    $this->info('***** Process Completed *************');

    }
}
