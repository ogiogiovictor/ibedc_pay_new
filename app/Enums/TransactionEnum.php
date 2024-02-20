<?php

namespace App\Enums;

use \Spatie\Enum\Enum;

/**
 * @method static self Postpaid()
 * @method static self Prepaid()
 */
class TransactionEnum extends Enum
{
    /**
     * Get the labels for the enum values.
     *
     * @return array
     */
    protected static function labels(): array
    {
        return [
            'Postpaid' => 'Postpaid',
            'Prepaid' => 'Prepaid',
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
            'Postpaid' => 'Postpaid',
            'Prepaid' => 'Prepaid',
        ];
    }
}
