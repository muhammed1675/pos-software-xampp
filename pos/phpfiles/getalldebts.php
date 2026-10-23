<?php
// header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$AllPayable = 0;
$AllPaid = 0;
$AllProfit = 0;
$AllDiscount = 0;
$AllBalance = 0;
$allgoods = array();
$n = 0;
$sql = "SELECT ReceiptNumber, Payable, Discount, Paid, PaymentMode, DatePaid, Customer, Phone, Staff, Profit, LastPaymentDate FROM debtreceiptdetails";

$result = mysqli_query($conn, $sql);
if ($result) {
    if (pos_num_rows($result) > 0) {
        while ($newArray = pos_fetch_array($result)) {
            if (floatval($newArray[1]) > floatval($newArray[3])) {
                $allgoods[$n]["RID"] = $newArray[0];
                $allgoods[$n]["Payable"] = $newArray[1];
                $AllPayable += $newArray[1];
                $allgoods[$n]["Discount"] = $newArray[2];
                $AllDiscount += $newArray[2];
                $allgoods[$n]["Paid"] = $newArray[3];
                $AllPaid += $newArray[3];
                $allgoods[$n]["Balance"] = floatval($newArray[1]) - floatval($newArray[3]);
                $AllBalance += floatval($newArray[1]) - floatval($newArray[3]);
                $allgoods[$n]["PaymentMode"] = $newArray[4];
                $allgoods[$n]["DatePaid"] = $newArray[5];
                $allgoods[$n]["Customer"] = $newArray[6];
                $allgoods[$n]["Phone"] = $newArray[7];
                $allgoods[$n]["Staff"] = $newArray[8];
                $allgoods[$n]["Profit"] = $newArray[9];
                $allgoods[$n]["LastPayment"] = $newArray[10];
                $AllProfit += $newArray[9];
                $n++;
            }
        }
        $allgoods[$n]["RID"] = "TOTAL";
        $allgoods[$n]["Payable"] = $AllPayable;
        $allgoods[$n]["Discount"] = $AllDiscount;
        $allgoods[$n]["Paid"] = $AllPaid;
        $allgoods[$n]["Balance"] = $AllBalance;
        $allgoods[$n]["PaymentMode"] = "-";
        $allgoods[$n]["DatePaid"] = "-";
        $allgoods[$n]["Customer"] = "-";
        $allgoods[$n]["Phone"] = "-";
        $allgoods[$n]["Staff"] = "-";
        $allgoods[$n]["Profit"] = $AllProfit;
        $allgoods[$n]["LastPayment"] = "-";

    }
}

echo (json_encode($allgoods));
// echo ($sql);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
