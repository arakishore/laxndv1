<?php
class logsProcessor
{
    function writeJsonS3($filename, $logStorePath, $logData, $activityAttribute)
    {
        $year = date("Y");
        $month = date("m");
        $date = date("d");
        $hour = date("h");
        
        foreach ($activityAttribute as $key => $value) {
            $logData[$key] = $value;
        }
        
        $URL = $logStorePath . $year . "/" . $month . "/" . $date . "/" . $hour . "_OClock_" . $filename;
        // check for file exists or not
        $fileContent = uploadFileContentOnS3($URL);
        
        if (noError($fileContent['errCode'])) {
            // file not found, create file and write logs
            $tempArray[] = $logData;
            $jsonData = json_encode($tempArray, JSON_PRETTY_PRINT);
            $uploadS3 = appendLogsFileS3($URL,$jsonData);
        } else {
            // file found, append the logs
            $tempArray = json_decode($fileContent['errMsg'], true);
            $tempArray[] = $logData;
            $jsonData1 = json_encode($tempArray, JSON_PRETTY_PRINT);
            $uploadS3 = appendLogsFileS3($URL,$jsonData1);
        }
    }

    function writeXML($filename, $logStorePath, $logData, $activityAttribute)
    {
        $year = date("Y");
        $month = date("m");
        $date = date("d");
        $hour = date("h");

        if ($activityAttribute["path"] != 'content') {
            $logDir = "../logs/".$logStorePath . $year . "/" . $month . "/" . $date;
        } else {
            $activityAttribute= $activityAttribute['activity'];
            $logDir = "../../logs/".$logStorePath . $year . "/" . $month . "/" . $date;
        }
       
        if (!is_dir($logDir)) {			
            mkdir($logDir, 0777, true);
        }else{
			//echo "did not make";
		}
        
        $URL = $logDir. "/" . $hour . "_OClock_" . $filename;
        if (!file_exists($URL)) {
            $method = (file_exists($URL)) ? 'a' : 'w';
            $myfile = fopen($URL, $method);
            fwrite($myfile, "<root>\n</root>");
            fclose($myfile);
        } else {
			//echo "able to open file";
		}
        
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($URL);

        $activity = $xml->addChild('activity');
        foreach ($activityAttribute as $key => $value) {
            $activity->addAttribute($key, $value);
        }

        foreach ($logData as $key => $value) {
            foreach ($value as $keys => $dvalue) {
                if (gettype($dvalue) != "array") {
                    $resultData = $activity->addChild($key, $dvalue);
                } else if(gettype($dvalue) == "array") {
                    foreach ($dvalue as $keyss => $valuedata) {
                        $resultData->addAttribute($keyss, $valuedata);
                    }
                }
            }
        }

        $xml->asXML($URL);
    }

    function writeJSON($filename, $logStorePath, $logData, $activityAttribute)
    {
        $year = date("Y");
        $month = date("m");
        $date = date("d");
        $hour = date("h");

        $logDir = $logStorePath . $year . "/" . $month . "/" . $date;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $URL = $logStorePath . $year . "/" . $month . "/" . $date . "/" . $hour . "_OClock_" . $filename;
        
        foreach ($activityAttribute as $key => $value) {
            $logData[$key] = $value;
        }
        
        if (file_exists($URL)) {
            $fh = fopen($URL, 'r+');
            $inp = file_get_contents($URL);
            $tempArray = json_decode($inp, true);
            $tempArray[] = $logData;
            $jsonData = json_encode($tempArray, JSON_PRETTY_PRINT);
        } else {
            $fh = fopen($URL, 'w');
            $tempArray[] = $logData;
            $jsonData = json_encode($tempArray, JSON_PRETTY_PRINT);
        }

        fwrite($fh, $jsonData);
        fclose($fh);
    }
}