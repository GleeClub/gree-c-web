<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_POST['eventNo'])) {
  	$eventNo = mysql_real_escape_string($_POST['eventNo']);
    $sql = "DELETE FROM `event` WHERE `eventNo` = $eventNo LIMIT 1";
  	mysql_query($sql);
  	echo "";
}

?>