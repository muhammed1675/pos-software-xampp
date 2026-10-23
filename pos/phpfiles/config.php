<?php
// Retail POS - settings for this install. Edit here, not in the endpoint files.
// (XAMPP defaults: MySQL/MariaDB on port 3306, user "root", empty password.)
return [
    'db_host'  => '127.0.0.1',
    'db_port'  => 3306,
    'db_user'  => 'root',
    'db_pass'  => '',
    'db_name'  => 'retail_pos',
    'timezone' => 'Africa/Lagos',

    // Shown on the login page, header and printed receipts.
    'shop_name'    => 'YOUR SHOP NAME',
    'shop_address' => 'Shop address line',
    'shop_phones'  => '0800 000 0000',
    'shop_about'   => 'A short description of what your shop sells.',   // login page "About us"

    // "Software by" lines (receipt footer and the Contact page).
    'dev_name'   => 'YOUR COMPANY NAME',
    'dev_phone'  => '0800 000 0000',
    'dev_email'  => 'you@example.com',
    'dev_credit' => 'YOUR COMPANY NAME | 0800 000 0000',

    // Used to scramble stored passwords. Change it for each customer, then run:
    //   php phpfiles/reset-admin.php NewPassword
    // (changing it later makes existing passwords unreadable - do it BEFORE creating users).
    'secret_key' => 'pk_GOKZcS6pgpBiJidoS7dzdzCs',

    // "Update Server" (online sync) is OFF until you fill this in and set sync_enabled to true.
    // WARNING: the sync TRUNCATES the same-named tables in the remote database, so point it at a
    // database that belongs to THIS shop only.
    'sync_enabled' => false,
    'sync_remote'  => [
        'host' => '',
        'port' => 3306,
        'user' => '',
        'pass' => '',
        'name' => '',
    ],
];
