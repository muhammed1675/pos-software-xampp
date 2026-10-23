<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$HtmlCode = "";
$IgnoreDetails = $_POST["dt"];
$alertToIgnore = $IgnoreDetails['It'];
$Ignorer = $IgnoreDetails['User'];
$Htmlcode = "";
date_default_timezone_set('Africa/Lagos');
$date = date("M d, Y h:i a");
$sql = "UPDATE expirydates SET ExpiryStatus = 'Ignored', dateignored = '" . $date . "', staffwhoignored = '" . $Ignorer . "' WHERE SN = '" . $alertToIgnore . "'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $Htmlcode = "Success success SUCCESS";
} else {
    $Htmlcode = "Failure";
}

echo $Htmlcode;
