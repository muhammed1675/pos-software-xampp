<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$AllUsernames = array();

$sql = "Select Username, Surname, Firstname, Middlename, DateRegistered, LastLogin from staffrecord";
$result = mysqli_query($conn, $sql);
if (pos_num_rows($result) > 0) {
    $n = 0;
    while ($newArray = pos_fetch_array($result)) {
        $AllUsernames[$n]["Username"] = $newArray[0];
        $AllUsernames[$n]["Fullname"] = $newArray[1] . " " . $newArray[2] . " " . $newArray[3];
        $AllUsernames[$n]["DateRegistered"] = $newArray[4];
        $AllUsernames[$n]["LastLogin"] = $newArray[5];
        $n++;
    }
}

echo (json_encode($AllUsernames));
