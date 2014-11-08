<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$email = $_POST['email'];
$hc = "1234321ffeeff";
$safeEmail = mysql_real_escape_string($email);
$n = mysql_num_rows(mysql_query("select * from member where email='$safeEmail';"));
if($n) {
//To encrypt
	$now = time();
	$toenc = $email . " $now";
	$enc = encrypt2($toenc);
	$msg = "We have received a request to reset your password on Gree-C-Web.  To reset your password, <a href='" .
"http://mensgleeclub.gatech.edu/buzz/resetPassword.php?enc=" . $enc . "'>click here.</a>  If you did not request " .
"a password reset, please ignore this email.";
	$headers  = 'MIME-Version: 1.0' . "\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
	mail($email, "Gree-C-Web Password Reset", $msg, $headers);
	echo "Reset link sent to $email.";
} else {
	echo "That email was not found on the server.  Please try another email.";
}
?>
