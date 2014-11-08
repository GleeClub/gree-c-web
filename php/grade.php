<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

$type = positionFromEmail($_COOKIE['email']);
if ($type != "President" && $type != "Instructor" && $type != "VP")
{
	echo "DENIED";
	exit(1);
}
echo attendance(mysql_real_escape_string($_POST['member']), 0);
?>
