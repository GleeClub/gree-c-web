<?php
require_once('functions.php');
if (! $USER)
{
	echo "You must be logged in to view minutes.";
	exit(0);
}
echo todoBlock($USER, ($_POST['form'] == 'true') ? true : false, ($_POST['list'] == 'true') ? true : false);
?>
