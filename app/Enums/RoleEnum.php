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
            'agency_admin' => 'agency_admin'
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
            'agency_admin' => 'agency_admin'
        ];
    }
}
