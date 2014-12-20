<?php
require_once('functions.php');
$name = mysql_real_escape_string($_POST['name']);
if (! isset($_COOKIE['email']))
{
	echo "You must be logged in to view minutes.";
	exit(0);
}
$query = "select count(public) as `n` from `minutes` where name = '$name'";
$result = mysql_fetch_array(mysql_query($query));
if ($result['n'] == 0)
{
	echo "The minutes you requested do not exist.";
	exit(1);
}
else if ($result['n'] > 1)
{
	echo "Ambiguous request.";
	exit(1);
}
if (isOfficer($_COOKIE['email']) && ! isset($_POST['public'])) $query = "select private from `minutes` where name = '$name'";
else  $query = "select public from `minutes` where name = '$name'";
$result = mysql_fetch_array(mysql_query($query));
echo $result[0];
?>
