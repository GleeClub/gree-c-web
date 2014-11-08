<?php
	require_once('php/variables.php');
	require_once('php/functions.php');
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	$email = decrypt2($_GET['enc']);
	$arr = explode(" ", $email);
	$email = $arr[0];
	$time = intval($arr[1]);
	$sql = "select * from member where email='" . mysql_real_escape_string($email) . "';";
	$res = mysql_fetch_array(mysql_query($sql));
	if(empty($res) || time() - $time > 1800) {
		echo 'Link has expired, please request a reset again.';
	} else {
		echo 'Resetting password for ' . $email . '<br />';
		?>
		<form method="POST" name="reset" action="php/doResetPassword.php">
			<input type="hidden" name='email' value="<?php echo $email; ?>" />
			Enter new password: <input type="password" name="p1" /> <br />
			Reenter new password: <input type="password" name="p2" /> <br />
			<input type="submit" value="Reset Password" />
		</form>
		<?php
	}
?>