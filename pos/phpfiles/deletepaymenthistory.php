<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$PaymentToDelete = $_POST["dt"];
$PID = $PaymentToDelete["PID"];
$RID = $PaymentToDelete["RID"];
$Customer = $PaymentToDelete["Customer"];
$Username = $PaymentToDelete["Username"];
$DatePaid = $PaymentToDelete["DatePaid"];
$AmountPaid = $PaymentToDelete["AmountPaid"];
$month = substr($DatePaid, 0, 3);
$yr = substr($DatePaid, 8, 4);
$ddb = $month . "_" . $yr;
$TotalPaid = 0;

$Htmlcode = "";
$sql = "DELETE FROM debtpaymentrecords WHERE PaymentID  = '" . $PID . "'";

$result = mysqli_query($conn, $sql);
if ($result) {
    $Htmlcode = "Success success SUCCESS";
} else {
    $Htmlcode = "Failure1";
}

$sql2 = "DELETE FROM " . $ddb . "_receiptdetails WHERE ReceiptNumber = '" . $PID . "'";

$result2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));
if ($result2) {
    $HtmlCode .= "Success2";
} else {
    $HtmlCode .= "Failure2";
}

$sqlU = "SELECT Paid FROM debtreceiptdetails WHERE ReceiptNumber = '" . $RID . "'";
$resultU = mysqli_query($conn, $sqlU);
if ($resultU) {
    if (pos_num_rows($resultU) > 0) {
        while ($newArrayU = pos_fetch_array($resultU)) {
            $TotalPaid = $newArrayU[0];
        }
    }
}

$sqlM = "UPDATE debtreceiptdetails SET Paid = '" . ($TotalPaid - $AmountPaid) . "' WHERE ReceiptNumber  = '" . $RID . "'";
if (mysqli_query($conn, $sqlM)) {
    $HtmlCode .= "Success3";
} else {
    $HtmlCode .= "Error3";
}

if (strpos($HtmlCode, 'Success') !== false) {
    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $EID = "E" . date("his") . (rand(10000, 99999));
    $mess = $Username . " deleted Receipt Number " . $PID . " that worth " . $AmountPaid . " Naira, debt payment of " . $Customer;
    $sqlI = "Insert into logrecords (Date, EventID, Event) Values ( '" . $date . "','" . $EID . "','" . $mess . "')";
    if (mysqli_query($conn, $sqlI)) {
        $HtmlCode .= "Success4";
    } else {
        $HtmlCode = "Error4";
    }
}

echo $Htmlcode;
