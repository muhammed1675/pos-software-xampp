<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$allgoods = array();
$n = 0;

$sql = "SELECT GoodID, Goodname FROM allgoodsproperties";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $allgoods[$n][0] = $newArray[0];
        $allgoods[$n][1] = $newArray[1];
        $n++;
    }
}

echo (json_encode($allgoods));
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
