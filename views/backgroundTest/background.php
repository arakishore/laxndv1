<?php
file_put_contents("testprocesses.php",$argv[1]."--background start time = " . time() . "\n", FILE_APPEND);
sleep(10);
file_put_contents("testprocesses.php","background end time = " . time() . "\n", FILE_APPEND);
?>