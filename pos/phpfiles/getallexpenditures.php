<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$datasent = $_POST['dt'];
$AllExpenditureRecords = array();
$type = $datasent['type'];
$datessent = $datasent['dates'];
$dbmonthyear1 = date('M') . "_" . date('Y') . "_expenditures";
$AllAffectedExpenditure = array();
$AllAffectedTotal = array();
$TotalArray = array();
$NewArray = array();

$sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear1 . " (SN INT NOT NULL AUTO_INCREMENT, "
    . "EntryID VARCHAR(100), "
    . "Giver VARCHAR(100) NULL, "
    . "Amount VARCHAR(100) NULL, "
    . "Receiver VARCHAR(100), "
    . "Description VARCHAR(1000) DEFAULT '0', "
    . "Account VARCHAR(100) DEFAULT '0', "
    . "DateRegistered VARCHAR(50), "
    . "PRIMARY KEY (SN)"
    . ")";
if (mysqli_query($conn, $sql1) === true) {
    $HtmlCode .= "New table created successfully";
}

if ($type === "specific") {
    $month = substr($datessent, 0, 3);
    $yr = substr($datessent, 8, 12);
    $ddb = $month . "_" . $yr;
    $AllExpenditureRecords["ThisDate"] = $datessent;
    $AllExpenditureRecords["ThisExpenditureRecords"] = gettheExpenditure($datessent, $conn, $ddb);
} else {
    for ($i = 0; $i < count($datessent); $i++) {
        $month = substr($datessent[$i], 0, 3);
        $yr = substr($datessent[$i], 8, 12);
        $ddb = $month . "_" . $yr;

        $AllExpenditureRecords[$i]["ThisDate"] = $datessent[$i];
        $AllExpenditureRecords[$i]["ThisExpenditureRecords"] = gettheExpenditure($datessent[$i], $conn, $ddb);

    }

}

function gettheExpenditure($dat, $conn, $dby)
{

    $AllCash = 0;
    $AllPOS = 0;
    $AllTransfer = 0;

    $ExpenditureRecord = array();
    $m = 0;
    $Cash = 0;
    $POS = 0;
    $Transfer = 0;

    $sqlCheck = "SHOW TABLES LIKE '" . $dby . "_expenditures'";

    $resultM = mysqli_query($conn, $sqlCheck);
    if ($resultM->num_rows > 0) {

        $sqlA = "SELECT EntryID, Giver, Amount, Receiver, Description, Account, DateRegistered FROM " . $dby . "_expenditures WHERE DateRegistered LIKE '" . $dat . "%'";

        $resultA = mysqli_query($conn, $sqlA) or die(mysqli_error($conn));
        if (pos_num_rows($resultA) > 0) {
            while ($newArrayA = pos_fetch_array($resultA)) {
                $Cash = $newArrayA[5] === "Cash" ? $Cash + (floatval($newArrayA[2])) : $Cash;

                $POS = $newArrayA[5] === "POS" ? $POS + (floatval($newArrayA[2])) : $POS;

                $Transfer = $newArrayA[5] === "Transfer" ? $Transfer + (floatval($newArrayA[2])) : $Transfer;

                $ExpenditureRecord[$m]["EntryID"] = $newArrayA[0];
                $ExpenditureRecord[$m]["Giver"] = $newArrayA[1];
                $ExpenditureRecord[$m]["Amount"] = $newArrayA[2];
                $ExpenditureRecord[$m]["Receiver"] = $newArrayA[3];
                $ExpenditureRecord[$m]["Purpose"] = $newArrayA[4];
                $ExpenditureRecord[$m]["Account"] = $newArrayA[5];
                $ExpenditureRecord[$m]["DateRegistered"] = $newArrayA[6];
                $ExpenditureRecord[$m]["ddb"] = $dby;
                $ExpenditureRecord[$m]["Datesent"] = $dat;
                $m++;
            }
            $ExpenditureRecord[$m]["EntryID"] = "Total Expenditure";
            $ExpenditureRecord[$m]["Giver"] = "";
            $ExpenditureRecord[$m]["Amount"] = $POS + $Cash + $Transfer;
            $ExpenditureRecord[$m]["Receiver"] = "";
            $ExpenditureRecord[$m]["Purpose"] = "";
            $ExpenditureRecord[$m]["Account"] = "";
            $ExpenditureRecord[$m]["DateRegistered"] = "";
            $ExpenditureRecord[$m]["ddb"] = $dby;
            $ExpenditureRecord[$m]["Datesent"] = $dat;
        }
        return $ExpenditureRecord;
    } else {
        return array();
    }
}

echo (json_encode($AllExpenditureRecords));
// echo ($sqlS);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
