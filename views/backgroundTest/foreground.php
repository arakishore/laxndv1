<?php

ini_set("display_errors",1);
error_reporting(E_ALL);

echo "<pre>loading page</pre>";

function run_background_process()
{
    file_put_contents("testprocesses.php","foreground start time = " . time() . "\n");
    echo "<pre>  foreground start time = " . time() . "</pre>";

    // output from the command must be redirected to a file or another output stream 
    // http://ca.php.net/manual/en/function.exec.php

    exec("php background.php xyzs > testoutput.php 2>&1 & echo $!", $output);

    echo "<pre>  foreground end time = " . time() . "</pre>";
    file_put_contents("testprocesses.php","foreground end time = " . time() . "\n", FILE_APPEND);
    return $output;
}

echo "<pre>calling run_background_process</pre>";

$output = run_background_process();

echo "<pre>output = "; print_r($output); echo "</pre>";
echo "<pre>end of page</pre>";

?>