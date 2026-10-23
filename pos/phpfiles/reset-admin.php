<?php
// Usage (Command Prompt):  php reset-admin.php NewPassword
// Sets/creates the ADMIN login using the secret_key in config.php. Command line only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if ($argc < 2 || strlen($argv[1]) < 6) { fwrite(STDERR, "Usage: php reset-admin.php NewPassword   (6+ characters)\n"); exit(1); }
include __DIR__ . '/connectdatabase.php';
function enc($d, $key) {
    $r = ''; $kl = strlen($key);
    for ($i = 0; $i < strlen($d); $i++) {
        $r .= chr(ord($d[$i]) + ord($key[($i % $kl) - 1 < 0 ? $kl - 1 : ($i % $kl) - 1]));
    }
    return strtr(base64_encode($r), '+/', '-_');
}
$pw = mysqli_real_escape_string($conn, enc($argv[1], $pos_config['secret_key']));
$date = date('M d, Y h:i a');
mysqli_query($conn, "INSERT INTO staffrecord (Username, Surname, Firstname, Middlename, Gateway, Address, PhoneNumber, Email, Category, DateRegistered, LastLogin)
  VALUES ('ADMIN','Administrator','System','','$pw','','','','Admin','$date','$date')
  ON DUPLICATE KEY UPDATE Gateway = '$pw'");
mysqli_query($conn, "INSERT IGNORE INTO permission (ID, NS, MR, SI, CP, ASt, VS, CPw, SP, SR, GS, IV, CR, VD, CF, GH, US)
  VALUES ('ADMIN','1','1','1','1','1','1','1','1','1','1','1','1','1','1','1','1')");
echo mysqli_error($conn) ?: "ADMIN password set.\n";
