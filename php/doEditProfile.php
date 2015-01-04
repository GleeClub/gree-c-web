<?php
require_once('functions.php');
$sql = "update member set ";
$oldEmail = $_COOKIE['email'];
$useremail = $_COOKIE['email'];
$default = 1;
$count = 0;

if (isset($_POST['user']))
{
	if (! isOfficer($useremail))
	{
		echo "You do not have permission to change someone else's settings.";
		exit(1);
	}
	$oldEmail = $_POST['user'];
}

$required = array('firstName', 'lastName', 'email', 'phone', 'registration', 'passengers', 'onCampus', 'major', 'hometown');

foreach ($required as $field)
{
	if (! isset($_POST[$field]) || $_POST[$field] == '')
	{
		echo "Missing value for property \"$field\".";
		exit(1);
	}
}

$restricted = array('position', 'tieNum', 'confirmed');

if (! isOfficer($useremail))
{
	foreach ($restricted as $field)
	{
		if (isset($_POST[$field]))
		{
			echo "Permission denied to set property \"$field\".";
			exit(1);
		}
	}
}

foreach($_POST as $key => $value){
	$value = mysql_real_escape_string($value);
	switch($key)
	{
		case 'email':
			if($value == $oldEmail) break; //Not updating email, don't worry about it
			$email = mysql_real_escape_string($value);
			$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
			if(!preg_match($validEmail, $email))
			{
				echo "Invalid email.";
				exit(1);
			}
			$result = mysql_fetch_array(mysql_query("select * from member where email='$email'"));
			if(!empty($result))
			{
				echo "That email address is already in use.  $value $oldEmail";
				exit(1);
			}
			$sql = $sql . "email='$value', ";
			break;
		case 'password':
			$pass = $value;
			if($value=='') break; //User is not updating password
			$default=0;
			break;
		case 'passwordCheck':
			if($pass == '') break; //User is not updating password
			if($value=='')
			{
				echo "Password check field is empty.";
				exit(1);
				break;
			}
			if(strcmp($pass, $value)!=0)
			{
				echo "Password entries did not match.  Go back and try again.";
				exit(1);
				break;
			}
			$p = md5($value);
			$sql = $sql . "password='$p', ";
			break;
		case 'phone':
			$validPhone = "/[0-9]{10}/";
			if(!preg_match($validPhone, $value))
			{
				echo "Invalid phone number (proper format is just 10 digits).  Go back and try again.";
				exit(1);
				break;
			}
			$sql = $sql . "phone='$value', ";
			break;				
		case 'passengers':
			$validNumber = "/[0-9]{1}/";
			if(!preg_match($validNumber, $value))
			{
				echo "Invalid number of passengers (must be an integer, 0 if you don't have a car).  Go back and try again.";
				exit(1);
				break;
			}
			$sql = $sql . "passengers='$value', ";
			break;
		case 'firstName':
		case 'prefName':
		case 'lastName':
		case 'section':
		case 'picture':
		case 'registration':
		case 'onCampus':
		case 'location':
		case 'about':
		case 'major':
		case 'minor':
		case 'techYear':
		case 'clubYear':
		case 'hometown':
		case 'gChat':
		case 'twitter':
		case 'gatewayDrug':
		case 'conflicts':
		case 'position':
		case 'sectional':
		case 'tieNum':
		case 'confirmed':
			$sql = $sql . "$key='$value', ";
			break;
		case 'user':
			break;
		default:
			echo "Unknown property \"$key\".";
			exit(1);
			break;
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
		if($useremail == $oldEmail) setcookie('email', $email ? $email : $oldEmail, time()+60*60*24*120, '/', false, false);
		mysql_query("COMMIT");  //Things went ok, commit transaction
		echo "OK";
	}
	else
	{
		mysql_query("ROLLBACK"); //Something went wrong, so rollback changes.
		echo "Couldn't commit changes in database.";
	}
?>
