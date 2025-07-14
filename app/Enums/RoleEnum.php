<?php

namespace App\Enums;

use \Spatie\Enum\Enum;

/**
 * @method static self user()
 * @method static self admin()
 * @method static self supervisor()
 * @method static self manager()
 * @method static self customer()
 * @method static self agent()
 * @method static self super_admin()
 * @method static self agency_admin()
 * @method static self payment_channel()
 * @method static self dtm()
 * @method static self bhm()
 * @method static self region()
 * @method static self billing()
 * @method static self mso()
 * @method static self audit()
 * @method static self rico()
 */

class RoleEnum extends Enum
{
    /**
     * Get the labels for the enum values.
     * 
     * @return array
     */
    protected static function labels(): array
    {
        return [
            'user' => 'user',
            'admin' => 'admin',
            'supervisor' => 'supervisor',
            'manager' => 'manager',
            'customer' => 'customer',
            'agent' => 'agent',
            'super_admin' => 'super_admin',
            'agency_admin' => 'agency_admin',
            'payment_channel' => 'payment_channel',
            'dtm' => 'dtm',
            'bhm' => 'bhm',
            'region' => 'region',
            'billing' => 'billing',
            'mso' => 'mso',
            'audit' => 'audit',
            'rico' => 'rico',
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
            'user' => 'user',
            'admin' => 'admin',
            'supervisor' => 'supervisor',
            'manager' => 'manager',
            'customer' => 'customer',
            'agent' => 'agent',
            'super_admin' => 'super_admin',
            'agency_admin' => 'agency_admin',
            'payment_channel' => 'payment_channel',
            'dtm' => 'dtm',
            'bhm' => 'bhm',
            'region' => 'region',
            'billing' => 'billing',
            'mso' => 'mso',
            'audit' => 'audit',
            'rico' => 'rico',
        ];
    }
}
