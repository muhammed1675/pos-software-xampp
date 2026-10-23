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

$DebtCollector = $SP['DebtCollector'];
$Customer = $SP['Customer'];
$Payable = $SP['Payable'];
$PaymentMode = $SP['PaymentMode'];
$RID = $SP['RID'];
$NowPaying = floatval($SP['nowpaying']);
$PaidBefore = $SP['Paid'];
$Balance = $SP['Balance'];
date_default_timezone_set('Africa/Lagos');
$DateRegistered = date("M d, Y h:i a");
$dbmonthyear1 = date('M') . "_" . date('Y') . "_salesrecords";
$dbmonthyear2 = date('M') . "_" . date('Y') . "_receiptdetails";
$DID = "D" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);

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
$PM = "'0','0','0',";
if ($PaymentMode === "Cash") {
    $PM = "'" . $NowPaying . "', '0', '0',";
}
if ($PaymentMode === "POS") {
    $PM = "'0','" . $NowPaying . "','0',";
}
if ($PaymentMode === "Transfer") {
    $PM = "'0', '0','" . $NowPaying . "',";
}

$sqlJ = "Insert into " . $dbmonthyear2 . " (ReceiptNumber, Payable, Discount, Paid, PaymentMode, Cash, POS, Transfer, DatePaid, Customer, Phone, Staff, Profit)"
    . " Values ('" . $DID . "','" . $Payable . "','0','" . $NowPaying . "','" . $PaymentMode . "'," . $PM . "'" . $DateRegistered . "','" . $Customer . "','','" . $DebtCollector . "','0')";
if (mysqli_query($conn, $sqlJ)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode .= "Error3";
}
$TotalNowPaid = floatval($PaidBefore) + floatval($NowPaying);

$sqlP = "UPDATE debtreceiptdetails SET Paid = '" . $TotalNowPaid . "' WHERE ReceiptNumber = '" . $RID . "'";
if (mysqli_query($conn, $sqlP)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode .= "Error4";
}

$SqlC = "INSERT INTO debtpaymentrecords (PaymentID, ReceiptNumber, AmountPaid, PaymentMode, StaffPaid, DatePaid) VALUES ('" . $DID . "','" . $RID . "','" . $NowPaying . "','" . $PaymentMode . "','" . $DebtCollector . "','" . $DateRegistered . "')";
if (mysqli_query($conn, $SqlC)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode .= "Error5";
}
echo ($HtmlCode);
