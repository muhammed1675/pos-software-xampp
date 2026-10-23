<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllSalesRecords = array();
$type = $datasent['type'];
$itemname = $datasent['itemname'];
$datessent = $datasent['dates'];
$AllMySalesHistory = array();
$AllMySupplyHistory = array();
$AllMyPriceHistory = array();
$ItemIdentity = "";
//

$sqlM = "Select GoodID FROM allgoodsproperties WHERE Goodname = '" . $itemname . "'";

$resultQ = mysqli_query($conn, $sqlM);
if (pos_num_rows($resultQ) > 0) {
    while ($newArrayQ = pos_fetch_array($resultQ)) {
        $ItemIdentity = $newArrayQ[0];
    }
}

if ($type === "specific") {
    $l = 0;
    $v = 0;
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    $AllMySalesHistory = getMydata($datessent, $conn, $ddb, $ItemIdentity, $itemname, "Sales");
    $AllMySupplyHistory = getMydata($datessent, $conn, $ddb, $ItemIdentity, $itemname, "Supply");
    $AllMyPriceHistory = getMydata($datessent, $conn, $ddb, $ItemIdentity, $itemname, "Price");
    // $AllSalesRecords["ThisDate"] = $datessent;
    // $AllSalesRecords["ThisSalesRecords"] = getMydata($datessent, $conn, $ddb, $itemname);
} else {
    $k = 0;
    for ($i = 0; $i < count($datessent); $i++) {

        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;
        $TempSales = array();
        $TempSupply = array();
        $TempPrice = array();
        $TempSales = getMydata($datessent[$i], $conn, $ddb, $ItemIdentity, $itemname, "Sales");
        for ($p = 0; $p < count($TempSales); $p++) {
            array_push($AllMySalesHistory, $TempSales[$p]);
        }
        $TempSupply = getMydata($datessent[$i], $conn, $ddb, $ItemIdentity, $itemname, "Supply");
        for ($p = 0; $p < count($TempSupply); $p++) {
            array_push($AllMySupplyHistory, $TempSupply[$p]);
        }
        $TempPrice = getMydata($datessent[$i], $conn, $ddb, $ItemIdentity, $itemname, "Price");
        for ($p = 0; $p < count($TempPrice); $p++) {
            array_push($AllMyPriceHistory, $TempPrice[$p]);
        }
        //     $AllSalesRecords[$i]["ThisDate"] = $datessent[$i];
        //     $AllSalesRecords[$i]["ThisSalesRecords"] = getMydata($datessent[$i], $conn, $ddb, $itemname);
    }
}

$AllHistoryRecord = array();
$AllHistoryRecord["Sales"] = $AllMySalesHistory;
$AllHistoryRecord["Supply"] = $AllMySupplyHistory;
$AllHistoryRecord["Price"] = $AllMyPriceHistory;

function getActualValue($bb, $cvt, $sd)
{
    $bvb4 = floatval($bb);
    $man = floor($bvb4);
    $dec = $bvb4 - $man;
    $unitvalue = round($dec * floatval($cvt));
    $unitvalue = $unitvalue > 0 ? ", " . $unitvalue . " Units" : "";
    $tipvalue = $man . " " . $sd . "s" . $unitvalue;
    return $tipvalue;
}

function getMydata($dat, $conn, $dby, $Itnm, $Itnam, $HistType)
{

    $ThisDateHistory = array();

    switch ($HistType) {
        case "Sales":
            $sqlA = "SELECT A.TrackingNo, A.GoodsID, A.Goodname, A.Nou, A.BulkBalanceBefore, A.BulkBalanceAfter, A.DateSold, A.Staff, A.Buyer, B.Soldin, B.ConverterRate FROM " . $dby . "_salesrecords A, allgoodsproperties B  WHERE A.GoodsID = '" . $Itnm . "' AND  A.DateSold LIKE '" . $dat . "%' AND A.GoodsID = B.GoodID ORDER BY TrackingNo ASC";
            break;
        case "Supply":
            $sqlA = "SELECT A.TrackingNo, A.GoodID, A.Goodname, A.QuantityUnit, A.BulkBalanceBefore, A.BulkBalanceAfter, A.DateReceived, A.Receiver, A.Supplier, B.Soldin, B.ConverterRate FROM " . $dby . "_allgoodssupplied A, allgoodsproperties B WHERE A.GoodID = '" . $Itnm . "' AND  A.DateReceived LIKE '" . $dat . "%' AND A.GoodID = B.GoodID  ORDER BY TrackingNo ASC";
            break;
        case "Price":
            $sqlA = "SELECT A.TrackingNo,  A.Itemname, A.Quantity, A.Description, A.Staff, A.Date,  B.Soldin, B.ConverterRate FROM " . $dby . "_price_change_track A, allgoodsproperties B WHERE A.GoodID = '" . $Itnm . "' AND  A.Date LIKE '" . $dat . "%' AND A.GoodID = B.GoodID ORDER BY A.TrackingNo ASC";
            break;
    }
    // echo ($sqlA);
    $result = mysqli_query($conn, $sqlA);
    if ($result) {
        $b = 0;
        if (pos_num_rows($result) > 0) {
            while ($newArray = pos_fetch_array($result)) {
                if ($HistType != "Price") {
                    $ThisDateHistory[$b]["TrackingNo"] = floatval($newArray[0]);
                    $ThisDateHistory[$b]["GoodID"] = $newArray[1];
                    $ThisDateHistory[$b]["GoodName"] = $newArray[2];
                    $ThisDateHistory[$b]["Unit"] = $newArray[3];
                    $ThisDateHistory[$b]["BBBefore"] = $newArray[4];
                    $ThisDateHistory[$b]["BBAfter"] = $newArray[5];
                    $ThisDateHistory[$b]["TransactionDate"] = $newArray[6];
                    $ThisDateHistory[$b]["Staff"] = $newArray[7];
                    $ThisDateHistory[$b]["Person"] = $newArray[8];
                    $ThisDateHistory[$b]["Soldin"] = $newArray[9];
                    $ThisDateHistory[$b]["Converter"] = $newArray[10];
                    $ThisDateHistory[$b]["TType"] = $HistType;
                    $ThisDateHistory[$b]["ToolTipBefore"] = getActualValue($newArray[4], $newArray[10], $newArray[9]);
                    $ThisDateHistory[$b]["ToolTipAfter"] = getActualValue($newArray[5], $newArray[10], $newArray[9]);
                    $b++;
                } else {

                    $ThisDateHistory[$b]["TrackingNo"] = floatval($newArray[0]);
                    $ThisDateHistory[$b]["GoodID"] = $newArray[1];
                    $ThisDateHistory[$b]["GoodName"] = $newArray[1];
                    $ThisDateHistory[$b]["Bulk"] = "-";
                    $ThisDateHistory[$b]["Unit"] = "-";
                    $ThisDateHistory[$b]["BBBefore"] = $newArray[2];
                    $ThisDateHistory[$b]["BBAfter"] = $newArray[2];
                    $ThisDateHistory[$b]["TransactionDate"] = $newArray[5];
                    $ThisDateHistory[$b]["Staff"] = $newArray[4];
                    $ThisDateHistory[$b]["Person"] = $newArray[3];
                    $ThisDateHistory[$b]["Soldin"] = $newArray[6];
                    $ThisDateHistory[$b]["Converter"] = $newArray[7];
                    $ThisDateHistory[$b]["TType"] = $HistType;
                    $ThisDateHistory[$b]["ToolTipBefore"] = getActualValue($newArray[2], $newArray[7], $newArray[6]);
                    $ThisDateHistory[$b]["ToolTipAfter"] = getActualValue($newArray[2], $newArray[7], $newArray[6]);
                    $b++;

                }
            }
        }
    }

    return $ThisDateHistory;

}
echo (json_encode($AllHistoryRecord));
// echo ($sql);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
