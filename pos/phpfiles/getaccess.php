<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$Details = $_POST['dt'];
$Username = $Details["Username"];
$UserDetails = array();

$sql = "Select Surname, Firstname, Middlename, Gateway, LastLogin, Category from staffrecord Where Username = '" . $Username . "'";
$result = mysqli_query($conn, $sql);
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $UserDetails["Surname"] = $newArray[0];
        $UserDetails["Firstname"] = $newArray[1];
        $UserDetails["Middlename"] = $newArray[2] == null || $newArray[2] == "" ? "" : $newArray[2];
        $UserDetails["Scrambled"] = decrypt($newArray[3]);
        $UserDetails["LastLogin"] = $newArray[4];
        $UserDetails["Category"] = $newArray[5];
    }

    $sqlQ = "Select NS, MR, SI, CP, ASt, VS, CPw, SP, SR, GS, IV, CR, VD,CF, GH, US from permission Where ID = '" . $Username . "'";
    $resultQ = mysqli_query($conn, $sqlQ);
    if (pos_num_rows($resultQ) > 0) {
        while ($newArrayQ = pos_fetch_array($resultQ)) {
            $UserDetails["Ns"] = $newArrayQ[0];
            $UserDetails["Mr"] = $newArrayQ[1];
            $UserDetails["Si"] = $newArrayQ[2];
            $UserDetails["Cp"] = $newArrayQ[3];
            $UserDetails["ASt"] = $newArrayQ[4];
            $UserDetails["Vs"] = $newArrayQ[5];
            $UserDetails["CPw"] = $newArrayQ[6];
            $UserDetails["Sp"] = $newArrayQ[7];
            $UserDetails["Sr"] = $newArrayQ[8];
            $UserDetails["Gs"] = $newArrayQ[9];
            $UserDetails["Iv"] = $newArrayQ[10];
            $UserDetails["Cr"] = $newArrayQ[11];
            $UserDetails["Vd"] = $newArrayQ[12];
            $UserDetails["Cf"] = $newArrayQ[13];
            $UserDetails["Gh"] = $newArrayQ[14];
            $UserDetails["Us"] = $newArrayQ[15];
        }
    }

} else {
    $UserDetails["Scrambled"] = "Disallowed";
}

echo (json_encode($UserDetails));

function encrypt($sData)
{
    global $pos_config;
    $sKey = $pos_config['secret_key'];
    $sResult = '';
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;
    }
    return encode_base64($sResult);
}

function decrypt($sData)
{
    global $pos_config;
    $sKey = $pos_config['secret_key'];
    $sResult = '';
    $sData = decode_base64($sData);
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) - ord($sKeyChar));
        $sResult .= $sChar;
    }
    return $sResult;
}

function encode_base64($sData)
{
    $sBase64 = base64_encode($sData);
    return strtr($sBase64, '+/', '-_');
}

function decode_base64($sData)
{
    $sBase64 = strtr($sData, '-_', '+/');
    return base64_decode($sBase64);
}
