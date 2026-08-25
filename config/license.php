<?php

return [
    'secret' => env('LICENSE_SECRET', env('APP_KEY')),
    'grace_days' => (int) env('LICENSE_GRACE_DAYS', 3),
    'period_days' => 30,
];
