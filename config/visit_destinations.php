<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Visit Destinations Feature Flag
    |--------------------------------------------------------------------------
    |
    | When disabled, visit_destination_id is still assigned on new visits so
    | the destination queue works. Only push notifications are skipped.
    |
    */
    'enabled' => env('VISIT_DESTINATIONS_ENABLED', false),
];
