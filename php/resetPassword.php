<?php
	require_once('functions.php');
	$email = decrypt2($_GET["code"]);
	$arr = explode(" ", $email);
	$email = $arr[0];
	$time = intval($arr[1]);
	$res = query("select * from `member` where `email` = ?", [$email], QONE);
	if (! $res || time() - $time > 1800) echo "That link has expired.  Please request another request.";
	else
	{
		echo "Resetting password for $email. <br />";
?>
		<form method="POST" name="reset" action="doResetPassword.php">
			<input type="hidden" name='token' value="<?php echo $_GET["code"]; ?>" />
			Enter new password: <input type="password" name="p1" /> <br />
			Reenter new password: <input type="password" name="p2" /> <br />
			<input type="submit" value="Reset Password" />
		</form>
<?php
	}
?>
