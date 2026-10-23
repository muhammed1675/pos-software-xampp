<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

include "connectdatabase.php";
$HtmlCode = "";
$TheDates = $_POST['dt'];
$AllCustomerRecords = array();
$Start = $TheDates['Start'];
$End = $TheDates['End'];
$AllIDs = array();
$AllRecords = array();
$start_date = $Start; // start date
$end_date = $End; // end date

$start_month = (new DateTime($start_date))->format('M Y'); // get the month and year of start date in "MMM YYYY" format
$start_year = (new DateTime($start_date))->format('Y'); // get the year of start date

$end_month = (new DateTime($end_date))->format('M Y'); // get the month and year of end date in "MMM YYYY" format
$end_year = (new DateTime($end_date))->format('Y'); // get the year of end date

$months = array(); // initialize an array to store the months and years between the two dates

// loop through the months and years between the two dates
for ($i = $start_year; $i <= $end_year; $i++) {
    $month_start = 1;
    $month_end = 12;
    if ($i == $start_year) {
        $month_start = (new DateTime($start_date))->format('m');
    }
    if ($i == $end_year) {
        $month_end = (new DateTime($end_date))->format('m');
    }
    for ($j = $month_start; $j <= $month_end; $j++) {
        $month = DateTime::createFromFormat('!m', $j)->format('M');
        $months[] = $month . ' ' . $i; // add the month and year to the array in "MMM YYYY" format
    }
}

foreach ($months as $month) {
    $dby = str_replace(' ', '_', $month);

    $sqlC = " SHOW TABLES LIKE '" . $dby . "_receiptdetails'";
    $res = mysqli_query($conn, $sqlC);
    $table_exists = pos_num_rows($res) > 0;
    if ($table_exists) {
        $sqlR = "SELECT DISTINCT Customer A, BusinessName B FROM " . $dby . "_receiptdetails A, customerrecords B WHERE A.Customer LIKE 'CST_%' AND A.Customer = B.CustomerID";
        $result = mysqli_query($conn, $sqlR);
        $n = 0;
        if (pos_num_rows($result) > 0) {
            while ($newArray = pos_fetch_array($result)) {
                if (!in_array($newArray[0], $AllIDs)) {
                    array_push($AllIDs, $newArray[0]);
                    $AllRecords[$n][0] = $n + 1;
                    $AllRecords[$n][1] = $newArray[1];
                    $n++;
                }
            }
        }
    }
}

if (count($AllIDs) > 0) {
    $k = 0;

    foreach ($AllIDs as $ThisID) {
        $c = 2;
        $Summation = 0;

        foreach ($months as $month) {
            $dby = str_replace(' ', '_', $month);
            $sqlC = " SHOW TABLES LIKE '" . $dby . "_receiptdetails'";
            $res = mysqli_query($conn, $sqlC);
            $table_exists = pos_num_rows($res) > 0;
            if ($table_exists) {

                $sqlK = "SELECT Payable from " . $dby . "_receiptdetails WHERE Customer = '" . $ThisID . "'";

                $result = mysqli_query($conn, $sqlK);
                $ThisMonthTotal = 0;

                if (pos_num_rows($result) > 0) {
                    while ($newArray = pos_fetch_array($result)) {
                        $ThisMonthTotal += floatval($newArray[0]);
                    }
                    $AllRecords[$k][$c] = $ThisMonthTotal;
                    $Summation += $ThisMonthTotal;
                    $c++;
                } else {
                    $ThisMonthTotal = 0;
                    $AllRecords[$k][$c] = $ThisMonthTotal;
                    $Summation += $ThisMonthTotal;
                    $c++;
                }
            } else {
                $ThisMonthTotal = 0;
                $AllRecords[$k][$c] = $ThisMonthTotal;
                $Summation += $ThisMonthTotal;
                $c++;
            }
            $AllRecords[$k][$c] = $Summation;

        }
        $k++;
    }

}
array_unshift($months, 'SN', 'REGISTERED NAMES');
array_push($months, "TOTAL");
$AllCustomerRecords["Months"] = $months;
$AllCustomerRecords["Records"] = $AllRecords;

echo (json_encode($AllCustomerRecords));

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
