<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$allinventoryrecords = array();
$allgoodnames = array();
$returnedrecords = array();
$SearchCriteria = $_POST["dt"];
$n = 0;
$Tcp = 0;
$Tsp = 0;
$Tpr = 0;

if ($SearchCriteria === "") {
    $sql = "SELECT GoodID, Goodname, CurrentCostPrice, UnitLeft,  UnitSellingPrice1, UnitSellingPrice2, UnitSellingPrice3 FROM allgoodsproperties ORDER BY Goodname";
} else {
    $sql = "SELECT GoodID, Goodname, CurrentCostPrice, UnitLeft,  UnitSellingPrice1, UnitSellingPrice2, UnitSellingPrice3 FROM allgoodsproperties WHERE Goodname = '" . $SearchCriteria . "'";
}

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $allinventoryrecords[$n]["GID"] = $newArray[0] == "" || $newArray[0] == "-" ? "" : $newArray[0];
        $allinventoryrecords[$n]["Itemname"] = $newArray[1] == "" || $newArray[1] == "-" ? "" : $newArray[1];
        $allgoodnames[$n] = $newArray[1];
        $allinventoryrecords[$n]["CostPrice"] = $newArray[2] == "" || $newArray[2] == "-" ? "" : $newArray[2];

        $allinventoryrecords[$n]["UnitLeft"] = $newArray[3] == "" || $newArray[3] == "-" ? "" : $newArray[3];

        $allinventoryrecords[$n]["USP1"] = $newArray[4] == "" || $newArray[4] == "-" ? "" : $newArray[4];
        $allinventoryrecords[$n]["USP2"] = $newArray[5] == "" || $newArray[5] == "-" ? "" : $newArray[5];
        $allinventoryrecords[$n]["USP3"] = $newArray[6] == "" || $newArray[6] == "-" ? "" : $newArray[6];
        $allinventoryrecords[$n]["EditProperties"] = false;

        $cp = $newArray[2] == "" ? 0 : doubleval($newArray[2]);

        $usp = $newArray[4] == "" ? 0 : doubleval($newArray[4]);

        $uq = $newArray[3] == "" ? 0 : doubleval($newArray[3]);

        $tc = ($cp * $uq);
        $sc = ($newArray[4] == "" || $newArray[4] == "0") ? 0 : ($usp * $uq);
        $pr = $sc - $tc;

        $allinventoryrecords[$n]["TotalCostPrice"] = round($tc, 2);
        $allinventoryrecords[$n]["TotalSellingPrice"] = round($sc, 2);
        $allinventoryrecords[$n]["TotalProfit"] = round($pr, 2);

        $Tcp += $tc;
        $Tsp += $sc;
        $Tpr += $pr;

        $n++;
    }

    $allinventoryrecords[$n]["GID"] = "";
    $allinventoryrecords[$n]["Itemname"] = "TOTAL";
    $allgoodnames[$n] = "";
    $allinventoryrecords[$n]["CostPrice"] = "";

    $allinventoryrecords[$n]["UnitLeft"] = "";
    $allinventoryrecords[$n]["USP1"] = "";
    $allinventoryrecords[$n]["USP2"] = "";
    $allinventoryrecords[$n]["USP3"] = "";
    $allinventoryrecords[$n]["EditProperties"] = "";
    $allinventoryrecords[$n]["TotalCostPrice"] = round($Tcp, 2);
    $allinventoryrecords[$n]["TotalSellingPrice"] = round($Tsp, 2);
    $allinventoryrecords[$n]["TotalProfit"] = round($Tpr, 2);

    $returnedrecords["NamesOnly"] = $allgoodnames;
    $returnedrecords["allrecords"] = $allinventoryrecords;
}

echo (json_encode($returnedrecords));
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
