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
    $AllStaffRecords["ThisStaffRecords"] = getthestaff($datessent, $conn, $ddb, $AllAffectedStaff);
} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;

        $AllStaffRecords[$i]["ThisDate"] = $datessent[$i];
        $AllStaffRecords[$i]["ThisStaffRecords"] = getthestaff($datessent[$i], $conn, $ddb, $AllAffectedStaff);

    }

    $ATCash = 0;
    $ATTransfer = 0;
    $ATCredit = 0;
    $ATPOS = 0;
    $ATTotal = 0;
    $ATGoodsNo = 0;
    $ATTransactionsNo = 0;
    $z = 0;
    for ($g = 0; $g < count($AllAffectedStaff); $g++) {
        $StaffCash = 0;
        $StaffPOS = 0;
        $StaffTransfer = 0;
        $StaffCredit = 0;
        $StaffGoodsNo = 0;
        $StaffTotalSales = 0;
        $StaffTransactionNo = 0;

        for ($w = 0; $w < count($AllStaffRecords); $w++) {

            for ($c = 0; $c < count($AllStaffRecords[$w]["ThisStaffRecords"]); $c++) {
                if ($AllStaffRecords[$w]["ThisStaffRecords"][$c]["StaffName"] === $AllAffectedStaff[$g]) {
                    $StaffCash += floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["Cash"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["Cash"]) : 0;
                    $StaffPOS += floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["POS"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["POS"]) : 0;
                    $StaffTransfer += floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["Transfer"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["Transfer"]) : 0;
                    $StaffCredit += floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["Credit"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["Credit"]) : 0;
                    $StaffTransactionNo += floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["TransactionNo"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["TransactionNo"]) : 0;
                    $StaffTotalSales += floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["TotalSales"]) > 0 ? floatval($AllStaffRecords[$w]["ThisStaffRecords"][$c]["TotalSales"]) : 0;

                }
            }

        }
        $AllAffectedStaffTotal[$g]["StaffName"] = $AllAffectedStaff[$g];
        $AllAffectedStaffTotal[$g]["Cash"] = $StaffCash;
        $ATCash += $StaffCash;
        $AllAffectedStaffTotal[$g]["POS"] = $StaffPOS;
        $ATPOS += $StaffPOS;
        $AllAffectedStaffTotal[$g]["Transfer"] = $StaffTransfer;
        $ATTransfer += $StaffTransfer;
        $AllAffectedStaffTotal[$g]["Credit"] = $StaffCredit;
        $ATCredit = $StaffCredit;
        $AllAffectedStaffTotal[$g]["TransactionNo"] = $StaffTransactionNo;
        $ATTransactionsNo += $StaffTransactionNo;
        $AllAffectedStaffTotal[$g]["TotalSales"] = $StaffTotalSales;
        $ATTotal += $StaffTotalSales;

        array_push($TotalArray, $AllAffectedStaffTotal[$g]);

    }
    $z = count($TotalArray);
    $TotalArray[$z]["StaffName"] = "TOTAL";
    $TotalArray[$z]["Cash"] = $ATCash;
    $TotalArray[$z]["POS"] = $ATPOS;
    $TotalArray[$z]["Transfer"] = $ATTransfer;
    $TotalArray[$z]["Credit"] = $ATCredit;
    $TotalArray[$z]["TransactionNo"] = $ATTransactionsNo;
    $TotalArray[$z]["TotalSales"] = $ATTotal;
    $NewArray["ThisDate"] = $datessent[0] . " - " . $datessent[count($datessent) - 1];
    $NewArray["ThisStaffRecords"] = $TotalArray;
    array_unshift($AllStaffRecords, $NewArray);

}

function getthestaff($dat, $conn, $dby, &$AllAffectedStaff)
{

    $AllCash = 0;
    $AllPOS = 0;
    $AllTransfer = 0;
    $AllCredit = 0;
    $AllTransactions = 0;
    $StaffRecord = array();

    $sqlCheck = "SHOW TABLES LIKE '" . $dby . "_receiptdetails'";
    $resultM = mysqli_query($conn, $sqlCheck);
    if ($resultM->num_rows > 0) {
        $sqlS = "SELECT DISTINCT Staff FROM " . $dby . "_receiptdetails WHERE DatePaid LIKE '" . $dat . "%'";

        $result = mysqli_query($conn, $sqlS);
        if ($result) {
            if (pos_num_rows($result) > 0) {

                while ($newArray = pos_fetch_array($result)) {
                    if (!in_array($newArray[0], $AllAffectedStaff)) {
                        array_push($AllAffectedStaff, $newArray[0]);
                    }
                }
            }
        }
        if (count($AllAffectedStaff) > 0) {
            $n = 0;
            foreach ($AllAffectedStaff as $AFS) {
                $Cash = 0;
                $POS = 0;
                $Credit = 0;
                $Transfer = 0;

                $sqlA = "SELECT Staff, PaymentMode, Cash, POS, Transfer, Paid, ReceiptNumber, Payable FROM " . $dby . "_receiptdetails WHERE DatePaid LIKE '" . $dat . "%' AND Staff = '" . $AFS . "'";
                $NoOfTransaction = 0;
                $StaffRecord[$n]["StaffName"] = $AFS;
                $resultA = mysqli_query($conn, $sqlA) or die(mysqli_error($conn));
                if (pos_num_rows($resultA) > 0) {
                    while ($newArrayA = pos_fetch_array($resultA)) {
                        $Cash = $Cash + floatval($newArrayA[2]);
                        $POS = $POS + floatval($newArrayA[3]);
                        $Transfer = $Transfer + floatval($newArrayA[4]);

                        $Credit = $newArrayA[1] === "Credit" ? $Credit + (floatval($newArrayA[7])) : $Credit;

                        $NoOfTransaction++;

                    }
                }
                $StaffRecord[$n]["Cash"] = $Cash;
                $StaffRecord[$n]["POS"] = $POS;
                $StaffRecord[$n]["Transfer"] = $Transfer;
                $StaffRecord[$n]["Credit"] = $Credit;
                $StaffRecord[$n]["TransactionNo"] = $NoOfTransaction;
                $StaffRecord[$n]["TotalSales"] = $Cash + $POS + $Transfer + $Credit;

                $AllCash += $Cash;
                $AllPOS += $POS;
                $AllTransfer += $Transfer;
                $AllTransactions += $NoOfTransaction;
                $AllCredit += $Credit;
                $n++;

            }

            $StaffRecord[$n]["StaffName"] = "TOTAL";
            $StaffRecord[$n]["Cash"] = $AllCash;
            $StaffRecord[$n]["POS"] = $AllPOS;
            $StaffRecord[$n]["Transfer"] = $AllTransfer;
            $StaffRecord[$n]["Credit"] = $AllCredit;
            $StaffRecord[$n]["TransactionNo"] = $AllTransactions;
            $StaffRecord[$n]["TotalSales"] = $AllCash + $AllPOS + $AllTransfer + $AllCredit;
            return $StaffRecord;
        } else {
            return array();
        }
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
