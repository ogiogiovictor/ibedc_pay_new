<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WalletHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'payment_channel', 'price', 'transactionId', 'status', 'entry'
    ];

    /**
     * Generate monthly summary of wallet transactions and payments
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function monthlySummary(int $year, int $month): array
    {
        // Query for wallet transactions
        $transactionSummary = self::select(
            DB::raw('SUM(price) as total_transaction_amount'),
            DB::raw('COUNT(*) as transaction_count')
        )
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
        ->first();

        // Query for wallet payments
        $paymentSummary = self::select(
            DB::raw('SUM(price) as total_payment_amount'),
            DB::raw('COUNT(*) as payment_count')
        )
        ->where('payment_channel', '!=', null) // Assuming payment_channel is not null for payments
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
        ->first();

        // Constructing the monthly summary array
        $summary = [
            'year' => $year,
            'month' => $month,
            'transaction_summary' => [
                'total_amount' => $transactionSummary->total_transaction_amount ?? 0,
                'count' => $transactionSummary->transaction_count ?? 0,
            ],
            'payment_summary' => [
                'total_amount' => $paymentSummary->total_payment_amount ?? 0,
                'count' => $paymentSummary->payment_count ?? 0,
            ],
        ];

        return $summary;
    }
}
