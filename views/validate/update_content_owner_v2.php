<?php
/*
File - views/upload-mis-files.php
view file that shows the form to upload MIS and Trial Balance excel files. Form submits to controller/mis/upload/index.php
The error/success messages are also displayed here after form submission
 */

//Manage distributors view page
session_start();

//prepare for request
//include necessary helpers
require_once '../../config/config.php';

//check if session is active
$sessionCheck = checkSession();

//include some more necessary helpers
require_once __ROOT__ . '/config/dbUtils.php';
require_once __ROOT__ . '/config/errorMap.php';
require_once __ROOT__ . '/config/logs/logsProcessor.php';
require_once __ROOT__ . '/config/logs/logsCoreFunctions.php';
require_once __ROOT__ . '/vendor/league/csv/src/ByteSequence.php';
require_once __ROOT__ . '/vendor/league/csv/src/AbstractCsv.php';
require_once __ROOT__ . '/vendor/league/csv/src/Stream.php';
require_once __ROOT__ . '/vendor/league/csv/src/Reader.php';
require_once __ROOT__ . '/vendor/league/csv/src/Statement.php';
require_once __ROOT__ . '/vendor/league/csv/src/MapIterator.php';
require_once __ROOT__ . '/vendor/league/csv/src/ResultSet.php';

require_once __ROOT__ . '/vendor/league/csv/src/functions.php';

//require __ROOT__ . '/vendor/autoload.php';

use League\Csv\Reader;
use League\Csv\Statement;

//include necessary models
require_once __ROOT__ . '/model/user/userModel.php';
require_once __ROOT__ . '/model/distributor/distributorModel.php';

//Connection With Database
$conn = createDbConnection($host, $dbUsername, $dbPassword, $dbName);
if (!noError($conn)) {
//error connecting to DB
    $returnArr["errCode"] = 1;
    $returnArr["errMsg"] = getErrMsg(1) . $conn["errMsg"];
} else {
    $conn = $conn["errMsg"];

    $reportMonthYear = "";
    $table_name = "";
    $filename = "";

    if (isset($_POST["reportMonthYear"])) {
        $reportMonthYear = cleanQueryParameter($conn, cleanXSS($_POST["reportMonthYear"]));
    }

    if (isset($_POST["table_name"])) {
        $table_name = cleanQueryParameter($conn, cleanXSS($_POST["table_name"]));
    }

    if (isset($_POST["filename"])) {
        $filename = cleanQueryParameter($conn, cleanXSS($_POST["filename"]));
    }

    $returnArr = array();
//get the user info
    $email = $_SESSION['userEmail'];
//initialize logs
 
if(isset($_FILES['assigned_content_owner']['tmp_name'])){
    $logsProcessor = new logsProcessor();
    $initLogs = initializeJsonLogs($email);

    $reader = Reader::createFromPath($_FILES['assigned_content_owner']['tmp_name']);
    $reader->setHeaderOffset(0);

    $header = $reader->getHeader(); //returns the CSV header record

    $reader->setHeaderOffset(0);

    $records = (new Statement())->process($reader);

    if ($table_name != "") {

        foreach ($records->getRecords() as $key => $record) {

//                print_r($record);

            $content_owner = $record['content_owner'];

            if ($_POST["type"] == "youtube_labelengine_report") {

                $assetID = $record['assetID'];
                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  assetID='" . $assetID . "' and content_owner IS NULL";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }

            if ($_POST["type"] == "youtuberedmusic_video_report") {

                $assetID = $record['assetID'];
                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  assetID='" . $assetID . "' and content_owner IS NULL";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }

            if ($_POST["type"] == "youtube_video_claim_report") {

                $assetID = $record['assetID'];
                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  assetID='" . $assetID . "' and content_owner IS NULL";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }
            if ($_POST["type"] == "youtube_red_music_video_finance_report") {

                $assetID = $record['assetID'];
                  $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  assetID='" . $assetID . "' and content_owner IS NULL";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);

            }

            if ($_POST["type"] == "audio_applemusic") {

                $Label = $record['Label'];

                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  Label='" . $Label . "' and content_owner IS NULL";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }
            if ($_POST["type"] == "audio_itune") {

                $Label = $record['Label'];

                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  Label='" . $Label . "' and content_owner IS NULL ";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }
            if ($_POST["type"] == "audio_gaana") {

                $Sub_vendor_Name = $record['Sub_vendor_Name'];

                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  Sub_vendor_Name='" . $Sub_vendor_Name . "' and content_owner IS NULL ";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }
            if ($_POST["type"] == "audio_saavan") {

                $Label = $record['Label'];

                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  Label='" . $Label . "' and content_owner IS NULL ";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }

            if ($_POST["type"] == "audio_spotify") {
                //

                $Label = $record['Label'];

                $updateUserQuery = "UPDATE {$table_name} SET content_owner='" . $content_owner . "' WHERE  Label='" . $Label . "' and content_owner IS NULL ";
                $updateUserQueryResult = runQuery($updateUserQuery, $conn);
            }

        }
    }

}

} //close db conn
