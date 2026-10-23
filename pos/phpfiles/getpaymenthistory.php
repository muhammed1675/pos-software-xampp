<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$Debt = $_POST["dt"];
$Rid = $Debt["RID"];
$Customer = $Debt["Customer"];
$allgoods = array();
$n = 0;
$sql = "SELECT PaymentID, AmountPaid, PaymentMode, StaffPaid, DatePaid FROM debtpaymentrecords WHERE ReceiptNumber = '" . $Rid . "' ORDER BY SN";

$result = mysqli_query($conn, $sql);
if ($result) {
    $TP = 0;
    if (pos_num_rows($result) > 0) {
        while ($newArray = pos_fetch_array($result)) {
            if (floatval($newArray[1]) > floatval($newArray[3])) {
                $allgoods[$n]["PID"] = $newArray[0];
                $allgoods[$n]["RID"] = $Rid;
                $allgoods[$n]["AmountPaid"] = $newArray[1];
                $allgoods[$n]["PaymentMode"] = $newArray[2];
                $allgoods[$n]["Staff"] = $newArray[3];
                $allgoods[$n]["DatePaid"] = $newArray[4];
                $allgoods[$n]["Customer"] = $Customer;
                $n++;
            }
        }
        
    }
}

echo (json_encode($allgoods));
// echo ($sql);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
