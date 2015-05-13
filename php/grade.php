<?php
require_once('functions.php');

$type = positionFromEmail(getuser());
if ($type != "President" && $type != "Instructor" && $type != "Vice President")
{
	echo "DENIED";
	exit(1);
}
echo attendance(mysql_real_escape_string($_POST['member']), 0);
?>
