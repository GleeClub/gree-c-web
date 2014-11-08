<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$oldname = mysql_real_escape_string($_POST['oldname']);
$newname = mysql_real_escape_string($_POST['newname']);
$private = mysql_real_escape_string($_POST['private']);
$public = mysql_real_escape_string($_POST['public']);
if (! isset($_COOKIE['email']) || ! isOfficer($_COOKIE['email']))
{
	echo "UNAUTHORIZED";
	exit(1);
}
if ($oldname == "") $query = "insert into `minutes` values (curdate(), '$newname', '$private', '$public')"; // New record
else if ($newname == ".DELETE") $query = "delete from `minutes` where name = '$oldname'";
else
{
	$query = "update `minutes` set name = '$newname', private = '$private', public = '$public' where name = '$oldname'"; // Edit existing record
}
if (mysql_query($query)) echo "OK";
else echo "FAIL";
?>
