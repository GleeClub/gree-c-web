<?php
require_once('functions.php');

$host=$SQLhost; // Host name 
$username=$SQLusername; // Mysql username 
$password=$SQLpassword; // Mysql password 
$db_name=$SQLcurrentDatabase; // Database name 
$tbl_name="member"; // Table name

// Connect to server and select databse.

if (! isOfficer($_COOKIE['email'])) die("Access denied");
setcookie('email', $_POST['user'], time()+60*60*24*120, '/', false, false);
header("Location: ../");
?>

