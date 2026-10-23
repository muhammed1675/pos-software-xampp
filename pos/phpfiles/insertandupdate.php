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
$SupplyRecords = $_POST['dt'];
$SupRecArray = $SupplyRecords["suprecords"];
$supplier = $SupplyRecords["Supplier"];
$invoice = $SupplyRecords["Invoice"];
$staff = $SupplyRecords["Staff"];

$SID = "S" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(10, 99);

function checkChange($initial, $present, $type, $conn, $staff, $soldin, $b, $track, $formattedquantity, $Itmna, $ItemID, $Number)
{

    if ($initial != $present) {
        $dbmonthyear3 = date('M') . "_" . date('Y') . "_price_change_track";
        $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear3 . " (SN INT NOT NULL AUTO_INCREMENT, "
            . "EntryID VARCHAR(100), "
            . "GoodID VARCHAR(100), "
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
        $tp = $type == "bulk" ? $soldin : "Unit";
        $msg = "Price" . $Number . " changed for one " . $tp . " from " . $initial . " to " . $present;
        $sqlA = "INSERT INTO " . $dbmonthyear3 . " (EntryID, GoodID,  Itemname, Date, Description, Quantity, Staff, TrackingNo) "
            . "VALUES ('" . $EID . "','" . $ItemID . "','" . $Itmna . "','" . $date . "','" . $msg . "'," . $formattedquantity . ",'" . $staff . "'," . $track . ")";

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

for ($h = 0; $h < count($SupRecArray); $h++) {

    $SP = $SupRecArray[$h];
    $itemname = ucfirst($SP['myItemname']);
    $converter = $SP['myConverter'];
    $itemgid = $SP['myGID'];
    $quanunit = floatval($SP['myQUnit']);
    $costprice = $SP['myCostPrice'];
    $totalcostprice = $SP['myTotalCostPrice'];
    $soldin = $SP['mySoldIn'];
    $unit1 = $SP['myUP1'];
    $unit2 = $SP['myUP2'];
    $unit3 = $SP['myUP3'];
    $PriceChanged = false;
    $ExpiryDate = $SP['EDate'];
    $ExpMinDays = $SP['MinExpDay'];
    $MinQuanity = $SP['MinQuan'];
    $logreport = $SP['logreport'];
    $isNew = $SP['isNew'];
    $TrackingNo = 0;
    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $dbmonthyear1 = date('M') . "_" . date('Y') . "_allgoodssupplied";

    $EID = "E" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);

    $TotalBulkBefore = $quanunit > 0 ? getTheQuantity($conn, $itemgid) : 0;

    $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear1 . " (SN INT NOT NULL AUTO_INCREMENT, "
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

    $sql2 = "INSERT INTO " . $dbmonthyear1 . "(EntryID, GoodEntryID, GoodID, Goodname, QuantityUnit, Unitcostprice, Totalcostprice, DateReceived, Receiver, Supplier, InvoiceNo, TrackingNo) "
        . "VALUES ('" . $SID . "','" . $EID . "','" . $itemgid . "','" . $itemname . "'," . $quanunit . ",'" . $costprice . "','" . $totalcostprice . "','" . $date . "','" . $staff . "','" . $supplier . "','" . $invoice . "'," . $TrackingNo . ")";

    if (mysqli_query($conn, $sql2)) {
        $HtmlCode .= "Success";
    } else {
        $HtmlCode .= "Error";
    }
    if (($quanunit > 0) && $ExpiryDate != "") {
        $sqlA = "INSERT INTO expirydates (GoodsID, Supplieddate,  Expirydate) "
            . "VALUES ('" . $itemgid . "','" . $date . "','" . $ExpiryDate . "')";

        if (mysqli_query($conn, $sqlA)) {
            $HtmlCode .= "Success";
        } else {
            $HtmlCode .= "Error";
        }
    }
    if (!$isNew) {
        $IBP1 = 0;
        $IBP2 = 0;
        $IBP3 = 0;
        $IUP1 = 0;
        $IUP2 = 0;
        $IUP3 = 0;
        $remU = 0;

        $sql3 = "SELECT  UnitLeft, TrackingNo, UnitSellingPrice1, UnitSellingPrice2, UnitSellingPrice3 From allgoodsproperties Where GoodID = '" . $itemgid . "'";
        $result = mysqli_query($conn, $sql3) or die(mysqli_error($conn));
        if (pos_num_rows($result) > 0) {
            while ($newArray = pos_fetch_array($result)) {

                $remU = floatval($newArray[0]);
                $TrackingNo = floatval($newArray[1]);
                $IUP1 = intval($newArray[2]);
                $IUP2 = intval($newArray[3]);
                $IUP3 = intval($newArray[4]);
            }
        }

        $formattedquantity = getTheQuantity($conn, $itemgid);

        if (checkChange($IUP1, $unit1, "unit", $conn, $staff, $soldin, "", $TrackingNo, $formattedquantity, $itemname, $itemgid, 1)) {
            $TrackingNo = floatval($TrackingNo + 1);
            $PriceChanged = true;
        }
        if (checkChange($IUP2, $unit2, "unit", $conn, $staff, $soldin, "", $TrackingNo, $formattedquantity, $itemname, $itemgid, 2)) {
            $TrackingNo = floatval($TrackingNo + 1);
            $PriceChanged = true;
        }
        if (checkChange($IUP3, $unit3, "unit", $conn, $staff, $soldin, "", $TrackingNo, $formattedquantity, $itemname, $itemgid, 3)) {
            $TrackingNo = floatval($TrackingNo + 1);
            $PriceChanged = true;
        }
        $trk = $PriceChanged ? $TrackingNo + 1 : $TrackingNo + 2;
        $sql4 = "UPDATE allgoodsproperties SET Currentcostprice = '" . $costprice . "', Soldin = '" . $soldin . "',  Unitsellingprice1 = '" . $unit1 . "', Unitsellingprice2 = '" . $unit2 . "', Unitsellingprice3 = '" . $unit3 . "', Unitleft = " . (floatval($quanunit) + floatval($remU)) . " ,Converterrate = '" . $converter . "', TrackingNo = " . ($trk) . ", minimumexpirydays = '" . $ExpMinDays . "', minimumquantity = '" . $MinQuanity . "' WHERE GoodID = '" . $itemgid . "'";

        if (mysqli_query($conn, $sql4)) {

            if ($logreport !== "") {
                $HtmlCode .= "Success Update";
            }
            date_default_timezone_set('Africa/Lagos');
            $date = date("M d, Y h:i a");
            $LID = "E" . date("his") . (rand(10000, 99999));
            $sqlA = "INSERT INTO logrecords (Date, EventID, Event) "
                . "VALUES ('" . $date . "','" . $LID . "','" . $logreport . "')";

            if (mysqli_query($conn, $sqlA)) {
                $HtmlCode .= "Success Log";
            } else {
                $HtmlCode .= "Error";
            }

        }
    } else // Insert New Item
    {

        $sqlB = "INSERT INTO allgoodsproperties (GoodID, Goodname, CurrentCostPrice, Soldin,  UnitLeft, ConverterRate, Unitsellingprice1, Unitsellingprice2, Unitsellingprice3, TrackingNo, minimumexpirydays, minimumquantity) "
            . "VALUES ('" . $itemgid . "','" . $itemname . "','" . $costprice . "','" . $soldin . "', " . $quanunit . ",'" . $converter . "','" . $unit1 . "','" . $unit2 . "','" . $unit3 . "','1','" . $ExpMinDays . "','" . $MinQuanity . "')";

        if (mysqli_query($conn, $sqlB)) {
            $HtmlCode .= "Success";
        } else {
            $HtmlCode .= "ErrorB" . $h;
        }
    }

    $TotalBulkAfter = $quanunit > 0 ? getTheQuantity($conn, $itemgid) : 0;
    $TrackingNo = $isNew ? 0 : $TrackingNo;
    $sqlJ = "UPDATE " . $dbmonthyear1 . " SET BulkBalanceBefore = " . $TotalBulkBefore . ", BulkBalanceAfter = " . $TotalBulkAfter . ", TrackingNo = " . $TrackingNo . " WHERE GoodEntryID = '" . $EID . "'";

    if (mysqli_query($conn, $sqlJ)) {
        $HtmlCode .= "Success Balance" . $itemgid;

    } else {
        $HtmlCode .= "Error Bal";
    }
}

function getTheQuantity(&$conn, $itgid)
{

    $ULeft = 0;
    $CRate = 0;
    $TotalBulk = 0;
    $sqlQ = "SELECT  Unitleft, ConverterRate FROM allgoodsproperties WHERE GoodID = '" . $itgid . "'";

    $resultQ = mysqli_query($conn, $sqlQ);
    if (pos_num_rows($resultQ) > 0) {
        while ($newArrayQ = pos_fetch_array($resultQ)) {

            $ULeft = floatval($newArrayQ[0]);
            $CRate = floatval($newArrayQ[1]);
        }
        $TotalBulk = $ULeft;

    }
    return $TotalBulk;
}
echo ($HtmlCode);
