<?php
header("Access-Control-Allow-Origin: *");
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
$updetails = array();
include "connectdatabase.php";
$HtmlCode = "";
$SID = "Invoice_" . date("Ymd") . date("his");
$dbmonthyear1 = date('M') . "_" . date('Y') . "_invoice_picture";
$folder = "./upload/" . $SID . "/";
if (!file_exists($folder)) {
    mkdir($folder);
}
$updetails[0] = $SID;
$date = date("M d, Y h:i a");
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_FILES["pictureSelected1"]["name"])) {

    $IdentityPix = $SID;
    $imagename = $_FILES["pictureSelected1"]["name"];
    $source = $_FILES["pictureSelected1"]["tmp_name"];
    $target = "upload/" . $IdentityPix . "_Picture" . ".jpg";
    $type = $_FILES["pictureSelected1"]["type"];
    // if ($type == "upload/jpeg" || $type == "upload/jpg" || $type == "upload/JPG" || $type == "upload/JPEG" || $type == "upload/png" || $type == "upload/PNG") {
    if (move_uploaded_file($source, $target)) {
        //orginal image making part

        $imagepath = $imagename;
        $save = $target; //This is the new file you saving
        $file = $target; //This is the original file
        list($width, $height) = getimagesize($file);
        $modwidth = 700;
        // $diff = $width / $modwidth;
        // $modheight = $height / $diff;
        $modheight = 800;
        $tn = imagecreatetruecolor($modwidth, $modheight);
        $image = imagecreatefromjpeg($file);
        imagecopyresampled($tn, $image, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);

        imagejpeg($tn, $save, 100);
        $updetails = $target;

        $sql1 = "CREATE TABLE IF NOT EXISTS " . $dbmonthyear1 . " (SN INT NOT NULL AUTO_INCREMENT, "
            . "EntryID VARCHAR(100), "
            . "DateUploaded VARCHAR(100) NULL, "
            . "PictureDirectory VARCHAR(1000) NULL, "
            . "PRIMARY KEY (SN)"
            . ")";
        if (mysqli_query($conn, $sql1) === true) {
            $HtmlCode .= "New table created successfully";
        }

        $sql2 = "INSERT INTO " . $dbmonthyear1 . "(EntryID, DateUploaded, PictureDirectory)  VALUES ('" . $SID . "','" . $date . "','" . $updetails . "')";

        if (mysqli_query($conn, $sql2)) {
            $HtmlCode .= "Success";
        } else {
            $HtmlCode .= "Error";
        }

    } else {
        $HtmlCode .= "Error";
    }
} else {
    $HtmlCode .= "Error";
}
echo $HtmlCode;
// echo json_encode($updetails);
?>