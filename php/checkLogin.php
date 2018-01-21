<?php

require_once('functions.php');

// username and password sent from form 
$myusername = mysql_real_escape_string($_POST['email']);
$mypassword = mysql_real_escape_string($_POST['password']);


$sql="SELECT * FROM `member` WHERE email='$myusername' and password=md5('$mypassword')";
$result=mysql_query($sql);

// Mysql_num_row is counting table row
$count = mysql_num_rows($result);
// If result matched $myusername and $mypassword, table row must be 1 row

if ($count != 1) die("Wrong email or password");
// Register $myusername, $mypassword and redirect to file "login_success.php"
//session_register("myusername");
setcookie('email', cookie_string($myusername), time() + 60*60*24*120, '/', false, false);
if (! isset($_COOKIE['choir']))
{
	$choir = "glee"; # TODO Use stored last choir in database to set this
	setcookie('choir', $choir, time() + 60*60*24*120, '/', false, false);
}
echo "OK";
?>
