<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$HtmlCode = "";
$Username = $_POST["dt"];
date_default_timezone_set("Africa/Lagos");
$date = date("M d, Y h:i a");
$EID = "E" . date("his") . (rand(10000, 99999));
$msg = $Username . " logged out";
$sqlL = "INSERT INTO logrecords (Date, EventID, Event) VALUES ('" . $date . "','" . $EID . "','" . $msg . "')";
if (mysqli_query($conn, $sqlL)) {
    $HtmlCode .= "Success6";
} else {
    $HtmlCode .= "Error6";
}

echo ($HtmlCode);
