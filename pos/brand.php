<?php
// Serves the compiled app script with the shop's own details filled in from phpfiles/config.php.
$cfg = require __DIR__ . '/phpfiles/config.php';
$f = isset($_GET['f']) ? $_GET['f'] : '';
if (!preg_match('/^main\.[a-z0-9]+\.chunk\.js$/', $f) || !is_file(__DIR__ . '/static/js/' . $f)) {
    http_response_code(404);
    exit;
}
$path = __DIR__ . '/static/js/' . $f;
$esc = function ($v) {   // safe inside a double-quoted JS string
    $j = json_encode((string) $v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    return substr($j, 1, -1);
};
$map = array(
    '{{SHOP_NAME}}'    => $esc(strtoupper($cfg['shop_name'])),
    '{{SHOP_ABOUT}}'   => $esc($cfg['shop_about']),
    '{{SHOP_ADDRESS}}' => $esc($cfg['shop_address']),
    '{{SHOP_PHONES}}'  => $esc($cfg['shop_phones']),
    '{{DEV_CREDIT}}'   => $esc($cfg['dev_credit']),
    '{{DEV_NAME}}'     => $esc($cfg['dev_name']),
    '{{DEV_PHONE}}'    => $esc($cfg['dev_phone']),
    '{{DEV_EMAIL}}'    => $esc($cfg['dev_email']),
);
$etag = '"' . md5(filemtime($path) . '|' . filemtime(__DIR__ . '/phpfiles/config.php') . '|' . serialize($map)) . '"';
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-cache');
header('ETag: ' . $etag);
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
    http_response_code(304);
    exit;
}
echo strtr(file_get_contents($path), $map);
