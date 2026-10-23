<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$itemtodelete = $_POST["dt"];
$rctid = $itemtodelete['RID'];
$RID = $itemtodelete['RID'];
$RProfit = $itemtodelete['RProfit'];
$RMode = $itemtodelete['RMode'];
$NewPayable = $itemtodelete['NewPayable'];
$NewCash = $itemtodelete['NewCash'];
$NewPos = $itemtodelete['NewPos'];
$NewTransfer = $itemtodelete['NewTransfer'];
$NewTender = $itemtodelete['NewTender'];
$NewChangeCollected = $itemtodelete['NewChangeCollected'];
$AllItemsToDelete = $itemtodelete['ItemsToDelete'];
$dbb = "";

foreach ($AllItemsToDelete as $item) {
    $itid = $item['itid'];
    $total = $item['total'];
    $pft = $item['pft'];
    $ddb = $item['ddb'];
    $rpble = $item['rpble'];
    $rpft = $item['rpft'];
    $cust = $item['rcust'];
    $ordername = $item['ordername'];
    $Username = $item['Username'];
    $nou = is_numeric($item['nou']) == 1 ? floatval($item['nou']) : 0;
    $rdiff = floatval($rpble) - floatval($total);
    $pftdiff = floatval($rpft) - floatval($pft);

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
            $AffectedTrackingNumber = floatval($newArray[0]);
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
            $TNo = floatval($newArray[1]);
            $Gid = $newArray[2];
        }
    }
    $TotalBulkBefore = getTheQuantity($conn, $Gid);
    $prevTotalUnit = $prevU;
    $difference = $nou;
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
    $mess = $Username . " deleted  " . $ordername . " that worth " . $total . " Naira on Receipt Number " . $rctid . " that earlier cost " . $rpble . ".";
    $sqlI = "Insert into logrecords (Date, EventID, Event) Values ( '" . $date . "','" . $EID . "','" . $mess . "')";
    if (mysqli_query($conn, $sqlI)) {
        $HtmlCode .= "Success3";
    } else {
        $HtmlCode = "Failure";
    }
}

if ($RMode === "Credit") {
    $sql1 = "UPDATE " . $ddb . "_receiptdetails SET Payable = '" . $NewPayable . "',  Profit = '" . $RProfit . "' WHERE ReceiptNumber = '" . $RID . "'";
} else {
    $sql1 = "UPDATE " . $ddb . "_receiptdetails SET Payable = '" . $NewPayable . "', Cash = '" . $NewCash . "', POS = '" . $NewPos . "', Transfer = '" . $NewTransfer . "', Paid = '" . $NewPayable . "', Tender = '" . $NewTender . "', ChangeCollected = '" . $NewChangeCollected . "', Profit = '" . $RProfit . "' WHERE ReceiptNumber = '" . $RID . "'";
}

$result1 = mysqli_query($conn, $sql1);
if ($result1) {
    $Htmlcode .= "Success success SUCCESS 1";
} else {
    $Htmlcode .= "Failure";
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

echo $Htmlcode;
