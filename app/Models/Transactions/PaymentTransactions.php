<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentTransactions extends Model
{
    use HasFactory;

    protected $table = "payment_transactions";

    protected $fillable = [
        'email',
        'transaction_id',
        'phone',
        'amount',
        'account_type',
        'account_number',
        'meter_no',
        'status',
        'customer_name',
        'date_entered',
        'BUID',
        'provider',
        'providerRef',
        'receiptno',
        'payment_source',
        'Descript',
        'owner',
        'response_status',
        'latitude',
        'longitude',
        'source_type',
        'user_id',
        'units',
        'costOfUnits',
        'VAT'
    ];

    
    public function sumTodaySales() {
        $today = Carbon::today();
        return $this->whereDate('created_at', $today)
            ->whereIn('status', ['success', 'processing'])
            ->select(DB::raw('SUM(amount) as amount'))
            ->first()->amount;
    }

    public function countTodaysTransaction() {
        $today = Carbon::today();
        return $this->whereDate('created_at', $today)
                ->whereIn('status', ['success', 'processing'])
                ->count();
    }
}



