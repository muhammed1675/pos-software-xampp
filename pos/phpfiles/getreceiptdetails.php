<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$rid = $datasent['rid'];
$rdate = $datasent['rdate'];
$month = substr($rdate, 0, 3);
$yr = substr($rdate, 8, 4);
$ddb = $month . "_" . $yr;
$AllMyRecords = array();

$allgoods = array();
$n = 0;
$sql = "SELECT ReceiptNumber, SalesID, GoodName, Amount, Descriptions, NoU,  UnitPrice,  Profit FROM " . $ddb . "_salesrecords WHERE ReceiptNumber = '" . $rid . "' AND Status = 'Active'";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $allgoods[$n]["RID"] = $newArray[0];
        $allgoods[$n]["salesid"] = $newArray[1];
        $allgoods[$n]["ordername"] = $newArray[2];
        $allgoods[$n]["subtotal"] = $newArray[3];
        $allgoods[$n]["description"] = $newArray[4] . " " . $newArray[2];
        $allgoods[$n]["nou"] = floatval($newArray[5]);
        $allgoods[$n]["unitprice"] = $newArray[6];
        $allgoods[$n]["profit"] = $newArray[7];
        $allgoods[$n]["ddb"] = $ddb;
        $n++;

    }
}
echo (json_encode($allgoods));
// echo ($sql);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
