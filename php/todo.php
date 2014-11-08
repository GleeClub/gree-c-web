<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
//$name = mysql_real_escape_string($_POST['name']);
if (! isset($_COOKIE['email']))
{
	echo "You must be logged in to view minutes.";
	exit(0);
}
echo todoBlock($_COOKIE['email'], ($_POST['form'] == 'true') ? true : false, ($_POST['list'] == 'true') ? true : false);
?>
