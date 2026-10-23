<?php
// Shared start-up for every endpoint (all of them include this file).

// PHP 8.1+ makes mysqli throw exceptions by default. This app was written for the old behaviour
// (a failed query returns false, e.g. a report for a month that has no table yet), so keep that.
mysqli_report(MYSQLI_REPORT_OFF);

// Never print warnings/notices into the response (it would corrupt the JSON); log them instead.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');

$pos_config = require __DIR__ . '/config.php';
date_default_timezone_set($pos_config['timezone']);   // before ANY date() call, so month tables line up

$conn = mysqli_connect($pos_config['db_host'], $pos_config['db_user'], $pos_config['db_pass'], $pos_config['db_name'], (int) $pos_config['db_port']);
if (!$conn) {
    error_log('POS: database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    echo 'Error: cannot connect to the database. Is MySQL running in XAMPP, and does config.php match?';
    exit;
}
mysqli_set_charset($conn, 'utf8mb4');
// The app stores numbers as text and sometimes inserts empty strings; stay non-strict like the original XAMPP default.
mysqli_query($conn, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

// PHP 8 throws a TypeError if these get "false" (a failed query); PHP 7 only warned.
function pos_num_rows($result)
{
    return ($result instanceof mysqli_result) ? $result->num_rows : 0;
}
function pos_fetch_array($result)
{
    return ($result instanceof mysqli_result) ? mysqli_fetch_array($result) : null;
}
