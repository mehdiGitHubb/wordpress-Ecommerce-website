<?php

namespace WPMailSMTP\Vendor;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WPMailSMTP\\Vendor\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}
