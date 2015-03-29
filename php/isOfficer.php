<?php
require_once('functions.php');
if (! getuser() || ! isOfficer(getuser())) echo "0";
else echo "1";
?>
