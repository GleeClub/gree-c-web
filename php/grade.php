<?php
require_once('functions.php');

if (! isUber(getuser())) die("DENIED");
echo attendance(mysql_real_escape_string($_POST['member']), 0);
?>
