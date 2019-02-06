<?php

function check($msg)
{
	global $DB;
	if (! $msg) return;
	$DB->rollback();
	err($msg);
}

function clean($val)
{
	if ($val === true) return "1";
	if ($val === false) return "0";
	if ($val === null) return "";
	return $val;
}

function updateRegistration($member, $enrollment, $section)
{
	global $CHOIR, $SEMESTER;
	if ($enrollment != "class" && $enrollment != "club" && $enrollment != "inactive") return "Invalid enrollment \"$enrollment\"";
	$wasactive = false; // 1 if the user is already marked active
	$oldsect = 0;
	$res = query("select `section` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$member, $SEMESTER, $CHOIR], QONE);
	if ($res)
	{
		$wasactive = true;
		$oldsect = $res["section"];
	}
	if ($section === null)
	{
		if (! $wasactive) return "Section is required for members who are becoming active";
		$section = $oldsect;
	}
	$err = null;
	if ($wasactive && $enrollment == "inactive") // Active member going inactive
	{
		if (! $err) $err = query("delete from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$member, $SEMESTER, $CHOIR], QERR);
	}
	else if ($wasactive && $section != $oldsect) // Active member changing section
	{
		if (! $err) $err = query("update `activeSemester` set `enrollment` = ?, `section` = ? where `member` = ? and `semester` = ? and `choir` = ?", [$enrollment, $section, $member, $SEMESTER, $CHOIR], QERR);
		if (! $err) $err = query("delete from `attends` where `memberID` = ? and `eventNo` in (select `eventNo` from `event` where `type` = 'sectional' and `choir` = ? and `semester` = ? and `section` = ?) and (select `callTime` from `event` where `event`.`eventNo` = `attends`.`eventNo`) > current_timestamp", [$member, $CHOIR, $SEMESTER, $oldsect], QERR); // Delete attends for future sectionals in old section
		if (! $err) $err = query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select ?, `defaultAttend`, '0', `eventNo` from `event` where `semester` = ? and `choir` = ? and `type` = 'sectional' and `section` = ? and `callTime` > current_timestamp", [$member, $SEMESTER, $CHOIR, $section], QERR); // Add attends for future sectionals in new section
	}
	else if ($wasactive) // Active member remaining active, possibly changing registration
	{
		if (! $err) $err = query("update `activeSemester` set `enrollment` = ?, `section` = ? where `member` = ? and `semester` = ? and `choir` = ?", [$enrollment, $section, $member, $SEMESTER, $CHOIR], QERR);
	}
	else if ($enrollment == "inactive") { } // Remaining inactive
	else // New member or inactive member becoming active
	{
		if (! $err) $err = query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`, `section`) values (?, ?, ?, ?, ?)", [$member, $SEMESTER, $CHOIR, $enrollment, $section], QERR);
		if (! $err) $err = query("insert ignore into `attends` (`memberID`, `eventNo`, `shouldAttend`, `confirmed`) select ?, `eventNo`, `defaultAttend`, '0' from `event` where `semester` = ? and `choir` = ? and (`type` != 'sectional' or `section` = ? or `section` = 0)", [$member, $SEMESTER, $CHOIR, $section], QERR);
	}
	return $err;
}

function doEditProfile($email, $params)
{
	global $USER, $CHOIR, $SEMESTER, $DB;
	if (! $email) $email = $USER;
	if ($email != $USER && ! hasPermission("edit-user")) err("You do not have permission to change someone else's settings.");

	$permitted = array("firstName", "prefName", "lastName", "email", "password", "phone", "picture", "passengers", "onCampus", "location", "about", "major", "minor", "hometown", "techYear", "gChat", "twitter", "gatewayDrug", "conflicts");
	$required = array("firstName", "lastName", "email", "phone", "passengers", "onCampus", "major", "hometown");
	if (! $USER) array_push($required, "choir", "password", "password2", "section");
	foreach ($required as $field) if (! isset($params[$field])) err("Missing value for property \"$field\".");

	$newemail = $params["email"];
	$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
	if (! preg_match($validEmail, $params["email"])) err("Invalid email");
	if (query("select * from `member` where `email` = ? and `email` != ?", [$newemail, $email ? $email : ""], QCOUNT) > 0) err("That email address is already in use");

	if (isset($params["password"]) && $params["password"] != "")
	{
		if ($params["password"] != $params["password2"]) err("Passwords do not match");
		$params["password"] = md5($params["password"]);
	}
	else unset($params["password"]);

	if (! preg_match("/[0-9]{9,14}/", $params["phone"])) err("Invalid phone number (proper format is just 10 digits)");
	if (! preg_match("/[0-9]{1,2}/", $params["passengers"])) err("Invalid number of passengers (must be an integer; 0 if you don't have a car)");

	if (! $USER) $choir = $params["choir"];
	else $choir = $CHOIR;

	$keys = [];
	$vals = [];
	$sql = "";
	if ($USER)
	{
		foreach ($params as $key => $value) if (in_array($key, $permitted))
		{
			$keys[] = "`$key` = ?";
			$vals[] = clean($value);
		}
		$vals[] = $email;
		$sql = "update `member` set " . implode(", ", $keys) . " where `email` = ?";
	}
	else
	{
		foreach ($params as $key => $value) if (in_array($key, $permitted))
		{
			$keys[] = "`$key`";
			$vals[] = clean($value);
		}
		$sql = "insert into `member` (" . implode(", ", $keys) . ") values (" . implode(", ", array_fill(0, count($vals), "?")) . ")";
	}
	if (! $email) $email = $params["email"]; // New registrant
	$DB->begin_transaction();
	check(query($sql, $vals, QERR));
	check(updateRegistration($email, $params["enrollment"], $params["section"]));
	$DB->commit();
	if (! $USER || $USER == $email) setcookie("email", encrypt2($newemail), time() + 60 * 60 * 24 * 120, "/", false, false);
	if (! $USER) setcookie("choir", $choir, time() + 60 * 60 * 24 * 120, "/", false, false);
}

function forgotPasswordEmail($email)
{
	global $BASEURL, $application;
	if (query("select * from member where email = ?", [$email], QONE))
	{
		$enc = encrypt2($email . " " . time());
		$msg = "We have received a request to reset your password on $application.  To reset your password, <a href='$BASEURL/php/resetPassword.php?code=" . urlencode($enc) . "'>click here.</a>  If you did not request a password reset, please ignore this email.";
		$headers  = 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
		mail($email, "$application Password Reset", $msg, $headers);
	}
}
?>
