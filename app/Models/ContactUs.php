<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $table = "contact_us";

    protected $fillable = [
        'name', 'message', 'email', 'subject', 'account_type', 'unique_code', 'status', 'phone'
    ];

    public static function userComplains(): string {
        //return number_format(self::count());
        // Count the number of complaints where status is 1
    $complainsCount = self::where('status', 1)->count();

    // Return the count formatted as a string with number formatting
    return number_format($complainsCount);
    }
}
