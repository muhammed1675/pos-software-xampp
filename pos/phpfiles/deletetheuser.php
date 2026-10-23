<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$us = $_POST['dt'];
$usertodelete = $us['Username'];
$Fullname = $us['Fullname'];
$usname = $us['usname'];
$HtmlCode = "";
$sql = "DELETE FROM staffrecord WHERE Username  = '" . $usertodelete . "'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $HtmlCode = "Success success SUCCESS";
} else {
    $HtmlCode = "Failure";
}

if ($HtmlCode === "Success success SUCCESS") {
    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $EID = "E" . date("his") . (rand(10000, 99999));
    $mess = $usname . " deleted the account of " . $Fullname;
    $sqlI = "Insert into logrecords (Date, EventID, Event) Values ( '" . $date . "','" . $EID . "','" . $mess . "')";

    if (mysqli_query($conn, $sqlI)) {
        $HtmlCode .= "Success3";
    } else {
        $HtmlCode = "Error3";
    }
}

echo $HtmlCode;
