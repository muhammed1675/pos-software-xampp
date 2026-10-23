<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$TheRecord = $_POST["dt"];
$UpdatedRecord = $TheRecord["NewRecord"];
$OldRecord = $TheRecord["OldRecord"];
$Staff = $TheRecord["Staff"];
$IT = $UpdatedRecord["Itemname"];
$Itemgid = $UpdatedRecord["GID"];
$n = 0;
$itemname = $UpdatedRecord["Itemname"];
$TrackNo = 0;
$PriceChanged = false;
$formattedquantity = getTheQuantity($conn, $itemname);

$UBefore = floatval($OldRecord["UnitLeft"]);

$UAfter = floatval($UpdatedRecord["Unit"]);
$QDiff = floatval($QAfter - $QBefore);
$UDiff = floatval($UAfter - $UBefore);

$sqlC = "SELECT TrackingNo from allgoodsproperties WHERE GoodID = '" . $Itemgid . "'";
$resultC = mysqli_query($conn, $sqlC) or die(mysqli_error($conn));
if (pos_num_rows($resultC) > 0) {
    while ($newArrayC = pos_fetch_array($resultC)) {
        $TrackNo = floatval($newArrayC[0]);

    }
}

GetLog($UpdatedRecord["Unit"] != "" ? $UpdatedRecord["Unit"] : 0, $OldRecord["UnitLeft"] != "" ? $OldRecord["UnitLeft"] : 0, " Unit quantity left in stock", $conn, $IT, $Itemgid, $HtmlCode, $Staff, $formattedquantity, "", $TrackNo, 0);

if (GetLog($UpdatedRecord["USP1"] != "" ? $UpdatedRecord["USP1"] : 0, $OldRecord["USP1"] != "" ? $OldRecord["USP1"] : 0, "Unit Selling Price 1", $conn, $IT, $Itemgid, $HtmlCode, $Staff, $formattedquantity, "PC", $TrackNo, 1)) {
    $TrackNo = $TrackNo + 1;
    $PriceChanged = true;

}

if (GetLog($UpdatedRecord["USP2"] != "" ? $UpdatedRecord["USP2"] : 0, $OldRecord["USP2"] != "" ? $OldRecord["USP2"] : 0, "Unit Selling Price 2", $conn, $IT, $Itemgid, $HtmlCode, $Staff, $formattedquantity, "PC", $TrackNo, 2)) {
    $TrackNo = $TrackNo + 1;
    $PriceChanged = true;

}

if (GetLog($UpdatedRecord["USP3"] != "" ? $UpdatedRecord["USP3"] : 0, $OldRecord["USP3"] != "" ? $OldRecord["USP3"] : 0, "Unit Selling Price 3", $conn, $IT, $Itemgid, $HtmlCode, $Staff, $formattedquantity, "PC", $TrackNo, 3)) {
    $TrackNo = $TrackNo + 1;
    $PriceChanged = true;

}

$trk = $TrackNo + 1;
$UpdatedRecord["Unit"] = floatval($UpdatedRecord["Unit"]) >= 0 ? floatval($UpdatedRecord["Unit"]) : 0;
$sql = "UPDATE allgoodsproperties SET Currentcostprice = '" . $UpdatedRecord["Costprice"] . "', UnitLeft = " . $UpdatedRecord["Unit"] . ",  UnitSellingPrice1 = '" . $UpdatedRecord["USP1"] . "', UnitSellingPrice2 = '" . $UpdatedRecord["USP2"] . "', UnitSellingPrice3 = '" . $UpdatedRecord["USP3"] . "', TrackingNo = " . $trk . " WHERE Goodname = '" . $UpdatedRecord["Itemname"] . "'";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if ($result) {
    $HtmlCode = "Success";
} else {
    $HtmlCode = "Error";
}

$formattedquantity2 = getTheQuantity($conn, $itemname);

if ($formattedquantity != $formattedquantity2) {
    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $EID = "E" . date("his") . (rand(10000, 99999));
    $SID = "S" . date("his") . (rand(10000, 99999));
    $dbmonthyear1 = date('M') . "_" . date('Y') . "_allgoodssupplied";

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

    $sql2 = "INSERT INTO " . $dbmonthyear1 . "(EntryID, GoodEntryID, GoodID, Goodname, QuantityUnit, Unitcostprice, Totalcostprice, DateReceived, Receiver, Supplier, InvoiceNo, BulkBalanceBefore, BulkBalanceAfter, TrackingNo) "
        . "VALUES ('" . $SID . "','" . $EID . "','" . $Itemgid . "','" . $itemname . "'," . $UDiff . ",'Not Supplied','Not Supplied','" . $date . "','" . $Staff . "','Not Supplied','No Invoice'," . $formattedquantity . "," . $formattedquantity2 . "," . $TrackNo . ")";

    if (mysqli_query($conn, $sql2)) {
        $HtmlCode .= "Success Track";
    } else {
        $HtmlCode .= "Error Track";
    }
}

function GetLog($Final, $Initial, $Prop, $conn, $it, $itD, &$HtmlCode, $Stf, $quan, $pc, $track, $Number)
{
    if (floatval($Final) != floatval($Initial)) {
        $EID = "E" . date("his") . (rand(10000, 99999));
        $date = date("M d, Y h:i a");
        $msg = $Stf . " Changed the " . $Prop . " of " . $it . "  from " . $Initial . " to " . $Final . " through the Inventory Menu";
        $sqlA = "INSERT INTO logrecords (Date, EventID, Event) "
            . "VALUES ('" . $date . "','" . $EID . "','" . $msg . "')";

        if (mysqli_query($conn, $sqlA)) {
            $HtmlCode .= "Success Log";

        }
        if ($pc == "PC") {
            $dbmonthyear3 = date('M') . "_" . date('Y') . "_price_change_track";
            $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear3 . " (SN INT NOT NULL AUTO_INCREMENT, "
                . "EntryID VARCHAR(100), "
                . "ItemID VARCHAR(100), "
                . "Itemname VARCHAR(100), "
                . "Date VARCHAR(100) NULL, "
                . "Description VARCHAR(200) NULL, "
                . "Quantity DOUBLE DEFAULT '0', "
                . "Staff VARCHAR(100) DEFAULT '0', "
                . "TrackingNo INT(10) NULL, "
                . "PRIMARY KEY (SN)"
                . ")";
            if (mysqli_query($conn, $sql1) === true) {
                $HtmlCode = "New table created successfully";
            }

            date_default_timezone_set('Africa/Lagos');
            $date = date("M d, Y h:i a");
            $EID = "E" . date("his") . (rand(10000, 99999));
            $msg = "Price" . $Number . " changed for one " . $Prop . " from " . $Initial . " to " . $Final;
            $sqlA = "INSERT INTO " . $dbmonthyear3 . " (EntryID, GoodID,  Itemname, Date, Description, Quantity, Staff, TrackingNo) "
                . "VALUES ('" . $EID . "','" . $itD . "','" . $it . "','" . $date . "','" . $msg . "'," . $quan . ",'" . $Stf . "'," . $track . ")";

            if (mysqli_query($conn, $sqlA)) {
                $HtmlCode .= "Success Track";
            } else {
                $HtmlCode .= "Error Track";
            }
            return true;
        }
    } else {
        $HtmlCode = "ErrorA";
        return false;
    }

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

        $TotalBulk = floatval($ULeft);

    }
    return $TotalBulk;
}

echo ($HtmlCode);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
