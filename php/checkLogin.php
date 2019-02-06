<?php

require_once('functions.php');

// username and password sent from form
$myusername = $_POST['email'];
$mypassword = $_POST['password'];


if (query("select * from `member` where `email` = ? and `password` = md5(?)", [$myusername, $mypassword], QCOUNT) != 1) err("Wrong email or password");
// Register $myusername, $mypassword and redirect to file "login_success.php"
//session_register("myusername");
setcookie('email', encrypt2($myusername), time() + 60*60*24*120, '/', false, false);
if (! isset($_COOKIE['choir']))
{
	$choir = "glee"; // TODO Use stored last choir in database to set this
	setcookie('choir', $choir, time() + 60*60*24*120, '/', false, false);
}
echo "OK";
?>
