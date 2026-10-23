<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllStaffRecords = array();
$type = $datasent['type'];
$datessent = $datasent['dates'];
$Staff = $datasent['Staff'];
$AllAffectedStaff = array();
$AllAffectedTotal = array();
$TotalArray = array();
$NewArray = array();
$dbmonthyear2 = date('M') . "_" . date('Y') . "_receiptdetails";
$sqlR = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear2 . " (SN INT NOT NULL AUTO_INCREMENT, "
    . "ReceiptNumber VARCHAR(100), "
    . "Payable VARCHAR(100)  DEFAULT '0', "
    . "Discount VARCHAR(100)  DEFAULT '0', "
    . "Paid VARCHAR(1000) DEFAULT '0', "
    . "PaymentMode VARCHAR(100) DEFAULT '0', "
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

if ($type === "specific") {
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    $AllStaffRecords["ThisDate"] = $datessent;
    $AllStaffRecords["ThisStaffRecords"] = getthestaff($datessent, $conn, $ddb, $Staff);
} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;

        $AllStaffRecords[$i]["ThisDate"] = $datessent[$i];
        $AllStaffRecords[$i]["ThisStaffRecords"] = getthestaff($datessent[$i], $conn, $ddb, $Staff);

    }

    $ATCash = 0;
    $ATTransfer = 0;
    $ATCredit = 0;
    $ATPOS = 0;
    $ATTotal = 0;
    $ATGoodsNo = 0;
    $z = 0;

    $StaffCash = 0;
    $StaffPOS = 0;
    $StaffTransfer = 0;
    $StaffCredit = 0;
    $StaffGoodsNo = 0;
    $StaffTotalSales = 0;

    for ($w = 0; $w < count($AllStaffRecords); $w++) {

        if (count($AllStaffRecords[$w]["ThisStaffRecords"]) > 0) {
            $StaffCash += floatval($AllStaffRecords[$w]["ThisStaffRecords"]["Cash"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"]["Cash"]) : 0;
            $StaffPOS += floatval($AllStaffRecords[$w]["ThisStaffRecords"]["POS"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"]["POS"]) : 0;
            $StaffTransfer += floatval($AllStaffRecords[$w]["ThisStaffRecords"]["Transfer"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"]["Transfer"]) : 0;
            $StaffCredit += floatval($AllStaffRecords[$w]["ThisStaffRecords"]["Credit"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"]["Credit"]) : 0;
            $StaffGoodsNo += floatval($AllStaffRecords[$w]["ThisStaffRecords"]["TransactionNo"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"]["TransactionNo"]) : 0;
            $StaffTotalSales += floatval($AllStaffRecords[$w]["ThisStaffRecords"]["TotalSales"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"]["TotalSales"]) : 0;
        }
    }
    $AllAffectedStaffTotal["StaffName"] = $Staff;
    $AllAffectedStaffTotal["Cash"] = $StaffCash;

    $AllAffectedStaffTotal["POS"] = $StaffPOS;

    $AllAffectedStaffTotal["Transfer"] = $StaffTransfer;

    $AllAffectedStaffTotal["Credit"] = $StaffCredit;

    $AllAffectedStaffTotal["TransactionNo"] = $StaffGoodsNo;

    $AllAffectedStaffTotal["TotalSales"] = $StaffTotalSales;

    $NewArray["ThisDate"] = $datessent[0] . " - " . $datessent[count($datessent) - 1];
    $NewArray["ThisStaffRecords"] = $AllAffectedStaffTotal;
    array_unshift($AllStaffRecords, $NewArray);

}

function getthestaff($dat, $conn, $dby, &$Staff)
{

    $AllCash = 0;
    $AllPOS = 0;
    $AllTransfer = 0;
    $AllCredit = 0;
    $AllGoods = 0;
    $StaffRecord = array();

    $Cash = 0;
    $POS = 0;
    $Credit = 0;
    $Transfer = 0;

    $sqlCheck = "SHOW TABLES LIKE '" . $dby . "_receiptdetails'";

    $resultM = mysqli_query($conn, $sqlCheck);
    if ($resultM->num_rows > 0) {

        $sqlA = "SELECT ReceiptNumber, Payable, Discount, Paid, Cash, POS, Transfer, Tender, ChangeCollected, DatePaid, Customer, Phone, Staff, Profit, PaymentMode, Payable FROM " . $dby . "_receiptdetails WHERE DatePaid LIKE '" . $dat . "%' AND Staff = '" . $Staff . "'";
        $NoOfGoods = 0;
        $resultA = mysqli_query($conn, $sqlA) or die(mysqli_error($conn));
        if (pos_num_rows($resultA) > 0) {
            while ($newArrayA = pos_fetch_array($resultA)) {
                $Cash += floatval($newArrayA[4]);

                $POS += floatval($newArrayA[5]);

                $Transfer += floatval($newArrayA[6]);

                $Credit += $newArrayA[14] === "Credit" ? $Credit + (floatval($newArrayA[15])) : 0;

                $sqlC = "SELECT Count(GoodName) FROM " . $dby . "_salesrecords WHERE
                                                ReceiptNumber = '" . $newArrayA[0] . "'";

                $resultC = mysqli_query($conn, $sqlC) or die(mysqli_error($conn));
                if (pos_num_rows($resultC) > 0) {
                    while ($newArrayC = pos_fetch_array($resultC)) {
                        $NoOfGoods += floatval($newArrayC[0]);
                    }
                }

            }
            $StaffRecord["StaffName"] = $Staff;
            $StaffRecord["Cash"] = $Cash;
            $StaffRecord["POS"] = $POS;
            $StaffRecord["Transfer"] = $Transfer;
            $StaffRecord["Credit"] = $Credit;
            $StaffRecord["TransactionNo"] = $NoOfGoods;
            $StaffRecord["TotalSales"] = $Cash + $POS + $Transfer + $Credit;
        }

        return $StaffRecord;
    } else {
        return array();
    }

}

echo (json_encode($AllStaffRecords));
// echo ($sqlS);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
