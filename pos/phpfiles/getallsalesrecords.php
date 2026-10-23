<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllSalesRecords = array();
$type = $datasent['type'];
$datessent = $datasent['dates'];

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

if ($type === "specific") {
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    // $AllMyRecords =  getMydata($datessent, $conn, $ddb);
    $AllSalesRecords["ThisDate"] = $datessent;
    $AllSalesRecords["ThisSalesRecords"] = getMydata($datessent, $conn, $ddb);
} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;
        // $AllMyRecords = array_merge($AllMyRecords,getMydata($datessent[$i], $conn, $ddb));
        $AllSalesRecords[$i]["ThisDate"] = $datessent[$i];
        $AllSalesRecords[$i]["ThisSalesRecords"] = getMydata($datessent[$i], $conn, $ddb);
    }
}

function getMydata($dat, $conn, $dby)
{
    $AllPayable = 0;
    $AllPaid = 0;
    $AllCash = 0;
    $AllPOS = 0;
    $AllTransfer = 0;
    $AllCredit = 0;
    $AllTender = 0;
    $AllChangeCollected = 0;
    $AllProfit = 0;
    $AllDiscount = 0;
    $allgoods = array();
    $n = 0;
    $sql = "SELECT ReceiptNumber, Payable, Discount, Paid, Cash, POS, Transfer, Tender, ChangeCollected, DatePaid, Customer, Phone, Staff, Profit, PaymentMode, Payable FROM " . $dby . "_receiptdetails WHERE DatePaid LIKE '" . $dat . "%'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        if (pos_num_rows($result) > 0) {
            while ($newArray = pos_fetch_array($result)) {
                $allgoods[$n]["RID"] = $newArray[0];
                $allgoods[$n]["Payable"] = strpos($newArray[0], "D") !== false ? "Debt" : floatval($newArray[1]);
                $AllPayable += strpos($newArray[0], "D") !== false ? 0 : floatval($newArray[1]);
                $allgoods[$n]["Discount"] = floatval($newArray[2]);
                $AllDiscount += floatval($newArray[2]);
                $allgoods[$n]["Paid"] = floatval($newArray[3]);
                $AllPaid += floatval($newArray[3]);
                $allgoods[$n]["Cash"] = floatval($newArray[4]);
                $AllCash += floatval($newArray[4]);
                $allgoods[$n]["POS"] = floatval($newArray[5]);
                $AllPOS += floatval($newArray[5]);
                $allgoods[$n]["Transfer"] = floatval($newArray[6]);
                $AllTransfer += floatval($newArray[6]);
                $allgoods[$n]["Credit"] = $newArray[14] === "Credit" ? floatval($newArray[15]) : 0;
                $AllCredit += $newArray[14] === "Credit" ? floatval($newArray[15]) : 0;
                $allgoods[$n]["Tender"] = floatval($newArray[7]);
                $AllTender += floatval($newArray[7]);
                $allgoods[$n]["ChangeCollected"] = floatval($newArray[8]);
                $AllChangeCollected += floatval($newArray[8]);
                $allgoods[$n]["DatePaid"] = $newArray[9];
                $allgoods[$n]["Customer"] = $newArray[10];
                $allgoods[$n]["Phone"] = $newArray[11];
                $allgoods[$n]["Staff"] = $newArray[12];
                $allgoods[$n]["Profit"] = floatval($newArray[13]);
                $AllProfit += floatval($newArray[13]);

                $n++;
            }
            $allgoods[$n]["RID"] = "TOTAL";
            $allgoods[$n]["Payable"] = $AllPayable;
            $allgoods[$n]["Discount"] = $AllDiscount;
            $allgoods[$n]["Paid"] = $AllPaid;
            $allgoods[$n]["Cash"] = $AllCash;
            $allgoods[$n]["POS"] = $AllPOS;
            $allgoods[$n]["Transfer"] = $AllTransfer;
            $allgoods[$n]["Credit"] = $AllCredit;
            $allgoods[$n]["Tender"] = $AllTender;
            $allgoods[$n]["ChangeCollected"] = $AllChangeCollected;
            $allgoods[$n]["DatePaid"] = "-";
            $allgoods[$n]["Customer"] = "-";
            $allgoods[$n]["Phone"] = "-";
            $allgoods[$n]["Staff"] = "-";
            $allgoods[$n]["Profit"] = $AllProfit;

        }
    }

    return $allgoods;
}
echo (json_encode($AllSalesRecords));
// echo ($sql);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
