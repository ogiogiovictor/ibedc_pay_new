<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use App\Models\VirtualAccountTrasactions;
use Illuminate\Support\Facades\Mail;
use App\Models\VirtualAccount;
use App\Models\WalletLog;
use App\Models\CustomerAccount;

class CreditUserWallet extends Component
{

    public $email;
    public $amount;
    public $providerRef;
    public $transref;

    public $defaultEmail;
    public $defaultAmount;
    public $defaultTransref;
    public $defaultProviderRef;


    public function creditWallet() {

         Session::flash('error', 'This function is currently disabled');
            return;
         $authuser = Auth::user();

        if($authuser->authority !== (RoleEnum::super_admin()->value )) {
            //redirect to agency dashboard
            abort(403, 'Unauthorized action.');
        } 

        if (
            Auth::user()->email !== 'victor.ogiogio@ibedc.com' &&
            Auth::user()->email !== 'adebayo.oyebamiji@ibedc.com' &&
            Auth::user()->email !== 'victor.otunuga@ibedc.com'
        ) {
            session()->flash('error', 'You do not have permission to perform this action.');
            return;
        }

         
        $this->validate([
            'email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01',
            'transref' => 'required|string',
            'providerRef' => 'required|string|unique:wallet_histories,provider_reference',
        ]);

          
        $user = User::where('email', trim($this->email))->first();

             if (!$user) {
                session()->flash('error', 'User not found.');
                return;
             }


       $transaction = VirtualAccountTrasactions::where("flw_ref", $this->providerRef)->first();
       if ($transaction) {
            session()->flash('error', 'Transaction reference already found.');
            return;
        }
       



        // Find or create wallet for the user
        $wallet = WalletUser::firstOrCreate(
            ['user_id' => $user->id],
            ['wallet_amount' => 0]
        );

        // Credit the wallet
        $wallet->wallet_amount += (float) abs($this->amount);
        $wallet->save();

        // Create wallet history entry
         WalletHistory::create([
          'user_id' => $user->id,
          'payment_channel' => 'Wallet',
          'price' => $this->amount,
          'transactionId' => $this->transref,
          'status' => 'successful',
          'entry' => 'CR', // DR = Debit Record
          'provider_reference' => $this->providerRef,
          ]);


          WalletLog::create([
            'user_id' => $authuser->id,
            'price' => $this->amount,
            'provider_reference' => $this->providerRef,
            'current_balance' => $wallet->wallet_amount,
            'previous_balance' => $wallet->wallet_amount - (float) abs($this->amount),
            'customer_id' => $user->id,
          ]);


          //Send email notification to user about wallet credit
          $accountno = VirtualAccount::where('user_id', $user->id)->value('account_no');

          // ✅ Simple inline email
            try {
                $to = $user->email;
                $cc = ['Victor.Otunuga@ibedc.com', 'adebayo.oyebamiji@ibedc.com', 'victor.ogiogio@ibedc.com', 'Thelma.Okunorobo@ibedc.com', 'mayowa.ariyo@ibedc.com', 'babatunde.bodunde@ibedc.com' ]; // add more if needed

                Mail::raw(
                    "Dear {$user->name},\n\nYour wallet has been credited with ₦" . number_format($this->amount, 2) . ".\n\nTransaction Ref: {$this->transref}\nProvider Ref: {$this->providerRef}\nWallet Account: {$accountno}\n\nThank you.",
                    function ($message) use ($to, $cc) {
                        $message->to($to)
                                ->bcc($cc)
                                ->subject('Wallet Credited Successfully');
                    }
                );
            } catch (\Exception $e) {
                \Log::error('Mail sending failed: ' . $e->getMessage());
            }

           session()->flash('success', 'Wallet credited successfully.');
    }



    public function defaultCreditWallet(){

         Session::flash('error', 'This function is currently disabled');
            return;
            
        $authuser = Auth::user();

        if($authuser->authority !== (RoleEnum::super_admin()->value )) {
            abort(403, 'Unauthorized action.');
        } 

        if (
            Auth::user()->email !== 'victor.ogiogio@ibedc.com' &&
            Auth::user()->email !== 'adebayo.oyebamiji@ibedc.com' &&
            Auth::user()->email !== 'victor.otunuga@ibedc.com'
        ) {
            session()->flash('error', 'You do not have permission to perform this action.');
            return;
        }

         
        $this->validate([
            'defaultEmail' => 'required|exists:login_customer_accounts,email',
             //'defaultEmail' => 'required',
            'defaultAmount' => 'required|numeric|min:0.01',
            'defaultTransref' => 'required|string',
            'defaultProviderRef' => 'required|string|unique:wallet_histories,provider_reference',
        ]);


         $updateEmail = str_starts_with($this->defaultEmail, 'default') || str_starts_with($this->email, 'noemail');

         if (!$updateEmail) {
                session()->flash('error', 'Customer account not found for default/noemail email.');
                return;
            }

        $user = CustomerAccount::where('email', trim($this->defaultEmail))->first();

        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

    //   $transaction = VirtualAccountTrasactions::where("flw_ref", $this->defaultProviderRef)->first();
    //    if ($transaction) {
    //         session()->flash('error', 'Transaction reference already found.');
    //         return;
    //     }


      session()->flash('error', 'Blocked');
      return;


      $wallectCheck = WalletHistory::where("provider_reference", $this->defaultProviderRef)->first();
      if($wallectCheck) {
          session()->flash('error', 'Transaction reference already found in Wallet History');
            return;
      }
       

        // Find or create wallet for the user
        $wallet = WalletUser::firstOrCreate(
            ['user_id' => $user->id],
            ['wallet_amount' => 0]
        );

        // Credit the wallet
        $wallet->wallet_amount += (float) abs($this->defaultAmount);
        $wallet->save();


        // Create wallet history entry
         WalletHistory::create([
          'user_id' => $user->id,
          'payment_channel' => 'Wallet',
          'price' => $this->defaultAmount,
          'transactionId' => $this->defaultTransref,
          'status' => 'successful',
          'entry' => 'CR', // DR = Debit Record
          'provider_reference' => $this->defaultProviderRef,
          ]);


          WalletLog::create([
            'user_id' => $authuser->id,
            'price' => $this->defaultAmount,
            'provider_reference' => $this->defaultProviderRef,
            'current_balance' => $wallet->wallet_amount,
            'previous_balance' => $wallet->wallet_amount - (float) abs($this->defaultAmount),
            'customer_id' => $user->id,
          ]);

            $accountno = VirtualAccount::where('user_id', $user->id)->value('account_no');

          // ✅ Simple inline email
            try {
                $to = "adekemi.ajiboye@ibedc.com";
                $cc = ['Victor.Otunuga@ibedc.com', 'adebayo.oyebamiji@ibedc.com', 'victor.ogiogio@ibedc.com', 'Thelma.Okunorobo@ibedc.com', 'mayowa.ariyo@ibedc.com', 'babatunde.bodunde@ibedc.com' ]; // add more if needed

                Mail::raw(
                    "Dear {$user->name},\n\nYour wallet has been credited with ₦" . number_format($this->defaultAmount, 2) . ".\n\nTransaction Ref: {$this->defaultTransref}\nProvider Ref: {$this->defaultProviderRef}\nWallet Account: {$accountno}\n\nThank you.",
                    function ($message) use ($to, $cc) {
                        $message->to($to)
                                ->bcc($cc)
                                ->subject('Wallet Credited Successfully');
                    }
                );
            } catch (\Exception $e) {
                \Log::error('Mail sending failed: ' . $e->getMessage());
            }

           session()->flash('success', 'Wallet credited successfully.');


    }




    public function render()
    {
        return view('livewire.credit-user-wallet');
    }
}
