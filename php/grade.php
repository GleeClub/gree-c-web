<?php
require_once('functions.php');

$type = positionFromEmail($_COOKIE['email']);
if ($type != "President" && $type != "Instructor" && $type != "VP")
{
	echo "DENIED";
	exit(1);
}
echo attendance(mysql_real_escape_string($_POST['member']), 0);
?>
