<?php

require_once('variables.php');

// username and password sent from form 
$myusername=$_POST['email']; 
$mypassword=$_POST['password'];


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
echo $_COOKIE['email'];*/

// To protect MySQL injection (more detail about MySQL injection)
/*$myusername = stripslashes($myusername);
$mypassword = stripslashes($mypassword);
$myusername = mysql_real_escape_string($myusername);
$mypassword = mysql_real_escape_string($mypassword);*/

$sql="SELECT * FROM `member` WHERE email='$myusername' and password=md5('$mypassword')";
$result=mysql_query($sql);

// Mysql_num_row is counting table row
$count=mysql_num_rows($result);
// If result matched $myusername and $mypassword, table row must be 1 row

if($count==1){
	// Register $myusername, $mypassword and redirect to file "login_success.php"
	//session_register("myusername");
	//setcookie('email', $myusername, time()+60*60*24*120, '/'); //sets cookie to expire in 120 days
	//echo "got this far<br>";
	setcookie('email', $myusername, time()+60*60*24*120, '/', false, false);
	//echo "didn't go this far<br>";
	header("Location: ../");
}
else {
	echo "Wrong Username or Password";
}
?>
