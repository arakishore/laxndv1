<?php

class xmlProcessor
{
   
    function writeXML($filename, $logStorePath, $xml_data, $activity_attribute)
    {

        $year = date("Y");
        $month = date("m");
        $date = date("d");
        $hour = date("h");

        if($activity_attribute["path"] != 'content'){
             $logDir = "../logs/".$logStorePath . $year . "/" . $month . "/" . $date;
        }else{
            $activity_attribute= $activity_attribute['activity'];
             $logDir = "../../logs/".$logStorePath . $year . "/" . $month . "/" . $date;
        }
        
       
        if (!is_dir($logDir)) {
			
            mkdir($logDir, 0777, true);
        }else{
			//echo "did not make";
		}
        
         $URL = $logDir. "/" . $hour . "_OClock_" . $filename;
//exit;
        if (!file_exists($URL)) {
			//echo "unable to open file";
            $method = (file_exists($URL)) ? 'a' : 'w';
            $myfile = fopen($URL, $method); //or die("Unable to open file!");
            fwrite($myfile, "<root>\n</root>");
            fclose($myfile);
        }else{
			//echo "able to open file";
		}
//unset($array[$indexSpam]);
        libxml_use_internal_errors(true);

        $xml = simplexml_load_file($URL);

        $activity = $xml->addChild('activity');
        foreach ($activity_attribute as $key => $value) {
            $activity->addAttribute($key, $value);

        }

        foreach ($xml_data as $key => $value) {
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
}

?>