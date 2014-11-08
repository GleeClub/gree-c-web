<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
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