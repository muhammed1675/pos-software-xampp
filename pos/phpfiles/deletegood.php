<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$goodtodelete = $_POST["dt"];
$Htmlcode = "";
$sql = "DELETE FROM allgoodsproperties WHERE Goodname  = '" . $goodtodelete . "'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $Htmlcode = "Success success SUCCESS";
} else {
    $Htmlcode = "Failure";
}

echo $Htmlcode;
