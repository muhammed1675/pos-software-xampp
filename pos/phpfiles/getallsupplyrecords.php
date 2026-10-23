<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllSupplyRecords = array();
$type = $datasent['type'];
$datessent = $datasent['dates'];

//

if ($type === "specific") {
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    // $AllMyRecords =  getMydata($datessent, $conn, $ddb);
    $AllSupplyRecords["ThisDate"] = $datessent;
    $AllSupplyRecords["ThisSupplyRecords"] = getMydata($datessent, $conn, $ddb);
} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;
        // $AllMyRecords = array_merge($AllMyRecords,getMydata($datessent[$i], $conn, $ddb));
        $AllSupplyRecords[$i]["ThisDate"] = $datessent[$i];
        $AllSupplyRecords[$i]["ThisSupplyRecords"] = getMydata($datessent[$i], $conn, $ddb);
    }
}

function getMydata($dat, $conn, $dby)
{
    $ArraySupplierEachDate = array();
    $q = 0;
    $TheSupplier = "";
    $sqlS = "SELECT DISTINCT EntryID FROM " . $dby . "_allgoodssupplied WHERE DateReceived LIKE '" . $dat . "%'";

    $resultS = mysqli_query($conn, $sqlS);
    if ($resultS) {
        if (pos_num_rows($resultS) > 0) {
            while ($newArrayS = pos_fetch_array($resultS)) {

                $AllTotalCostPrice = 0;
                $allgoods = array();
                $n = 0;
                $sql = "SELECT A.EntryID, A.GoodID,  A.Goodname,  A.QuantityUnit, A.UnitCostPrice, A.TotalCostPrice, A.Receiver, A.Supplier,  A.DateReceived, B.Soldin, B.ConverterRate FROM " . $dby . "_allgoodssupplied A, allgoodsproperties B WHERE A.EntryID = '" . $newArrayS[0] . "' AND A.GoodID = B.GoodID ORDER BY A.SN";

                $result = mysqli_query($conn, $sql);
                if ($result) {
                    if (pos_num_rows($result) > 0) {
                        while ($newArray = pos_fetch_array($result)) {
                            $allgoods[$n]["EID"] = $newArray[0];
                            $allgoods[$n]["GID"] = $newArray[1];
                            $allgoods[$n]["GoodName"] = $newArray[2];
                            $soldin = $newArray[3] > 1 ? $newArray[9] . "s" : $newArray[9];

                            $allgoods[$n]["QUnit"] = $newArray[3];
                            $allgoods[$n]["CostPrice"] = $newArray[4];
                            $allgoods[$n]["TotalCostPrice"] = $newArray[5];
                            $AllTotalCostPrice += floatval($newArray[5]);
                            $allgoods[$n]["Receiver"] = $newArray[6];
                            $allgoods[$n]["Supplier"] = $newArray[7];
                            $TheSupplier = floatval($newArray[7]);
                            $allgoods[$n]["DateSupplied"] = $newArray[8];
                            $allgoods[$n]["Converter"] = $newArray[10];
                            $allgoods[$n]["Soldin"] = $soldin;
                            $allgoods[$n]["DBase"] = $dby . "_allgoodssupplied";
                            $n++;
                        }
                        $allgoods[$n]["EID"] = "TOTAL";
                        $allgoods[$n]["GID"] = "-";
                        $allgoods[$n]["Goodname"] = "-";
                        $allgoods[$n]["QUnit"] = "-";
                        $allgoods[$n]["CostPrice"] = "-";
                        $allgoods[$n]["TotalCostPrice"] = $AllTotalCostPrice;
                        $allgoods[$n]["Receiver"] = "-";
                        $allgoods[$n]["Supplier"] = "-";
                        $allgoods[$n]["DateSupplied"] = "-";

                    }

                }

                $ArraySupplierEachDate[$q]["TheTable"] = $allgoods;
                $ArraySupplierEachDate[$q]["TheSupplier"] = $TheSupplier;
                $q++;
            }
        }
    }

    return $ArraySupplierEachDate;
}
echo (json_encode($AllSupplyRecords));

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
