<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$allgoods = array();
$n = 0;
$Rdetails = $_POST['dt'];
$Rdt = $Rdetails['Rdt'];
$Rid = $Rdetails['Rid'];
$Amt = $Rdetails['Amt'];
$Username = $Rdetails['Username'];
$month = substr($Rdt, 0, 3);
$yr = substr($Rdt, 8, 12);
$ddb = $month . "_" . $yr;

$sql = "DELETE FROM " . $ddb . "_receiptdetails WHERE ReceiptNumber = '" . $Rid . "'";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if ($result) {
    $HtmlCode .= "Success1";
} else {
    $HtmlCode .= "Failure1";
}

$AllReceiptItems = array();
$sql = "SELECT ReceiptNumber, SalesID, Amount, Profit, Goodname, NoU from " . $ddb . "_salesrecords WHERE ReceiptNumber = '" . $Rid . "' AND Status = 'Active'";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

if (pos_num_rows($result) > 0) {
    $n = 0;
    while ($newArray = pos_fetch_array($result)) {
        $AllReceiptItems[$n]["rctid"] = $newArray[0];
        $AllReceiptItems[$n]["itid"] = $newArray[1];
        $AllReceiptItems[$n]["total"] = $newArray[2];
        $AllReceiptItems[$n]["pft"] = $newArray[3];
        $AllReceiptItems[$n]["ddb"] = $ddb;
        $AllReceiptItems[$n]["ordername"] = $newArray[4];
        $AllReceiptItems[$n]["Username"] = $Username;
        $AllReceiptItems[$n]["nou"] = $newArray[5];
        $n++;
    }
}

for ($n = 0; $n < count($AllReceiptItems); $n++) {

    $ans = DeleteAllReceiptItems($AllReceiptItems[$n], $conn);

}

function getTheQuantity(&$conn, $itid)
{

    $ULeft = 0;
    $TotalBulk = 0;
    $sqlQ = "SELECT  Unitleft FROM allgoodsproperties WHERE GoodID = '" . $itid . "'";

    $resultQ = mysqli_query($conn, $sqlQ);
    if (pos_num_rows($resultQ) > 0) {
        while ($newArrayQ = pos_fetch_array($resultQ)) {

            $ULeft = floatval($newArrayQ[0]);
        }
        $TotalBulk = $ULeft;

    }

    return $TotalBulk;
}

function DeleteAllReceiptItems($itemtodelete, $conn)
{

    $HtmlCode = "";
    $rctid = $itemtodelete['rctid'];
    $itid = $itemtodelete['itid'];
    $total = $itemtodelete['total'];
    $pft = $itemtodelete['pft'];
    $ddb = $itemtodelete['ddb'];
    $ordername = $itemtodelete['ordername'];
    $Username = $itemtodelete['Username'];
    $nou = is_numeric($itemtodelete['nou']) == 1 ? floatval($itemtodelete['nou']) : 0;

    $prevU = 0;

    $newU = 0;

    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");

    $SupplyTable = date('M') . "_" . date('Y') . "_allgoodssupplied";

    $Htmlcode = "";

    $AffectedTrackingNumber = 0;

    $sql3 = "SELECT TrackingNo FROM " . $ddb . "_salesrecords WHERE SalesID  = '" . $itid . "'";
    $result = mysqli_query($conn, $sql3) or die(mysqli_error($conn));
    if (pos_num_rows($result) > 0) {
        while ($newArray = pos_fetch_array($result)) {
            $AffectedTrackingNumber = $newArray[0];
        }
    }

    $sql = "UPDATE " . $ddb . "_salesrecords SET Status = 'DELETED' WHERE SalesID  = '" . $itid . "'";

    $result1 = mysqli_query($conn, $sql);
    if ($result1) {
        $Htmlcode .= "Success success SUCCESS 1";
    } else {
        $Htmlcode .= "Failure";
    }

    $sql2 = "SELECT UnitLeft, TrackingNo, GoodID from allgoodsproperties WHERE Goodname = '" . $ordername . "'";
    $TNo = 0;
    $Gid = "";
    $result = mysqli_query($conn, $sql2) or die(mysqli_error($conn));
    if (pos_num_rows($result) > 0) {
        while ($newArray = pos_fetch_array($result)) {
            $prevU = is_numeric($newArray[0]) == 1 ? floatval($newArray[0]) : 0;
            $TNo = $newArray[1];
            $Gid = $newArray[2];
        }
    }
    $TotalBulkBefore = getTheQuantity($conn, $Gid);
    $prevTotalUnit = floatval($prevU);
    $difference = floatval($nou);
    $newU = $prevTotalUnit + $difference;
    $NewTNo = $TNo + 1;
    $sql3 = "UPDATE allgoodsproperties SET  UnitLeft = " . $newU . ", TrackingNo = " . $NewTNo . " WHERE Goodname = '" . $ordername . "'";
    $result1 = mysqli_query($conn, $sql3);
    if ($result1) {
        $Htmlcode .= "Success success SUCCESS 3";
    } else {
        $Htmlcode .= "Failure";
    }
    $TotalBulkAfter = getTheQuantity($conn, $Gid);
    $SID = "S" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);
    $EID = "E" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);

    $dbmonthyear4 = date('M') . "_" . date('Y') . "_allgoodssupplied";
    $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear4 . " (SN INT NOT NULL AUTO_INCREMENT, "
        . "EntryID VARCHAR(100), "
        . "GoodID VARCHAR(100) NULL, "
        . "GoodEntryID VARCHAR(100) NULL, "
        . "Goodname VARCHAR(100), "
        . "QuantityUnit DOUBLE DEFAULT '0', "
        . "UnitCostPrice VARCHAR(100) DEFAULT '0', "
        . "Totalcostprice VARCHAR(100) DEFAULT '0', "
        . "DateReceived VARCHAR(50), "
        . "Receiver VARCHAR(50), "
        . "Supplier VARCHAR(100), "
        . "InvoiceNo VARCHAR(50), "
        . "BulkBalanceBefore DOUBLE NULL, "
        . "BulkBalanceAfter DOUBLE NULL, "
        . "TrackingNo INT(10) NULL, "
        . "PRIMARY KEY (SN)"
        . ")";
    if (mysqli_query($conn, $sql1) === true) {
        $HtmlCode .= "New table created successfully";
    }

    $sql2 = "INSERT INTO " . $SupplyTable . "(EntryID, GoodEntryID, GoodID, Goodname, QuantityUnit, Unitcostprice, Totalcostprice, DateReceived, Receiver, Supplier, InvoiceNo, BulkBalanceBefore, BulkBalanceAfter, TrackingNo ) VALUES ('" . $SID . "','" . $EID . "','" . $Gid . "','" . $ordername . "'," . $nou . ",'-','-','" . $date . "','" . $Username . "','Restored Item (" . $rctid . ") ','-'," . $TotalBulkBefore . "," . $TotalBulkAfter . ", " . $TNo . ")";

    if (mysqli_query($conn, $sql2)) {
        $HtmlCode .= "Success";
    } else {
        $HtmlCode .= "Error";
    }

    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $EID = "E" . date("his") . (rand(10000, 99999));
    $mess = $Username . " deleted  " . $ordername . " that worth " . $total . " Naira on Receipt Number " . $rctid . " that earlier cost " . $total . ".";
    $sqlI = "Insert into logrecords (Date, EventID, Event) Values ( '" . $date . "','" . $EID . "','" . $mess . "')";
    if (mysqli_query($conn, $sqlI)) {
        $HtmlCode .= "Success3";
    } else {
        $HtmlCode = "Failure";
    }

    return $HtmlCode;
}

echo $HtmlCode;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
