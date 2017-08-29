<?php
require_once("functions.php");
$user = $USER; // The user trying to change settings
$email = $USER; // The user being changed

if (isset($_POST["user"]))
{
	$email = $_POST["user"];
	if (! isOfficer($user) && $email != $user) die("You do not have permission to change someone else's settings.");
}

$permitted = array("firstName", "prefName", "lastName", "email", "password", "phone", "picture", "passengers", "onCampus", "location", "about", "major", "minor", "hometown", "techYear", "gChat", "twitter", "gatewayDrug", "conflicts");
$required = array("firstName", "lastName", "email", "phone", "passengers", "onCampus", "major", "hometown");
$restricted = array();
if (! $user) $required[] = "choir";
if ($user == $email) $required[] = "registration";
if (isset($_POST["onCampus"])) $_POST["onCampus"] = "1";
else $_POST["onCampus"] = "0";
foreach ($required as $field) if (! isset($_POST[$field]) || $_POST[$field] == "") die("Missing value for property \"$field\".");
if (! isOfficer($user)) foreach ($restricted as $field) if (isset($_POST[$field])) die("Permission denied to set property \"$field\".");

$newemail = mysql_real_escape_string($_POST["email"]);
$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
if (! preg_match($validEmail, $_POST["email"])) die("Invalid email");
$oldsect = 0;
$newsect = mysql_real_escape_string($_POST["section"]);
if ($user)
{
	$query = mysql_query("select `section` from `activeSemester` where `member` = '$newemail' and `semester` = '$SEMESTER' and `choir` = '$CHOIR'");
	if (! $query) die("Error: " . mysql_error());
	if ($user) $oldsect = mysql_fetch_array($query)["section"];
}
$count = mysql_num_rows(mysql_query("select * from `member` where `email` = '$newemail'"));
if (! $user && $count > 0) die("That email address is already in use");
if ($user && $_POST["email"] != $email && $count > 0) die("That email address is already in use");

if (! $user && (! isset($_POST["password"]) || $_POST["password"] == "" || ! isset($_POST["password2"]) || $_POST["password2"] == "")) die("Missing value for property \"password\".");
if ($_POST["password"] != $_POST["password2"]) die("Passwords do not match");
if ($_POST["password"] == "") unset($_POST["password"]);
else $_POST["password"] = md5($_POST["password"]);

if (! preg_match("/[0-9]{9,14}/", $_POST["phone"])) die("Invalid phone number (proper format is just 10 digits)");
if (! preg_match("/[0-9]{1,2}/", $_POST["passengers"])) die("Invalid number of passengers (must be an integer, 0 if you don't have a car)");

$reg = mysql_real_escape_string($_POST["registration"]);
if (! $user) $choir = mysql_real_escape_string($_POST["choir"]);
else $choir = $CHOIR;
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
function cancel($msg)
{
	echo $msg . ": " . mysql_error();
	mysql_query("rollback");
	die();
}
mysql_query("begin");
if (! mysql_query($sql)) cancel();
if ($user && ! mysql_query("update `activeSemester` set `enrollment` = '$reg', `section` = '$newsect' where `member` = '$newemail' and `semester` = '$SEMESTER' and `choir` = '$choir'")) cancel("Could not update active semesters");
if (! $user && ! mysql_query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`, `section`) values ('$newemail', '$SEMESTER', '$choir', '$reg', '$newsect')")) cancel("Could not update active semesters");
if (! $user || $newsect != $oldsect)
{
	$msg = updateSection($newemail, $SEMESTER, $choir, $newsect, $user);
	if ($msg != "") cancel("Couldn't set section");
}
if (! $user)
{
	if (! mysql_query("insert into `attends` (`memberID`, `eventNo`, `shouldAttend`) select '$newemail', `eventNo`, 1 from `event` where `choir` = '$choir' and `semester` = '$SEMESTER' and (`section` = 0 or `section` = $newsect) and `type` != 'sectional'")) cancel("Couldn't add new member to existing events");
}
mysql_query("commit");
if (! $user || $user == $email) setcookie("email", base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $newemail, MCRYPT_MODE_ECB)), time() + 60 * 60 * 24 * 120, "/", false, false);
if (! $user) setcookie('choir', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $choir, MCRYPT_MODE_ECB)), time() + 60 * 60 * 24 * 120, '/', false, false);
echo "OK";
?>
