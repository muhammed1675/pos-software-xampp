<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";

$exptodelete = $_POST["dt"];
$Ddb = $exptodelete['ddb'];
$Datesent = $exptodelete['Datesent'];
$Rid = $exptodelete['EntryID'];
$Amount = $exptodelete['Amount'];
$Giver = $exptodelete['Giver'];
$Receiever = $exptodelete['Receiver'];
$Purpose = $exptodelete['Purpose'];
$Username = $exptodelete['Username'];
$Htmlcode = "";
$sql = "DELETE FROM " . $Ddb . "_expenditures WHERE EntryId  = '" . $Rid . "'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $Htmlcode = "Success success SUCCESS";
} else {
    $Htmlcode = "Failure";
}

date_default_timezone_set('Africa/Lagos');
$date = date("M d, Y h:i a");
$EID = "E" . date("his") . (rand(10000, 99999));
$mess = $Username . " deleted the expenditure (earlier recorded  on " . $Datesent . ") given to " . $Receiever . " that worth " . $Amount . " Naira for the purpose of \" " . $Purpose . "\" and given by " . $Giver . ".";
$sqlI = "Insert into logrecords (Date, EventID, Event) Values ( '" . $date . "','" . $EID . "','" . $mess . "')";
if (mysqli_query($conn, $sqlI)) {
    $HtmlCode .= "Success3";
} else {
    $HtmlCode = "Failure";
}

echo $Htmlcode;
