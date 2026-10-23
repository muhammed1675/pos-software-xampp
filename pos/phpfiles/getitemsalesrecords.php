<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllItemsRecords = array();
$type = $datasent['type'];
$datessent = $datasent['dates'];
$AllAffectedItems = array();
$AllAffectedTotal = array();
$TotalArray = array();
$NewArray = array();

$dbmonthyear1 = date('M') . "_" . date('Y') . "_salesrecords";
$sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear1 . " (SN INT NOT NULL AUTO_INCREMENT, "
    . "ReceiptNumber VARCHAR(100), "
    . "SalesID VARCHAR(100) NOT NULL, "
    . "GoodsID VARCHAR(100), "
    . "GoodName VARCHAR(1000) DEFAULT '0', "
    . "NoU VARCHAR(100) DEFAULT '0', "
    . "Amount VARCHAR(50) DEFAULT '0', "
    . "Profit VARCHAR(50) DEFAULT '0', "
    . "Descriptions VARCHAR(100) DEFAULT '0', "
    . "UnitPrice VARCHAR(50) DEFAULT 'NA' ,"
    . "BulkBalanceBefore DOUBLE NULL, "
    . "BulkBalanceAfter DOUBLE NULL, "
    . "DateSold VARCHAR(50) NULL, "
    . "Staff VARCHAR(50) NULL, "
    . "Buyer VARCHAR(50) NULL, "
    . "TrackingNo INT(10) NULL, "
    . "Status VARCHAR(10) DEFAULT 'Active', "
    . "PRIMARY KEY (SN, SalesID)"
    . ")";

if (mysqli_query($conn, $sql1) === true) {
    $HtmlCode = "New table created successfully";
}

if ($type === "specific") {
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    $AllItemRecords["ThisDate"] = $datessent;
    $AllItemRecords["ThisItemRecords"] = gettheItem($datessent, $conn, $ddb, $AllAffectedItems);

} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;

        $AllItemRecords[$i]["ThisDate"] = $datessent[$i];
        $AllItemRecords[$i]["ThisItemRecords"] = gettheItem($datessent[$i], $conn, $ddb, $AllAffectedItems);

    }

    $ATUnit = 0;
    $ATProfit = 0;
    $ATTotal = 0;
    $z = 0;
    for ($g = 0; $g < count($AllAffectedItems); $g++) {

        $ItemUnit = 0;
        $ItemProfit = 0;

        for ($w = 0; $w < count($AllItemRecords); $w++) {

            for ($c = 0; $c < count($AllItemRecords[$w]["ThisItemRecords"]); $c++) {
                if ($AllItemRecords[$w]["ThisItemRecords"][$c]["ItemName"] === $AllAffectedItems[$g]) {

                    $ItemUnit += floatval($AllItemRecords[$w]["ThisItemRecords"][$c]["Unit"]) > 0 ? floatval($AllItemRecords[$w]["ThisItemRecords"][$c]["Unit"]) : 0;

                    $ItemProfit += floatval($AllItemRecords[$w]["ThisItemRecords"][$c]["Profit"]) > 0 ? floatval($AllItemRecords[$w]["ThisItemRecords"][$c]["Profit"]) : 0;

                }
            }

        }
        $AllAffectedItemTotal[$g]["ItemName"] = $AllAffectedItems[$g];
        $ATBulk += $ItemBulk;
        $AllAffectedItemTotal[$g]["Unit"] = $ItemUnit;
        $ATUnit += $ItemUnit;
        $AllAffectedItemTotal[$g]["Profit"] = $ItemProfit;
        $ATProfit += $ItemProfit;

        array_push($TotalArray, $AllAffectedItemTotal[$g]);

    }
    $z = count($TotalArray);
    $TotalArray[$z]["ItemName"] = "TOTAL";
    $TotalArray[$z]["Unit"] = $ATUnit;
    $TotalArray[$z]["Profit"] = $ATProfit;
    $NewArray["ThisDate"] = $datessent[0] . " - " . $datessent[count($datessent) - 1];
    $NewArray["ThisItemRecords"] = $TotalArray;
    array_unshift($AllItemRecords, $NewArray);

}

function gettheItem($dat, $conn, $dby, &$AllAffectedItems)
{

    $ItemRecord = array();
    $sqlS = "SELECT DISTINCT A.GoodName FROM " . $dby . "_salesrecords A, " . $dby . "_receiptdetails B WHERE B.DatePaid LIKE '" . $dat . "%' AND A.ReceiptNumber = B.ReceiptNumber";

    $result = mysqli_query($conn, $sqlS);
    if ($result) {
        if (pos_num_rows($result) > 0) {
            $n = 0;
            while ($newArray = pos_fetch_array($result)) {

                if (!in_array($newArray[0], $AllAffectedItems)) {
                    $ItemRecord[$n]["ItemName"] = $newArray[0];
                    array_push($AllAffectedItems, $newArray[0]);
                    $n++;
                }
            }
        }
    }
    if (count($AllAffectedItems) > 0) {

        $AllUnit = 0;
        $AllProfit = 0;
        $n = 0;
        foreach ($AllAffectedItems as $AFI) {

            $Unit = 0;
            $Profit = 0;

            $sqlA = "SELECT A.GoodName,  A.NoU, A.Profit FROM " . $dby . "_salesrecords A, " . $dby . "_receiptdetails B  WHERE B.DatePaid LIKE '" . $dat . "%' AND A.GoodName = '" . $AFI . "' AND A.Status = 'Active' AND A.ReceiptNumber = B.ReceiptNumber AND A.Status <> 'DELETED'";
            $resultA = mysqli_query($conn, $sqlA) or die(mysqli_error($conn));
            if (pos_num_rows($resultA) > 0) {
                while ($newArrayA = pos_fetch_array($resultA)) {
                    $Unit += floatval($newArrayA[1]) && is_numeric($newArrayA[1]) > 0 ? floatval($newArrayA[1]) : 0;
                    $Profit += floatval($newArrayA[2]) && is_numeric($newArrayA[2]) > 0 ? floatval($newArrayA[2]) : 0;

                }
            }

            $ItemRecord[$n]["Unit"] = $Unit;
            $ItemRecord[$n]["Profit"] = $Profit;

            $AllUnit += $Unit;
            $AllProfit += $Profit;
            $n++;

        }
        $ItemRecord[$n]["ItemName"] = "TOTAL";

        $ItemRecord[$n]["Unit"] = $AllUnit;
        $ItemRecord[$n]["Profit"] = $AllProfit;

        return $ItemRecord;
    } else {
        return array();
    }
}

echo (json_encode($AllItemRecords));
// echo ($sqlS);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
