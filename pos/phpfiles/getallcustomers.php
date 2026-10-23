<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$AllCustomers = array();
$n = 0;
$sql = "Select CustomerID, BusinessName, Address, PhoneNumber from customerrecords";
$result = mysqli_query($conn, $sql);
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $AllCustomers[$n]["CustomerID"] = $newArray[0];
        $AllCustomers[$n]["BusinessName"] = $newArray[1];
        $AllCustomers[$n]["Address"] = $newArray[2];
        $AllCustomers[$n]["PhoneNumber"] = $newArray[3];
        $n++;
    }
}

echo (json_encode($AllCustomers));
