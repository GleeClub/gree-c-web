<?php
require_once('functions.php');
//$name = mysql_real_escape_string($_POST['name']);
if (! isset($_COOKIE['email']))
{
	echo "You must be logged in to view minutes.";
	exit(0);
}
echo todoBlock($_COOKIE['email'], ($_POST['form'] == 'true') ? true : false, ($_POST['list'] == 'true') ? true : false);
?>
