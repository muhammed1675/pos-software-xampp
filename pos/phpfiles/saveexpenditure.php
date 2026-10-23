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
$EXP = $_POST['dt'];

$Username = $EXP['Username'];
$amount = $EXP['amount'];
$getter = $EXP['getter'];
$reason = $EXP['reason'];
$account = $EXP['account'];
date_default_timezone_set('Africa/Lagos');
$DateRegistered = date("M d, Y h:i a");
$dbmonthyear1 = date('M') . "_" . date('Y') . "_expenditures";
$EID = "E" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);

$sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear1 . " (SN INT NOT NULL AUTO_INCREMENT, "
    . "EntryID VARCHAR(100), "
    . "Giver VARCHAR(100) NULL, "
    . "Amount VARCHAR(100) NULL, "
    . "Receiver VARCHAR(100), "
    . "Description VARCHAR(1000) DEFAULT '0', "
    . "Account VARCHAR(100) DEFAULT '0', "
    . "DateRegistered VARCHAR(50), "
    . "PRIMARY KEY (SN)"
    . ")";
if (mysqli_query($conn, $sql1) === true) {
    $HtmlCode .= "New table created successfully";
}

$sql2 = "INSERT INTO " . $dbmonthyear1 . " (EntryID, Giver, Amount, Receiver, Description, Account, DateRegistered) "
    . "VALUES ('" . $EID . "','" . $Username . "','" . $amount . "','" . $getter . "','" . $reason . "','" . $account . "','" . $DateRegistered . "')";

if (mysqli_query($conn, $sql2)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode = "Error";
}

echo ($HtmlCode);
