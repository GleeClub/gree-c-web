<?php

require_once('variables.php');

// username and password sent from form 
$myusername = mysql_real_escape_string($_POST['email']);
$mypassword = mysql_real_escape_string($_POST['password']);


//debug stuff
/*if(!isset($_POST['email'])){
	$myusername=$_GET['email']; 
	$mypassword=$_GET['password'];
}

setcookie('email', $myusername, time()-3600);
echo $myusername."<br />";
echo $mypassword."<br />";
setcookie('email', $myusername, time()+60*60*24*120, '/', false, false);
print_r($_COOKIE);
echo getuser();*/

$sql="SELECT * FROM `member` WHERE email='$myusername' and password=md5('$mypassword')";
$result=mysql_query($sql);

// Mysql_num_row is counting table row
$count=mysql_num_rows($result);
// If result matched $myusername and $mypassword, table row must be 1 row

if ($count == 1)
{
	// Register $myusername, $mypassword and redirect to file "login_success.php"
	//session_register("myusername");
	setcookie('email', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $myusername, MCRYPT_MODE_ECB)), time() + 60*60*24*120, '/', false, false);
	header("Location: ../");
}
else echo "Wrong Username or Password";
?>
