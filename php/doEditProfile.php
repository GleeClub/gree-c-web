<?php
require_once('functions.php');
$sql = "update member set ";
$oldEmail = getuser();
$useremail = getuser();
$default = 1;
$count = 0;

if (isset($_POST['user']))
{
	if (! isOfficer($useremail)) die("You do not have permission to change someone else's settings.");
	$oldEmail = $_POST['user'];
}

$required = array('firstName', 'lastName', 'email', 'phone', 'passengers', 'onCampus', 'major', 'hometown');

foreach ($required as $field) if (! isset($_POST[$field]) || $_POST[$field] == '') die("Missing value for property \"$field\".");

$restricted = array('position', 'tieNum');

if (! isOfficer($useremail)) foreach ($restricted as $field) if (isset($_POST[$field])) die("Permission denied to set property \"$field\".");

foreach($_POST as $key => $value)
{
	$value = mysql_real_escape_string($value);
	switch($key)
	{
		case 'email':
			if($value == $oldEmail) break; //Not updating email, don't worry about it
			$email = mysql_real_escape_string($value);
			$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
			if (! preg_match($validEmail, $email)) die("Invalid email");
			$result = mysql_fetch_array(mysql_query("select * from member where email='$email'"));
			if (! empty($result)) die("That email address is already in use");
			$sql = $sql . "email='$value', ";
			break;
		case 'password':
			$pass = $value;
			if ($value=='') break; //User is not updating password
			$default=0;
			break;
		case 'passwordCheck':
			if ($pass == '') break; //User is not updating password
			if ($value == '') "Password check field is empty";
			if (strcmp($pass, $value) != 0) die("Password entries did not match");
			$p = md5($value);
			$sql = $sql . "password='$p', ";
			break;
		case 'phone':
			$validPhone = "/[0-9]{10}/";
			if (! preg_match($validPhone, $value)) die("Invalid phone number (proper format is just 10 digits)");
			$sql = $sql . "phone='$value', ";
			break;
		case 'passengers':
			$validNumber = "/[0-9]{1}/";
			if (! preg_match($validNumber, $value)) die("Invalid number of passengers (must be an integer, 0 if you don't have a car)");
			$sql = $sql . "passengers='$value', ";
			break;
		case 'registration':
			if (! mysql_query("update `activeSemester` set `enrollment` = '$value' where `member` = '$oldEmail' and `semester` = '$CUR_SEM'")) die("Error: " . mysql_error());
			break;
		case 'firstName':
		case 'prefName':
		case 'lastName':
		case 'section':
		case 'picture':
		case 'onCampus':
		case 'location':
		case 'about':
		case 'major':
		case 'minor':
		case 'techYear':
		case 'hometown':
		case 'gChat':
		case 'twitter':
		case 'gatewayDrug':
		case 'conflicts':
		case 'position':
		case 'sectional':
		case 'tieNum':
			$sql = $sql . "$key='$value', ";
			break;
		case 'user':
			break;
		default:
			die("Unknown property \"$key\".");
	}
	$count++;
}
	$sql = preg_replace('/, $/', ' ', $sql);
	$sql = $sql . "where email='$oldEmail'";
	mysql_query("BEGIN");  //Start transaction
	$result = mysql_query($sql);
	//$sql = "insert ignore into attends (memberID, eventID) select '$email', name from event where not(type=2);"; // FIXME Do we need to refresh event attendance when profile is modified?
	//$result1 = mysql_query($sql);
	if($result)
	{
		if($useremail == $oldEmail) setcookie('email', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $email ? $email : $oldEmail, MCRYPT_MODE_ECB)), time() + 60*60*24*120, '/', false, false);
		mysql_query("COMMIT");  //Things went ok, commit transaction
		echo "OK";
	}
	else
	{
		mysql_query("ROLLBACK"); //Something went wrong, so roll back changes.
		echo "Couldn't commit changes in database.";
	}
?>
