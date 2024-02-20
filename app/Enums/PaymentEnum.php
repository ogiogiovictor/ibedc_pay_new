<?php

namespace App\Enums;

use \Spatie\Enum\Enum;

/**
 * @method static self Polaris()
 * @method static self FCMB()
 * @method static self Wallet()
 */

class PaymentEnum extends Enum
{
    /**
     * Get the labels for the enum values.
     *
     * @return array
     */
    protected static function labels(): array
    {
        return [
            'Polaris' => 'Polaris',
            'FCMB' => 'FCMB',
            'Wallet' => 'Wallet',
        ];
    }

    /**
     * Get the values for the enum values.
     *
     * @return array
     */
    protected static function values(): array
    {
        return [
            'Polaris' => 'Polaris',
            'FCMB' => 'FCMB',
            'Wallet' => 'Wallet',
        ];
    }
}
