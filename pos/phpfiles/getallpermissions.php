<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$Username = $_POST['dt'];
$UserDetails = array();

$sqlQ = "Select NS, MR, SI, CP, ASt, VS, CPw, SP, SR, GS, IV, CR,  VD, CF, GH, US from permission Where ID = '" . $Username . "'";
$resultQ = mysqli_query($conn, $sqlQ);
if (pos_num_rows($resultQ) > 0) {
    while ($newArrayQ = pos_fetch_array($resultQ)) {
        $UserDetails[0]["name"] = "Make Sales";
        $UserDetails[0]["kii"] = "NS";
        $UserDetails[0]["vals"] = $newArrayQ[0];
        $UserDetails[1]["name"] = "Check Personal Report";
        $UserDetails[1]["vals"] = $newArrayQ[1];
        $UserDetails[1]["kii"] = "MR";
        $UserDetails[2]["name"] = "Supply Goods In";
        $UserDetails[2]["vals"] = $newArrayQ[2];
        $UserDetails[2]["kii"] = "SI";
        $UserDetails[3]["name"] = "Change Good's Price";
        $UserDetails[3]["vals"] = $newArrayQ[3];
        $UserDetails[3]["kii"] = "CP";
        $UserDetails[4]["name"] = "Register New Staff";
        $UserDetails[4]["vals"] = $newArrayQ[4];
        $UserDetails[4]["kii"] = "ASt";
        $UserDetails[5]["name"] = "View Staff details";
        $UserDetails[5]["vals"] = $newArrayQ[5];
        $UserDetails[5]["kii"] = "VS";
        $UserDetails[6]["name"] = "Change Personal Password";
        $UserDetails[6]["vals"] = $newArrayQ[6];
        $UserDetails[6]["kii"] = "CPw";
        $UserDetails[7]["name"] = "Check All Supply Report";
        $UserDetails[7]["vals"] = $newArrayQ[7];
        $UserDetails[7]["kii"] = "SP";
        $UserDetails[8]["name"] = "Check All Sales Report";
        $UserDetails[8]["vals"] = $newArrayQ[8];
        $UserDetails[8]["kii"] = "SR";
        $UserDetails[9]["name"] = "Check Goods Sales Summary";
        $UserDetails[9]["vals"] = $newArrayQ[9];
        $UserDetails[9]["kii"] = "GS";
        $UserDetails[10]["name"] = "Check the Inventory";
        $UserDetails[10]["vals"] = $newArrayQ[10];
        $UserDetails[10]["kii"] = "IV";
        $UserDetails[11]["name"] = "View Registered customers Records";
        $UserDetails[11]["vals"] = $newArrayQ[11];
        $UserDetails[11]["kii"] = "CR";
        $UserDetails[12]["name"] = "View/Pay Debts";
        $UserDetails[12]["vals"] = $newArrayQ[12];
        $UserDetails[12]["kii"] = "VD";
        $UserDetails[13]["name"] = "Record Cash Flow";
        $UserDetails[13]["vals"] = $newArrayQ[13];
        $UserDetails[13]["kii"] = "CF";
        $UserDetails[14]["name"] = "Check Goods History";
        $UserDetails[14]["vals"] = $newArrayQ[14];
        $UserDetails[14]["kii"] = "GH";
        $UserDetails[15]["name"] = "Update Online Server";
        $UserDetails[15]["vals"] = $newArrayQ[15];
        $UserDetails[15]["kii"] = "US";

    }
}

echo (json_encode($UserDetails));
