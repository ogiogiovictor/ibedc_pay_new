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
        'VAT',
        'agency'
    ];

    
    public function sumTodaySales() {
        // $today = Carbon::today();
        // return $this->whereDate('created_at', $today)
        //     ->whereIn('status', ['success', 'processing'])
        //     ->select(DB::raw('SUM(amount) as amount'))
        //     ->first()->amount;
        try {
            $today = Carbon::today();
    
            $result = $this->whereDate('created_at', $today)
                ->whereIn('status', ['success', 'processing'])
                ->select(DB::raw('SUM(amount) as amount'))
                ->first();
    
            if ($result) {
                return $result->amount ?? 0; // Return the sum or 0 if no result found
            } else {
                return 0; // Return 0 if there's no result
            }
        } catch (\Exception $e) {
            // Log or handle the exception appropriately
            // You can also print the error message for debugging purposes
            //Log::error("Error in sumTodaySales: " . $e->getMessage());
            return 0; // Return 0 if an error occurs
        }
    }

    public function countTodaysTransaction() {
        $today = Carbon::today();
        return $this->whereDate('created_at', $today)
                ->whereIn('status', ['success', 'processing'])
                ->count();
    }
}



