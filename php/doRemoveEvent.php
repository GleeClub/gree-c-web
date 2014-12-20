<?php
require_once('functions.php');

if(isset($_POST['eventNo'])) {
  	$eventNo = mysql_real_escape_string($_POST['eventNo']);
    $sql = "DELETE FROM `event` WHERE `eventNo` = $eventNo LIMIT 1";
  	mysql_query($sql);
  	echo "";
}

?>