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
$HtmlCode = "";
$itemname = $_POST['dt'];
$details = array();
$sql = "SELECT GoodID, Goodname, Soldin, Currentcostprice,  Unitleft, ConverterRate,  UnitSellingPrice1, UnitSellingPrice2, UnitSellingPrice3, minimumexpirydays, minimumquantity    From allgoodsproperties  Where Goodname = '" . $itemname . "'";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if (pos_num_rows($result) > 0) {
    $r = 1;
    while ($newArray = pos_fetch_array($result)) {
        $details["GoodID"] = $newArray[0] === null ? "" : $newArray[0];
        $details["Goodname"] = $newArray[1] === null ? "" : $newArray[1];
        $details["Soldin"] = $newArray[2] === null ? "" : $newArray[2];
        $details["CostPrice"] = $newArray[3] === null ? "" : $newArray[3];
        $details["QUnit"] = $newArray[5] === null ? "" : $newArray[4];
        $details["Converter"] = $newArray[6] === null ? "" : $newArray[5];
        $details["UP1"] = $newArray[6] === null ? "" : $newArray[6];
        $details["UP2"] = $newArray[7] === null ? "" : $newArray[7];
        $details["UP3"] = $newArray[8] === null ? "" : $newArray[8];
        $details["MExp"] = $newArray[9] === null ? "" : $newArray[9];
        $details["MQua"] = $newArray[10] === null ? "" : $newArray[10];

    }
}

echo (json_encode($details));
