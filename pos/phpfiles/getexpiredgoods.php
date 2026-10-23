<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
include "connectdatabase.php";
$AllExpiredGoods = array();
date_default_timezone_set('Africa/Lagos');
$date = date("Y-m-d");

$sql = "Select A.SN, A.Goodsid, A.Supplieddate, A.Expirydate, B.Goodname, B.minimumexpirydays  from expirydates A, allgoodsproperties B WHERE A.Expirystatus = 'Active' AND A.Goodsid = B.GoodID";

$result = mysqli_query($conn, $sql);
$n = 0;
if (pos_num_rows($result) > 0) {
    while ($newArray = pos_fetch_array($result)) {
        $string_date = $newArray[3];
        $mExp = intval($newArray[5]);
        $ed = strtotime($string_date);
        $expdate = date("Y-m-d", $ed);
        $date_diff = strtotime($expdate) - strtotime($date);
        $days_diff = round($date_diff / (60 * 60 * 24));
        $diff = intval($days_diff);
        if ($diff < $mExp) {
            $AllExpiredGoods[$n]['SN'] = $newArray[0];
            $AllExpiredGoods[$n]['GoodsId'] = $newArray[1];
            $AllExpiredGoods[$n]['Supplieddate'] = $newArray[2];
            $AllExpiredGoods[$n]['Expirydated'] = $newArray[3];
            $AllExpiredGoods[$n]['Itemname'] = $newArray[4];
            $AllExpiredGoods[$n]['mExp'] = $mExp;
            $AllExpiredGoods[$n]['toExp'] = $diff;

            $n++;
        }

    }
}

echo (json_encode($AllExpiredGoods));
