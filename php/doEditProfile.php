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
$oldsect = 0;
$newsect = mysql_real_escape_string($_POST["section"]);
$query = mysql_query("select `section` from `member` where `email` = '$newemail'");
$count = mysql_num_rows($query);
if ($user) $oldsect = mysql_fetch_array($query)["section"];
else if ($count > 0) die("That email address is already in use");
if ($user && $_POST["email"] != $email && $result > 0) die("That email address is already in use");

if (! $user && (! isset($_POST["password"]) || $_POST["password"] == "" || ! isset($_POST["password2"]) || $_POST["password2"] == "")) die("Missing value for property \"password\".");
if ($_POST["password"] != $_POST["password2"]) die("Passwords do not match");
if ($_POST["password"] == "") unset($_POST["password"]);
else $_POST["password"] = md5($_POST["password"]);

if (! preg_match("/[0-9]{9,14}/", $_POST["phone"])) die("Invalid phone number (proper format is just 10 digits)");
if (! preg_match("/[0-9]{1,2}/", $_POST["passengers"])) die("Invalid number of passengers (must be an integer, 0 if you don't have a car)");

$reg = mysql_real_escape_string($_POST["registration"]);
if ($reg != "class" && $reg != "club") die("Invalid registration");

$sql = "";
if ($user)
{
	$sql = "update `member` set ";
	$cond = array();
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

function cancel()
{
	echo "Couldn't apply settings: " . mysql_error();
	mysql_query("rollback");
	die();
}

mysql_query("begin");
if (! mysql_query($sql)) cancel();
if ($user && ! mysql_query("update `activeSemester` set `enrollment` = '$reg' where `member` = '$newemail' and `semester` = '$CUR_SEM'")) cancel();
if (! $user && ! mysql_query("insert into `activeSemester` (`member`, `semester`, `enrollment`) values ('$newemail', '$CUR_SEM', '$reg')")) cancel();
if (! $user)
{
	if (! mysql_query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select '$newemail', '1', '1', `eventNo` from `event` where `semester` = '$CUR_SEM' and (`type` = 1 or `type` = 3 or `type` = 4)")) cancel();
	if (! mysql_query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select '$newemail', '1', '1', `eventNo` from `event` where `semester` = '$CUR_SEM' and `type` = 2 and `section` = '$newsect'")) cancel();
}
if ($user && $newsect != $oldsect)
{
	if (! mysql_query("delete from `attends` where `memberID` = '$newemail' and `eventNo` in (select `eventNo` from `event` where `type` = 2) and (select `callTime` from `event` where `event`.`eventNo` = `attends`.`eventNo`) > current_timestamp")) cancel();
	if (! mysql_query("insert into `attends` (`memberID`, `shouldAttend`, `confirmed`, `eventNo`) select '$newemail', '1', '1', `eventNo` from `event` where `semester` = '$CUR_SEM' and `type` = 2 and `section` = '$newsect' and `callTime` > current_timestamp")) cancel();
}
if (! $user || $user == $email) setcookie("email", base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $newemail, MCRYPT_MODE_ECB)), time() + 60 * 60 * 24 * 120, "/", false, false);
mysql_query("commit");
echo "OK";
?>
