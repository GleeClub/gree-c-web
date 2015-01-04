<?php
	require_once('./functions.php');

	echo "<html>";

	$sql = "insert ignore into member (firstName, prefName, lastName, section, email, password, phone, picture, registration, passengers, onCampus, location, about, major, techYear, clubYear, gChat, twitter, gatewayDrug, conflicts) values(";

	$default = 1;
	$missingField = 0;
	$error = 0;
	$count = 0;
	foreach($_GET as $value)
	{
		if ($count == 0 || $count == 2 || $count == 4 || $count == 5 || $count == 6 || $count == 7 || $count == 9 || $count == 10 || $count == 11)
		{
			if($value=='')
			{
				$missingField = 1;
				break;
			}

		}
		switch($count)
		{
			case 4: 				//email
				$email = mysql_real_escape_string($value);
				$_SESSION['email'] = $email;
				$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
				if(!preg_match($validEmail, $email))
				{
					echo "<p id='title'>Invalid email.  Go back and try again.</p>";
					$error = 1;
				}
				$result = mysql_fetch_array(mysql_query("select * from member where email='$email'"));
				if(!empty($result))
				{
					echo "<p id='title'>That email address is already in use.  Go back and try again.</p>";
					$error = 1;
				}
				break;
			case 5: 				//password 1
				$default = 0;
				$pass = $value;
				break;
			case 6: 				//password 2
				if(strcmp($pass, $value)!=0)
				{
					echo "<p id='title'>Password entries did not match.  Go back and try again.</p>";
					$error = 1;
				}
				break;
			case 7: 				//phone
				$validPhone = "/[0-9]{10}/";
				if(!preg_match($validPhone, $value))
				{
					echo "<p id='title'>Invalid phone number (proper format is just 10 digits).  Go back and try again.</p>";
					$error = 1;
				}
				break;
			case 10:				//passengers
				$validNumber = "/[0-9]{1}/";
				if(!preg_match($validNumber, $value))
				{
					echo "<p id='title'>Invalid number of passengers (must be an integer, 0 if you don't have a car).  Go back and try again.</p>";
					$error = 1;
				}
				break;
			case 0: 				//firstName
			case 1: 				//prefName
			case 2: 				//lastName
			case 3: 				//section
			case 8: 				//picture
			case 9: 				//registration
			case 11:				//onCampus
			case 12:				//location
			case 13:				//about
			case 14:				//major
			case 15:				//techYear
			case 16:				//clubYear
			case 17:				//gChat
			case 18:				//twitter
			case 19:				//gatewayDrug
			case 20:				//conflicts
			default:
				break;
		}
		if($default)
		{
			if($count == 6) //Encrypt the password using md5
				$sql = $sql."md5('".mysql_real_escape_string($value)."'), ";
			else
				$sql = $sql."'".mysql_real_escape_string($value)."', ";
		}

		$default = 1;
		$count++;
	}
	$sql = substr_replace($sql, ");", strlen($sql)-2, 3);

	if($count<20 || $missingField)
	{
		echo "<p id='title'>You didn't fill out all of the required fields.  Go back and try again.</p>";
		$error = 1;
	}
	if(! $error)
	{
		$result0 = mysql_query($sql);
		$sql = "insert ignore into `attends` (`memberID`, `eventNo`) select '$email', `eventNo` from `event` where `semester` = '$CUR_SEM' and not(`type` = 2)";
		$result1 = mysql_query($sql);
		if($result0 && $result1) echo "<p>Success</p><META HTTP-EQUIV=\"refresh\" content=\"0;URL=http://mensgleeclub.gatech.edu/buzz/\">";
	}

	echo "</html>";

?>
