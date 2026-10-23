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

$Username = $SP['Username'];
$BusinessName = $SP['BusinessName'];
$Address = $SP['Address'];
$PhoneNumber = $SP['PhoneNumber'];
date_default_timezone_set('Africa/Lagos');
$DateRegistered = date("M d, Y h:i a");
$CID = "CST_" . date("Ymd") . date("his") . rand(10, 99) . rand(10, 99) . rand(100, 999);

$sql2 = "INSERT INTO customerrecords (CustomerId, BusinessName, Address, PhoneNumber, DateRegistered) "
    . "VALUES ('" . $CID . "','" . $BusinessName . "','" . $Address . "','" . $PhoneNumber . "','" . $DateRegistered . "')";

if (mysqli_query($conn, $sql2)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode = "Error";
}

echo ($HtmlCode);
