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
$itemname = ucfirst($SP['myItemname']);
$itemID = ucfirst($SP['myItemID']);
$supplier = "Supplier";
$invoice = "Invoice";
$soldin = $SP['mySoldIn'];
$unit1 = $SP['myUP1'];
$unit2 = $SP['myUP2'];
$unit3 = $SP['myUP3'];
$logreport = $SP['logreport'];
$isNew = $SP['isNew'];
$staff = $SP['Staff'];
$Newname = $SP['myNewname'];

$TrackNo = 0;
$PriceChanged = false;
$fq = getTheQuantity($conn, $itemname);

$formattedquantity = $fq["Formatted"];
$UBefore = $fq["Unit"];
$Track = $fq["Track"];
$IUP1 = $fq["IUP1"];
$IUP2 = $fq["IUP2"];
$IUP3 = $fq["IUP3"];

date_default_timezone_set('Africa/Lagos');
$date = date("M d, Y h:i a");
$GID = "G" . date("Ymd") . date("his");
$SID = "S" . date("Ymd") . date("his");
$EID = "E" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);
$itemgid = $EID;
$NN = $Newname != "" ? ", Goodname = '" . $Newname . "'" : "";

function checkChange($initial, $present, $type, $conn, $staff, $soldin, $b, $track, $formattedquantity, $Itmna, $itD, $Number)
{

    if ($initial != $present) {
        $dbmonthyear3 = date('M') . "_" . date('Y') . "_price_change_track";
        $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear3 . " (SN INT NOT NULL AUTO_INCREMENT, "
            . "EntryID VARCHAR(100), "
            . "GoodID VARCHAR(40), "
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
        $tp = "Unit";
        $msg = "Price" . $Number . " changed for one " . $tp . " from " . $initial . " to " . $present;
        $sqlA = "INSERT INTO " . $dbmonthyear3 . " (EntryID, GoodID, Itemname, Date, Description, Quantity, Staff, TrackingNo) "
            . "VALUES ('" . $EID . "','" . $itD . "','" . $Itmna . "','" . $date . "','" . $msg . "','" . $formattedquantity . "','" . $staff . "', " . $track . ")";

        if (mysqli_query($conn, $sqlA)) {
            $HtmlCode .= "Success Track";
        } else {
            $HtmlCode .= "Error Track";
        }
        return true;
    } else {
        return false;
    }
}

if (checkChange($IUP1, $unit1, "unit", $conn, $staff, $soldin, "", $Track, $formattedquantity, $itemname, $itemID, 1)) {
    $Track = $Track + 1;
    $PriceChanged = true;
}
if (checkChange($IUP2, $unit2, "unit", $conn, $staff, $soldin, "", $Track, $formattedquantity, $itemname, $itemID, 2)) {
    $Track = $Track + 1;
    $PriceChanged = true;
}
if (checkChange($IUP3, $unit3, "unit", $conn, $staff, $soldin, "", $Track, $formattedquantity, $itemname, $itemID, 3)) {
    $Track = $Track + 1;
    $PriceChanged = true;
}

$EID = "E" . date("his") . (rand(10000, 99999));
$trk = $PriceChanged ? $Track : $Track + 1;
$sql4 = "UPDATE allgoodsproperties SET  Unitsellingprice1 = " . $unit1 . ", Unitsellingprice2 = '" . $unit2 . "', Unitsellingprice3 = '" . $unit3 . "', TrackingNo= " . $trk . $NN . " WHERE GoodName = '" . $itemname . "'";

if (mysqli_query($conn, $sql4)) {

    if ($logreport !== "") {
        $HtmlCode .= "Success Update";
    }
    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $EID = "E" . date("his") . (rand(10000, 99999));
    $sqlA = "INSERT INTO logrecords (Date, EventID, Event) "
        . "VALUES ('" . $date . "','" . $EID . "','" . $logreport . "')";

    if (mysqli_query($conn, $sqlA)) {
        $HtmlCode .= "Success Log";
    } else {
        $HtmlCode = "Error";
    }

}

function getTheQuantity(&$conn, $itnm)
{

    $ULeft = 0;
    $CRate = 0;
    $TotalBulk = 0;
    $TheProperties = array();
    $sqlQ = "SELECT  Unitleft, TrackingNo, UnitSellingPrice1, UnitSellingPrice2, UnitSellingPrice3  FROM allgoodsproperties WHERE Goodname = '" . $itnm . "'";

    $resultQ = mysqli_query($conn, $sqlQ);
    if (pos_num_rows($resultQ) > 0) {
        while ($newArrayQ = pos_fetch_array($resultQ)) {

            $ULeft = floatval($newArrayQ[0]);
            $TNo = floatval($newArrayQ[1]);

            $IU1 = floatval($newArrayQ[2]);
            $IU2 = floatval($newArrayQ[3]);
            $IU3 = floatval($newArrayQ[4]);

        }

        $TotalBulk = $ULeft;

    }
    $TheProperties["Formatted"] = floatval($TotalBulk);
    $TheProperties["Unit"] = floatval($ULeft);
    $TheProperties["Track"] = $TNo;
    $TheProperties["IUP1"] = $IU1;
    $TheProperties["IUP2"] = $IU2;
    $TheProperties["IUP3"] = $IU3;
    $TheProperties["Track"] = $TNo;
    return $TheProperties;
}

echo ($HtmlCode);
