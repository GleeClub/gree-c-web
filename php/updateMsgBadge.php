<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

$i = getNumUnreadMessages(mysql_real_escape_string($_COOKIE['email']));
echo $i;
?>
