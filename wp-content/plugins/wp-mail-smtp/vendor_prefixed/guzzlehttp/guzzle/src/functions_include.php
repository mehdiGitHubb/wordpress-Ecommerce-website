<?php

namespace WPMailSMTP\Vendor;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WPMailSMTP\\Vendor\\GuzzleHttp\\uri_template')) {
    require __DIR__ . '/functions.php';
}
