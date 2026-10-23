<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllInvoiceRecords = array();
$type = $datasent['type'];
$datessent = $datasent['dates'];

//

if ($type === "specific") {
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    // $AllMyRecords =  getMydata($datessent, $conn, $ddb);
    $AllInvoiceRecords["ThisDate"] = $datessent;
    $AllInvoiceRecords["ThisInvoiceRecords"] = getMydata($datessent, $conn, $ddb);
} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;
        // $AllMyRecords = array_merge($AllMyRecords,getMydata($datessent[$i], $conn, $ddb));
        $AllInvoiceRecords[$i]["ThisDate"] = $datessent[$i];
        $AllInvoiceRecords[$i]["ThisInvoiceRecords"] = getMydata($datessent[$i], $conn, $ddb);
    }
}

function getMydata($dat, $conn, $dby)
{
    $allinvoice = array();
    $sql = "SELECT EntryID, DateUploaded, PictureDirectory  FROM " . $dby . "_invoice_picture WHERE DateUploaded LIKE '" . $dat . "%'";

    $result = mysqli_query($conn, $sql);
    if ($result) {
        if (pos_num_rows($result) > 0) {
            $n = 0;
            while ($newArray = pos_fetch_array($result)) {
                $allinvoice[$n]["EID"] = $newArray[0];
                $allinvoice[$n]["Dates"] = $newArray[1];
                $allinvoice[$n]["Directory"] = $newArray[2];
                $n++;
            }

        }

    }
    return $allinvoice;
}
echo (json_encode($AllInvoiceRecords));

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
