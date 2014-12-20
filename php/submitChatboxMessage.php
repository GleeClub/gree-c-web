<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

$message = $_POST["message"];
$message = mysql_real_escape_string($message);

$sql="INSERT INTO `chatboxMessage` ( `sender` , `timeSent` , `contents` , `messageID` ) 
VALUES (
'$userEmail', NOW( ) , '$message', NULL
);";
//echo $sql;
mysql_query($sql);

?>