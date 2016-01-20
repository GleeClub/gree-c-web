<?php
require_once("functions.php");
$user = getuser(); // The user trying to change settings
$email = getuser(); // The user being changed

if (isset($_POST["user"]))
{
	if (! isOfficer($user)) die("You do not have permission to change someone else's settings.");
	$email = $_POST["user"];
}

$permitted = array("firstName", "prefName", "lastName", "section", "email", "password", "phone", "picture", "passengers", "onCampus", "location", "about", "major", "minor", "hometown", "techYear", "gChat", "twitter", "gatewayDrug", "conflicts");
$required = array("firstName", "lastName", "email", "phone", "passengers", "onCampus", "major", "hometown");
$restricted = array("position", "tieNum");
if ($user == $email) $required[] = "registration";
$_POST["onCampus"] = 0;
if (isset($_POST["onCampus"])) $_POST["onCampus"] = 1;
foreach ($required as $field) if (! isset($_POST[$field]) || $_POST[$field] == "") die("Missing value for property \"$field\".");
if (! isOfficer($user)) foreach ($restricted as $field) if (isset($_POST[$field])) die("Permission denied to set property \"$field\".");

$newemail = mysql_real_escape_string($_POST["email"]);
$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
if (! preg_match($validEmail, $_POST["email"])) die("Invalid email");
$result = mysql_num_rows(mysql_query("select * from `member` where `email` = '$newemail'"));
if (! $user && $result > 0) die("That email address is already in use");
if ($user && $_POST["email"] != $email && $result > 0) die("That email address is already in use");

if (! $user && (! isset($_POST["password"]) || $_POST["password"] == "" || ! isset($_POST["password2"]) || $_POST["password2"] == "")) die("Missing value for property \"password\".");
if ($_POST["password"] != $_POST["password2"]) die("Passwords do not match");

if (! preg_match("/[0-9]{9,14}/", $_POST["phone"])) die("Invalid phone number (proper format is just 10 digits)");
if (! preg_match("/[0-9]{1,2}/", $_POST["passengers"])) die("Invalid number of passengers (must be an integer, 0 if you don't have a car)");

$sql = "";
if ($user)
{
	$sql = "update `member` set ";
	$cond = array();
	if ($_POST["password"] == "") unset($_POST["password"]);
	foreach ($_POST as $key => $value) if (array_search($key, $permitted) !== FALSE) $cond[] = "`$key` = '" . mysql_real_escape_string($value) . "'";
	$sql .= implode(", ", $cond) . " where `email` = '" . mysql_real_escape_string($email) . "'";
}
else
{
	$keys = array();
	$vals = array();
	foreach ($_POST as $key => $value) if (array_search($key, $permitted) !== FALSE)
	{
		$keys[] = "`$key`";
		$vals[] = "'" . mysql_real_escape_string($value) . "'";
	}
	$sql = "insert into `member` (" . implode(", ", $keys) . ") values (" . implode(", ", $vals) . ")";
}

mysql_query("begin");
if (mysql_query($sql))
{
	$reg = mysql_real_escape_string($_POST["registration"]);
	if ($reg != "class" && $reg != "club")
	{
		mysql_query("rollback");
		die("Invalid registration");
	}
	if ($user && ! mysql_query("update `activeSemester` set `enrollment` = '$reg' where `member` = '$newemail' and `semester` = '$CUR_SEM'"))
	{
		mysql_query("rollback");
		die("Error: " . mysql_error());
	}
	if (! $user && ! mysql_query("insert into `activeSemester` (`member`, `semester`, `enrollment`) values ('$newemail', '$CUR_SEM', '$reg')"))
	{
		mysql_query("rollback");
		die("Error: " . mysql_error());
	}
	if (! $user || $user == $email) setcookie("email", base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $_POST["email"], MCRYPT_MODE_ECB)), time() + 60 * 60 * 24 * 120, "/", false, false);
	mysql_query("commit");
	echo "OK";
}
else
{
	mysql_query("rollback");
	echo "Couldn't apply settings: " . mysql_error();
}
?>
