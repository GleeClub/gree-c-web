<?php
require_once('functions.php');
if (! isset($_COOKIE['email']) || ! isOfficer($_COOKIE['email'])) echo "0";
else echo "1";
?>
