<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$AllUsernames = array();
$n = 0;
$sql = "Select Username from staffrecord";
$result = mysqli_query($conn, $sql);
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $AllUsernames[$n] = $newArray[0];
        $n++;
    }
}

echo (json_encode($AllUsernames));
