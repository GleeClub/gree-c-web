<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$announceNo = $_POST['announceNo'];
$sql = "UPDATE `announcement` SET `archived` = 1 WHERE `announcementNo`=" . mysql_real_escape_string($announceNo);
mysql_query($sql);
?>