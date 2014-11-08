<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
if (! isset($_COOKIE['email']) || ! isOfficer($_COOKIE['email'])) echo "0";
else echo "1";
?>
