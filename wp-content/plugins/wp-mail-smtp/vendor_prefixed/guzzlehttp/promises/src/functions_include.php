<?php

namespace WPMailSMTP\Vendor;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WPMailSMTP\\Vendor\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
