<?php
session_start();
session_destroy();

$redirectURL = "../views/login/";

print("<script>");
print("window.location='" . $redirectURL . "'");
print("</script>");

exit;
?>