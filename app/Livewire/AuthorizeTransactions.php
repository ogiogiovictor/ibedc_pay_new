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
        logger()->info('Authorizing transaction for user', ['user_id' => $user->id]);

    if (Gate::forUser($user)->allows(RoleEnum::super_admin()->value)) {
        logger()->info('User is a super_admin');
        $transaction = new PayTransactions();
        return $transaction->ibedcpayTransactions();
    } if (Gate::forUser($user)->allows(RoleEnum::payment_channel()->value)) {
        logger()->info('User is a payment_channel');
        $transaction = new PayTransactions();
        return $transaction->ibedcpayTransactions();
    } elseif (Gate::forUser($user)->allows(RoleEnum::admin()->value)) {
        logger()->info('User is an admin');
        $transaction = new PayTransactions();
        return $transaction->ibedcpayTransactions();
    } elseif (Gate::forUser($user)->allows(RoleEnum::manager()->value)) {
        logger()->info('User is a manager');
        $transaction = new PayTransactions();
        return $transaction->agencyTransaction($user->agency);  // This should be the region or bhub or hq
    } elseif (Gate::forUser($user)->allows(RoleEnum::supervisor()->value)) {
        logger()->info('User is a supervisor');
        $transaction = new PayTransactions();
        return $transaction->agencyTransaction($user->agency); // This should be the region or bhub or hq
    } elseif (Gate::forUser($user)->allows(RoleEnum::agent()->value)) {
        logger()->info('User is an agent');
        $transaction = new PayTransactions();
        return $transaction->agencyTransaction($user->agency);
    } else {
        logger()->error('User does not have the required role', ['user_id' => $user->id]);
        abort(403);
    }

    }


    // public function render()
    // {
    //     return view('livewire.authorize-transactions');
    // }
}
