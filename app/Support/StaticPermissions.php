<?php

namespace App\Support;

use Illuminate\Support\Arr;

final class StaticPermissions
{
    const DASHBOARD = 'Dashboard';

    const INCOME_REPORT = 'Income_Report';

    const SEND_TEXT_NOTIFICATION = 'Send_Text_Notification';

    const SCAN_QR_CODE = 'Scan_QR_Code';

    const CREATE_EVENT_SEATS_GRID = 'Create_Event_Seats_Grid';

    const EDIT_EVENT_SEATS_GRID = 'Edit_Event_Seats_Grid';
    const VIEW_EVENT_SEATS = 'View_Event_Seats';


    public static function allAdminPermissions(): array
    {
        return self::allPermissions([]);

    }

    public static function allPermissions(array $exclusives = []): array
    {
        try {
            $class = new \ReflectionClass(__CLASS__);
            $constants = $class->getConstants();
            $permissions = Arr::where($constants, function ($value, $key) use ($exclusives) {
                return !in_array($value, $exclusives);
            });

            return array_values($permissions);
        } catch (\ReflectionException $exception) {
            return [];
        }
    }

}
