<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'message', 'email', 'subject', 'account_type', 'unique_code', 'status', 'phone'
    ];

    public static function userComplains(): string {
        return number_format(self::count());
    }
}
