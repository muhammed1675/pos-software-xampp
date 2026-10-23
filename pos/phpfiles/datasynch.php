<?php
set_time_limit(200000);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

include "connectdatabase.php";

if (empty($pos_config['sync_enabled'])) {
    echo "data:{\"type\":\"Status\", \"message\":\"Error: online sync is not set up on this server (see phpfiles/config.php)\"}\n\n";
    exit();
}
$TablesDetails = array();
$Staff = "";
if (isset($_GET['data'])) {
    $TablesDetails = json_decode($_GET['data'], true);
    $Tables = $TablesDetails['SelectedTables'];
    $Staff = $TablesDetails['Staff'];
    $HtmlCode = "";
    $ddb = strtolower(date('M') . "_" . date('Y'));
    $TableToSync = array();

    function is_connected()
    {
        global $pos_config;
        $connected = @fsockopen($pos_config['sync_remote']['host'], $pos_config['sync_remote']['port'], $errno, $errstr, 8);
        if ($connected) {
            return true;
        } else {
            return false;
        }
    }
    function SendUpdate($type, $msg)
    {
        echo "data:{\"type\":\"$type\", \"message\":\"$msg\"}\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        // sleep(4);
    }

    if (is_connected()) {

        foreach ($Tables as $tab) {
            switch ($tab) {
                case "Sales Report":
                    $TableToSync = [...$TableToSync, $ddb . "_receiptdetails", $ddb . "_salesrecords", $ddb . "_expenditures"];
                    break;
                case "Supply Report":
                    $TableToSync = [...$TableToSync, $ddb . "_allgoodssupplied"];
                    break;
                case "Inventory":
                    $TableToSync = [...$TableToSync, "allgoodsproperties"];
                    break;
                case "Debtors Report":
                    $TableToSync = [...$TableToSync, "debtpaymentrecords", "debtreceiptdetails"];
                    break;

            }
        }

        // To calculate the percentage done
        $TotalRows = 0;
        $sourceDB = new mysqli($pos_config['db_host'], $pos_config['db_user'], $pos_config['db_pass'], $pos_config['db_name'], $pos_config['db_port']);

        $targetDB = new mysqli($pos_config['sync_remote']['host'], $pos_config['sync_remote']['user'], $pos_config['sync_remote']['pass'], $pos_config['sync_remote']['name'], $pos_config['sync_remote']['port']);

        if ($sourceDB->connect_error || $targetDB->connect_error) {
            die("Connection failed: " . $sourceDB->connect_error . ' / ' . $targetDB->connect_error);
        }

        function CheckSourceTableForExistence($tablename, $sourceDB)
        {

            $Status = false;
            $tableExistsQuery = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$tablename' ";
            if ($sourceDB->query($tableExistsQuery)->num_rows > 0) {
                $Status = true;
            }

            return $Status;
        }

        // Get the total number of rows in the source table for progress tracking
        $totalRows = 0;
        foreach ($TableToSync as $ThisTable) {
            $sourceTable = $ThisTable;

            if (CheckSourceTableForExistence($sourceTable, $sourceDB)) {
                $query = "SELECT COUNT(*) FROM $sourceTable";
                $result = $sourceDB->query($query);
                $totalRows += $result->fetch_row()[0];
                $result->close();
            }
        }

        $OverAllProgress = 0;
        foreach ($TableToSync as $ThisTable) {

            $sourceTable = $ThisTable;
            $targetTable = $ThisTable;
            if (CheckSourceTableForExistence($sourceTable, $sourceDB)) {

// Retrieve the structure of the source table
                $query = "SHOW COLUMNS FROM $sourceTable";
                $structure = $sourceDB->query($query);

                if ($structure) {
                    // Create the CREATE TABLE statement
                    $createTableQuery = "CREATE TABLE $targetTable (";
                    while ($row = $structure->fetch_assoc()) {
                        $createTableQuery .= $row['Field'] . ' ' . $row['Type'] . ', ';
                    }
                    $createTableQuery = rtrim($createTableQuery, ', ') . ')';

                    // Execute the CREATE TABLE statement in the target database
                    if ($targetDB->query($createTableQuery)) {
                        // echo "Table created in the target database with the same structure as the source table.";
                    } else {
                        // echo "Error creating table: " . $targetDB->error;
                    }
                } else {
                    // echo "Error fetching source table structure: " . $sourceDB->error;
                }

                // Empty the target table
                $emptyQuery = "TRUNCATE TABLE $targetTable"; // or "DELETE FROM $targetTable" for a more flexible approach
                if ($targetDB->query($emptyQuery)) {
                    // echo "Table in the target database has been emptied.";
                } else {
                    // echo "Error emptying the table: " . $targetDB->error;
                }

                $query = "SELECT COUNT(*) FROM $sourceTable";
                $result1 = $sourceDB->query($query);
                $TableRows = $result1->fetch_row()[0];
                $result1->close();
                $remaining = 0;
                $chunkSize = 100; // Adjust the chunk size as needed
                // To check if chunkSize is greater than the total rows in the table

                if ($chunkSize > $TableRows) {
                    $chunkSize = $TableRows;
                }

                $progress = 0;
                $startCopying = true;
// Copy data from source table to target table in chunks
                $query = "SELECT * FROM $sourceTable LIMIT $chunkSize";
                while ($startCopying) {
                    if ($chunkResult = $sourceDB->query($query)) {
                        $rowsInserted = 0;
                        while ($row = $chunkResult->fetch_assoc()) {
                            // Insert each row into the target table
                            $query = "INSERT INTO $targetTable VALUES (";
                            foreach ($row as $value) {
                                $query .= "'" . $targetDB->real_escape_string((string)$value) . "',";
                            }
                            $query = rtrim($query, ',') . ")";

                            if ($targetDB->query($query)) {
                                $rowsInserted++;
                            }
                        }

                        $progress = $progress + $rowsInserted;
                        $OverAllProgress = $OverAllProgress + $rowsInserted;
                        // Calculate and display progress
                        $ProgressValue = floor((floatval($OverAllProgress) / floatval($totalRows)) * 100);

                        SendUpdate("Progress", strval($ProgressValue));

                        if ($progress < $TableRows) {

                            $query = "SELECT * FROM $sourceTable LIMIT $chunkSize OFFSET $progress";
                        } else {
                            $startCopying = false;
                        }
                    }
                }
            }

        }
        $sourceDB->close();

        date_default_timezone_set('Africa/Lagos');
        $date = date("M d, Y h:i a");
        foreach ($Tables as $tab) {
            $updateQuery = "UPDATE updatetrack SET lastupdate = '" . $date . "', Staff = '" . $Staff . "' WHERE reportname = '" . $tab . "'";
            if ($targetDB->query($updateQuery)) {
                $HtmlCode .= "Success";
            } else {
                $HtmlCode .= "Error";
            }

        }

        $targetDB->close();

    } else {
        $HtmlCode .= "Error error ERROR";
    }

    SendUpdate("Status", $HtmlCode);
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    exit();
}
