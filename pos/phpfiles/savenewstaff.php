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
$Surname = $SP['Surname'];
$Firstname = $SP['Firstname'];
$Middlename = $SP['Middlename'];
$Gateway = $SP['Gateway'];
$Address = $SP['Address'];
$PhoneNumber = $SP['PhoneNumber'];
$Email = $SP['Email'];
$Category = $SP['Category'];
date_default_timezone_set('Africa/Lagos');
$DateRegistered = date("M d, Y h:i a");

$sql2 = "INSERT INTO staffrecord (Username, Surname, Firstname, Middlename, Gateway, Address, PhoneNumber, Email, Category, DateRegistered, LastLogin) "
. "VALUES ('" . $Username . "','" . $Surname . "','" . $Firstname . "','" . $Middlename . "','" . encrypt($Gateway) . "','" . $Address . "','" . $PhoneNumber . "','" . $Email . "','" . $Category . "','" . $DateRegistered . "','" . $DateRegistered . "')";

if (mysqli_query($conn, $sql2)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode = "Error";
}
$sql3 = "INSERT INTO permission (ID) "
    . "VALUES ('" . $Username . "')";

if (mysqli_query($conn, $sql3)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode = "Error3";
}

function encrypt($sData)
{
    global $pos_config;
    $sKey = $pos_config['secret_key'];
    $sResult = '';
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;
    }
    return encode_base64($sResult);
}

function encode_base64($sData)
{
    $sBase64 = base64_encode($sData);
    return strtr($sBase64, '+/', '-_');
}

function decode_base64($sData)
{
    $sBase64 = strtr($sData, '-_', '+/');
    return base64_decode($sBase64);
}

echo ($HtmlCode);
