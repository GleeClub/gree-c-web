<?php
require_once('functions.php');
if (! $USER || ! isOfficer($USER)) echo "0";
else echo "1";
?>
