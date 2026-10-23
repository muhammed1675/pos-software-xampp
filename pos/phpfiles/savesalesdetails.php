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
$OrderDetails = $_POST['dt'];
$OrdersBought = $OrderDetails['OrderData'];
$rcd = $OrderDetails['rcn'];
$rcv = $OrderDetails['Staff'];
$cnm = $OrderDetails['cn'];
$cpn = $OrderDetails['pn'];
$discountgiven = $OrderDetails['Discount'];
$amountpayable = $OrderDetails['Payable'];
$amountpaid = $OrderDetails['PayableWithDiscount'];
$paymentmode = $OrderDetails['PaymentMode'];
$Cash = $OrderDetails['Cash'];
$POS = $OrderDetails['POS'];
$Transfer = $OrderDetails['Transfer'];
$Tender = $OrderDetails['Tender'];
$ChangeCollected = $OrderDetails['ChangeCollected'];
$dbmonthyear1 = date('M') . "_" . date('Y') . "_salesrecords";
$dbmonthyear2 = date('M') . "_" . date('Y') . "_receiptdetails";
$TotalProfit = 0;
$CP = 0;
$UL = 0;

$dbmonthyear2 = date('M') . "_" . date('Y') . "_receiptdetails";

$sqlR = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear2 . " (SN INT NOT NULL AUTO_INCREMENT, "
    . "ReceiptNumber VARCHAR(100), "
    . "Payable VARCHAR(100)  DEFAULT '0', "
    . "Discount VARCHAR(100)  DEFAULT '0', "
    . "Paid VARCHAR(1000) DEFAULT '0', "
    . "PaymentMode VARCHAR(100) DEFAULT '0', "
    . "Cash VARCHAR(100) DEFAULT '0', "
    . "POS VARCHAR(100) DEFAULT '0', "
    . "Transfer VARCHAR(100) DEFAULT '0', "
    . "Tender VARCHAR(100) DEFAULT '0', "
    . "ChangeCollected VARCHAR(100) DEFAULT '0', "
    . "DatePaid VARCHAR(100) DEFAULT '0', "
    . "Customer VARCHAR(100) NULL, "
    . "Phone VARCHAR(100) NULL, "
    . "Staff VARCHAR(100) NULL, "
    . "Profit VARCHAR(100) DEFAULT '0' ,"
    . "PRIMARY KEY (SN)"
    . ")";

if (mysqli_query($conn, $sqlR) === true) {
    $HtmlCode = "New table created successfully";
}

for ($y = 0; $y < count($OrdersBought); $y++) {
    $itemname = $OrdersBought[$y]['ordername'];
    $gid = $OrdersBought[$y]['itemid'];
    $nou = floatval($OrdersBought[$y]['nou']);
    $amt = $OrdersBought[$y]['subtotal'];
    $SID = "S" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);
    date_default_timezone_set('Africa/Lagos');
    $date = date("M d, Y h:i a");
    $itemgid = $OrdersBought[$y]['itemid'];
    $desc = $OrdersBought[$y]['rdesc'];

    $uprice = $OrdersBought[$y]['unitprice'];
    $TrackingNo = 0;

    $TotalBulkBefore = $nou > 0 ? getTheQuantity($conn, $gid) : 0;

    $sql = "Select  CurrentCostprice, Unitleft,  TrackingNo from allgoodsproperties WHERE GoodID = '" . $gid . "'";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    if (pos_num_rows($result) > 0) {
        while ($newArray = pos_fetch_array($result)) {

            $CP = floatval($newArray[0]);
            $UL = floatval($newArray[1]);
            $TrackingNo = floatval($newArray[2]);
        }
    }

    $UCP = $CP;
    $grandcostprice = $UCP * $nou;
    $Profit = $gid == "12345" ? 0 : (floatval($amt) - $grandcostprice);
    $TotalProfit += $Profit;

    $newUnit = floatval($UL - $nou);

    $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear1 . " (SN INT NOT NULL AUTO_INCREMENT, "
        . "ReceiptNumber VARCHAR(100), "
        . "SalesID VARCHAR(100) NOT NULL, "
        . "GoodsID VARCHAR(100), "
        . "GoodName VARCHAR(1000) DEFAULT '0', "
        . "NoU DOUBLE NULL, "
        . "Amount VARCHAR(50) DEFAULT '0', "
        . "Profit VARCHAR(50) DEFAULT '0', "
        . "Descriptions VARCHAR(100) DEFAULT '0', "
        . "UnitPrice VARCHAR(50) DEFAULT 'NA' ,"
        . "BulkBalanceBefore DOUBLE NULL, "
        . "BulkBalanceAfter DOUBLE NULL, "
        . "DateSold VARCHAR(50) NULL, "
        . "Staff VARCHAR(50) NULL, "
        . "Buyer VARCHAR(50) NULL, "
        . "TrackingNo INT(10) NULL, "
        . "Status VARCHAR(10) DEFAULT 'Active', "
        . "PRIMARY KEY (SN, SalesID)"
        . ")";

    if (mysqli_query($conn, $sql1) === true) {
        $HtmlCode = "New table created successfully";
    }

    $sql3 = "Insert into " . $dbmonthyear1 . " (ReceiptNumber, SalesID, GoodsID, Goodname,  NoU,  Amount, Profit, Descriptions,  UnitPrice, DateSold, Staff, Buyer,   TrackingNo)"
        . " Values ('" . $rcd . "','" . $SID . "','" . $gid . "','" . $itemname . "'," . $nou . ",'" . $amt . "','" . $Profit . "','" . $desc . "','" . $uprice . "','" . $date . "','" . $rcv . "','" . $cnm . "'," . $TrackingNo . ")";
    if (mysqli_query($conn, $sql3)) {
        $HtmlCode .= "Success";
    } else {
        $HtmlCode = "Error1";
    }

    $sql = "UPDATE allgoodsproperties SET  UnitLeft = " . $newUnit . ", TrackingNo = " . (floatval($TrackingNo + 1)) . "  WHERE GoodID = '" . $gid . "'";
    if (mysqli_query($conn, $sql)) {
        $HtmlCode .= "Success";
    } else {
        $HtmlCode = "Error2";
    }

    $TotalBulkAfter = $nou > 0 ? getTheQuantity($conn, $gid) : 0;

    $sqlJ = "UPDATE " . $dbmonthyear1 . " SET BulkBalanceBefore = " . $TotalBulkBefore . ", BulkBalanceAfter = " . $TotalBulkAfter . ", TrackingNo = " . $TrackingNo . " WHERE SalesID = '" . $SID . "'";

    if (mysqli_query($conn, $sqlJ)) {
        $HtmlCode .= "Success Balance" . $itemgid;

    } else {
        $HtmlCode .= "Error Bal";
    }

}

$sqlR = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear2 . " (SN INT NOT NULL AUTO_INCREMENT, "
    . "ReceiptNumber VARCHAR(100), "
    . "Payable VARCHAR(100)  DEFAULT '0', "
    . "Discount VARCHAR(100)  DEFAULT '0', "
    . "Paid VARCHAR(1000) DEFAULT '0', "
    . "PaymentMode VARCHAR(100) DEFAULT '0', "
    . "Cash VARCHAR(100) DEFAULT '0', "
    . "POS VARCHAR(100) DEFAULT '0', "
    . "Transfer VARCHAR(100) DEFAULT '0', "
    . "Tender VARCHAR(100) DEFAULT '0', "
    . "ChangeCollected VARCHAR(100) DEFAULT '0', "
    . "DatePaid VARCHAR(100) DEFAULT '0', "
    . "Customer VARCHAR(100) NULL, "
    . "Phone VARCHAR(100) NULL, "
    . "Staff VARCHAR(100) NULL, "
    . "Profit VARCHAR(100) DEFAULT '0' ,"
    . "PRIMARY KEY (SN)"
    . ")";

if (mysqli_query($conn, $sqlR) === true) {
    $HtmlCode = "New table created successfully";
}
$amountpaid = $paymentmode == "Credit" ? 0 : $amountpaid;
$sqlJ = "Insert into " . $dbmonthyear2 . " (ReceiptNumber, Payable, Discount, Paid, PaymentMode, Cash, POS, Transfer, Tender, ChangeCollected, DatePaid, Customer, Phone, Staff, Profit)"
    . " Values ('" . $rcd . "','" . $amountpayable . "','" . $discountgiven . "','" . $amountpaid . "','" . $paymentmode . "','" . $Cash . "','" . $POS . "','" . $Transfer . "','" . $Tender . "','" . $ChangeCollected . "','" . $date . "','" . $cnm . "','" . $cpn . "','" . $rcv . "','" . $TotalProfit . "')";
if (mysqli_query($conn, $sqlJ)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode = "Error3";
}

if ($paymentmode == "Credit") {
    $sqlK = "Insert into debtreceiptdetails (ReceiptNumber, Payable, Discount, Paid, PaymentMode,  DatePaid, Customer, Phone, Staff, Profit, LastPaymentDate)"
        . " Values ('" . $rcd . "','" . $amountpayable . "','" . $discountgiven . "','" . $amountpaid . "','" . $paymentmode . "','" . $date . "','" . $cnm . "','" . $cpn . "','" . $rcv . "','" . $TotalProfit . "','" . $date . "')";
    if (mysqli_query($conn, $sqlK)) {
        $HtmlCode .= "Success";
    } else {
        $HtmlCode = "Error4";
    }
}

function getTheQuantity(&$conn, $itgid)
{

    $ULeft = 0;
    $CRate = 0;
    $TotalBulk = 0;
    $sqlQ = "SELECT  Unitleft FROM allgoodsproperties WHERE GoodID = '" . $itgid . "'";

    $resultQ = mysqli_query($conn, $sqlQ);
    if (pos_num_rows($resultQ) > 0) {
        while ($newArrayQ = pos_fetch_array($resultQ)) {

            $ULeft = floatval($newArrayQ[0]);
        }
        $TotalBulk = $ULeft;

    }
    return $TotalBulk;
}

echo ($HtmlCode);
