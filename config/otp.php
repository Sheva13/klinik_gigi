<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Registration Toggle
    |--------------------------------------------------------------------------
    |
    | When disabled, new registrations are created and activated immediately
    | without sending or verifying an email OTP. Set OTP_DISABLED=true in the
    | environment for demo deployments, and false to restore verification.
    |
    */
    'disabled' => env('OTP_DISABLED', false),
];
