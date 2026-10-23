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
$Fullname = $SP['Fullname'];
$ThePermissions = $SP['ThePermissions'];

$Admin = $SP['Admin'];
date_default_timezone_set('Africa/Lagos');
$DateRegistered = date("M d, Y h:i a");

$sql = "UPDATE permission SET " . $ThePermissions[0]["kii"] . " = '" . $ThePermissions[0]["vals"] . "', " . $ThePermissions[1]["kii"] . " = '" . $ThePermissions[1]["vals"] . "', " . $ThePermissions[2]["kii"] . " = '" . $ThePermissions[2]["vals"] . "', " . $ThePermissions[3]["kii"] . " = '" . $ThePermissions[3]["vals"] . "', " . $ThePermissions[4]["kii"] . " = '" . $ThePermissions[4]["vals"] . "', " . $ThePermissions[5]["kii"] . " = '" . $ThePermissions[5]["vals"] . "', " . $ThePermissions[6]["kii"] . " = '" . $ThePermissions[6]["vals"] . "', " . $ThePermissions[7]["kii"] . " = '" . $ThePermissions[7]["vals"] . "', " . $ThePermissions[8]["kii"] . " = '" . $ThePermissions[8]["vals"] . "', " . $ThePermissions[9]["kii"] . " = '" . $ThePermissions[9]["vals"] . "', " . $ThePermissions[10]["kii"] . " = '" . $ThePermissions[10]["vals"] . "', " . $ThePermissions[11]["kii"] . " = '" . $ThePermissions[11]["vals"] . "', " . $ThePermissions[12]["kii"] . " = '" . $ThePermissions[12]["vals"] . "', " . $ThePermissions[13]["kii"] . " = '" . $ThePermissions[13]["vals"] . "', " . $ThePermissions[14]["kii"] . " = '" . $ThePermissions[14]["vals"] . "', " . $ThePermissions[15]["kii"] . " = '" . $ThePermissions[15]["vals"] . "' WHERE ID = '" . $Username . "'";

if (mysqli_query($conn, $sql)) {
    $HtmlCode .= "Success";
} else {
    $HtmlCode = "Error3";
}

echo ($HtmlCode);
