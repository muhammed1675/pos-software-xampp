<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$SP = $_POST['dt'];
$itemname = ucfirst($SP['NGoodName']);
$NEID = $SP['NEid'];
$NGID = $SP['NGid'];
$NDate = $SP['NDate'];
$NSoldin = $SP['NSoldin'];
$NGoodName = $SP['NGoodName'];
$NQUnit = floatval($SP['NQUnit']);
$OldQUnit = floatval($SP['OldQUnit']);
$QUdiff = 0;
$Dbase = $SP['Dbase'];
$NSupplier = $SP['NSupplier'];
$staff = $SP["Staff"];
$CostPrice = floatval($SP["CostPrice"]);
$Converter = floatval($SP["Converter"]);
$TrackNo = 0;

$UnitPrice = $Converter > 0 ? $CostPrice / $Converter : 0;

$TT = 0;

$TU = 0;

if ($NQUnit > 0) {
    $TU = ($NQUnit * $UnitPrice);
}

$TT = $TU;

$sql1 = "SELECT  UnitLeft, TrackingNo FROM allgoodsproperties WHERE GoodID = '" . $NGID . "'";
$result1 = mysqli_query($conn, $sql1);
if ($result1) {
    if (pos_num_rows($result1) > 0) {
        while ($newArray1 = pos_fetch_array($result1)) {

            if ($NQUnit != $OldQUnit) {
                $QUdiff = $NQUnit - $OldQUnit;
                $newunit = floatval($newArray1[0]) + $QUdiff;
                $TrackNo = floatval($newArray1[1]);
                $trk = $TrackNo + 1;
                $sql2 = "UPDATE allgoodsproperties SET UnitLeft = " . $newunit . "  TrackingNo = " . $trk . " WHERE GoodID = '" . $NGID . "'";
                if (mysqli_query($conn, $sql2)) {
                    $HtmlCode = "Success3";
                } else {
                    $HtmlCode .= "Error3";
                }
            }
        }
    }
}

if ($NQUnit != $OldQUnit) {

    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $EID = "E" . date("his") . (rand(10000, 99999));
    $SID = "S" . date("his") . (rand(10000, 99999));
    $dbmonthyear1 = date('M') . "_" . date('Y') . "_allgoodssupplied";

    $sql2 = "INSERT INTO " . $dbmonthyear1 . "(EntryID, GoodEntryID, GoodID, Goodname, QuantityUnit, Unitcostprice, Totalcostprice, DateReceived, Receiver, Supplier, InvoiceNo, BulkBalanceBefore, BulkBalanceAfter, TrackingNo) "
        . "VALUES ('" . $SID . "','" . $EID . "','" . $NGID . "','" . $itemname . "','" . $QUdiff . "','Edited Supply Record','Edited Supply Record','" . $date . "','" . $staff . "','Not Supplied','No Invoice','" . $OldQUnit . "','" . $NQUnit . "'," . $TrackNo . ")";

    if (mysqli_query($conn, $sql2)) {
        date_default_timezone_set('Africa/Lagos');
        $date = date("M d, Y h:i a");
        $EID = "E" . date("his") . (rand(10000, 99999));
        $msg = $staff . " changed the number of units of " . $itemname . " supplied on " . $NDate . " by " . $NSupplier . " from " . $OldQUnit . " to " . $NQUnit;
        $sqlL = "INSERT INTO logrecords (Date, EventID, Event) VALUES ('" . $date . "','" . $EID . "','" . $msg . "')";
        if (mysqli_query($conn, $sqlL)) {
            $HtmlCode .= "Success6";
        } else {
            $HtmlCode .= "Error6";
        }
        $HtmlCode = "Success5";
        $HtmlCode .= "Success Track";
    } else {
        $HtmlCode .= "Error Track";
    }

    //Old code

}

function getTheQuantity(&$conn, $itnm)
{

    $ULeft = 0;
    $CRate = 0;
    $TotalBulk = 0;
    $sqlQ = "SELECT  Unitleft FROM allgoodsproperties WHERE Goodname = '" . $itnm . "'";

    $resultQ = mysqli_query($conn, $sqlQ);
    if (pos_num_rows($resultQ) > 0) {
        while ($newArrayQ = pos_fetch_array($resultQ)) {

            $ULeft = floatval($newArrayQ[0]);
        }

        $TotalBulk = $ULeft;

    }
    return $TotalBulk;
}

echo ($QUdiff);
