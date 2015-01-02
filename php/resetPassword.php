<?php
	require_once('functions.php');
	$email = decrypt2($_GET['enc']);
	$arr = explode(" ", $email);
	$email = $arr[0];
	$time = intval($arr[1]);
	$sql = "select * from member where email='" . mysql_real_escape_string($email) . "';";
	$res = mysql_fetch_array(mysql_query($sql));
	if(empty($res) || time() - $time > 1800) echo 'Link has expired, please request a reset again.';
	else
	{
		echo 'Resetting password for ' . $email . '<br />';
?>
		<form method="POST" name="reset" action="doResetPassword.php">
			<input type="hidden" name='email' value="<?php echo $email; ?>" />
			Enter new password: <input type="password" name="p1" /> <br />
			Reenter new password: <input type="password" name="p2" /> <br />
			<input type="submit" value="Reset Password" />
		</form>
<?php
	}
?>
