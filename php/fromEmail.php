<?php
require_once('functions.php');

$host=$SQLhost; // Host name
$username=$SQLusername; // Mysql username
$password=$SQLpassword; // Mysql password
$db_name=$SQLcurrentDatabase; // Database name
$tbl_name="member"; // Table name

// Connect to server and select databse.

$user = $_COOKIE['email'];
if (! isset($user)) die("DENIED");


$event = mysql_real_escape_string($_GET['id']);
$attend = '';
if ($_GET['attend'] == 'true') $attend = true;
else if ($_GET['attend'] == 'false') $attend = false;
else die("BAD_REQUEST");
$shouldAttend = 1;

$result= mysql_fetch_array(mysql_query("select * from `event` where `eventNo` = '$event'"));
if(! ($result['type'] == 3 || $result['type'] == 4) || (strtotime($result["callTime"]) - time()) < 86400) die("BAD_EVENT");

mysql_query("update `attends` set `shouldAttend` = '$attend', `confirmed` ='1' where `memberID` = '$user' AND `eventNo` = '$event'");
header("/buzz/index.php#event:$event");
?>

