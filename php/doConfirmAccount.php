<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = mysql_real_escape_string($_COOKIE['email']);
if (! mysql_query("update `member` set confirmed=1 where email='$userEmail'")) die("Error confirming member");
if (! mysql_query("insert ignore into `attends` (`memberID`, `eventNo`) select '$userEmail', `eventNo` from `event` where `semester` = '$CUR_SEM' and not(`type` = 2)")) die("Error setting attendance relationships");
echo "Success"
?>
