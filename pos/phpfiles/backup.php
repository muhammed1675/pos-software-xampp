<?php
// Plain-text SQL backup of the whole POS database (the "Back - Up" menu downloads this as a .sql file).
header("Access-Control-Allow-Origin: *");
header('Content-Type: text/plain; charset=utf-8');
include "connectdatabase.php";
set_time_limit(0);

echo "-- POS backup " . date("Y-m-d H:i:s") . "\n";
echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

$tables = array();
$res = mysqli_query($conn, "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
while ($res && ($row = mysqli_fetch_row($res))) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $q = "`" . str_replace("`", "``", $table) . "`";
    $create = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $q"));
    echo "DROP TABLE IF EXISTS $q;\n" . $create[1] . ";\n\n";

    $data = mysqli_query($conn, "SELECT * FROM $q", MYSQLI_USE_RESULT);
    if (!$data) {
        continue;
    }
    $batch = array();
    while ($row = mysqli_fetch_row($data)) {
        $vals = array();
        foreach ($row as $v) {
            $vals[] = $v === null ? "NULL" : "'" . mysqli_real_escape_string($conn, $v) . "'";
        }
        $batch[] = "(" . implode(",", $vals) . ")";
        if (count($batch) >= 100) {
            echo "INSERT INTO $q VALUES\n" . implode(",\n", $batch) . ";\n";
            $batch = array();
        }
    }
    if (count($batch) > 0) {
        echo "INSERT INTO $q VALUES\n" . implode(",\n", $batch) . ";\n";
    }
    mysqli_free_result($data);
    echo "\n";
}
echo "SET FOREIGN_KEY_CHECKS=1;\n";
