<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Transactions\PayTransactions;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Gate;

class AuthorizeTransactions extends Component
{

    public static function authorizeTransaction($user)
    {
        if (Gate::forUser($user)->allows(RoleEnum::super_admin()->value)) {
            $transaction = new PayTransactions();
            return $transaction->ibedcpayTransactions();
        } elseif (Gate::forUser($user)->allows(RoleEnum::admin()->value)) {
            $transaction = new PayTransactions();
            return $transaction->ibedcpayTransactions();
        } elseif (Gate::forUser($user)->allows(RoleEnum::manager()->value)) {
            $transaction = new PayTransactions();
            return $transaction->agencyTransaction($user->agency);  // This should be the region or bhub or hq
        } elseif (Gate::forUser($user)->allows(RoleEnum::supervisor()->value)) {
            $transaction = new PayTransactions();
            return $transaction->agencyTransaction($user->agency); // This should be the region or bhub or hq
        } elseif (Gate::forUser($user)->allows(RoleEnum::agent()->value)) {
            $transaction = new PayTransactions();
            return $transaction->agencyTransaction($user->agency);
        } else {
            abort(403);
        }

    }


    // public function render()
    // {
    //     return view('livewire.authorize-transactions');
    // }
}
