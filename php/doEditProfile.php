<?php
require_once("functions.php");
$user = $USER; // The user trying to change settings
$email = $USER; // The user being changed

if (isset($_POST["user"]))
{
	$email = $_POST["user"];
	if (! hasPermission("edit-user") && $email != $user) die("You do not have permission to change someone else's settings.");
}

$permitted = array("firstName", "prefName", "lastName", "email", "password", "phone", "picture", "passengers", "onCampus", "location", "about", "major", "minor", "hometown", "techYear", "gChat", "twitter", "gatewayDrug", "conflicts");
$required = array("firstName", "lastName", "email", "phone", "passengers", "onCampus", "major", "hometown");
$restricted = array();
if (! $user) $required[] = "choir";
if ($user == $email) $required[] = "registration";
if (isset($_POST["onCampus"])) $_POST["onCampus"] = "1";
else $_POST["onCampus"] = "0";
foreach ($required as $field) if (! isset($_POST[$field]) || $_POST[$field] == "") die("Missing value for property \"$field\".");
if (! hasPermission("edit-user")) foreach ($restricted as $field) if (isset($_POST[$field])) die("Permission denied to set property \"$field\".");

$newemail = $_POST["email"];
$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
if (! preg_match($validEmail, $_POST["email"])) die("Invalid email");
$oldsect = 0;
$newsect = $_POST["section"];
if ($user)
{
	$res = query("select `section` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$newemail, $SEMESTER, $CHOIR], QONE);
	if (! $res) die("Couldn't determine old section");
	$oldsect = $res["section"];
}
$count = query("select * from `member` where `email` = ?", [$newemail], QCOUNT);
if (! $user && $count > 0) die("That email address is already in use");
if ($user && $_POST["email"] != $email && $count > 0) die("That email address is already in use");

if (! $user && (! isset($_POST["password"]) || $_POST["password"] == "" || ! isset($_POST["password2"]) || $_POST["password2"] == "")) die("Missing value for property \"password\".");
if ($_POST["password"] != $_POST["password2"]) die("Passwords do not match");
if ($_POST["password"] == "") unset($_POST["password"]);
else $_POST["password"] = md5($_POST["password"]);

if (! preg_match("/[0-9]{9,14}/", $_POST["phone"])) die("Invalid phone number (proper format is just 10 digits)");
if (! preg_match("/[0-9]{1,2}/", $_POST["passengers"])) die("Invalid number of passengers (must be an integer, 0 if you don't have a car)");

$reg = $_POST["registration"];
if (! $user) $choir = $_POST["choir"];
else $choir = $CHOIR;
if ($reg != "class" && $reg != "club") die("Invalid registration");

function check($msg)
{
	if (! $msg) return;
	query("rollback");
	die($msg);
}
$keys = [];
$vals = [];
$sql = "";
if ($user)
{
	foreach ($_POST as $key => $value)
	{
		if (! in_array($key, $permitted)) die("Cannot accept value for invalid key \"$key\"");
		$keys[] = "`$key` = ?";
		$vals[] = $value;
	}
	$vals[] = $email;
	$sql = "update `member` set " . implode(", ", $keys) . " where `email` = ?";
}
else
{
	foreach ($_POST as $key => $value)
	{
		if (! in_array($key, $permitted)) die("Cannot accept value for invalid key \"$key\"");
		$keys[] = "`$key`";
		$vals[] = $value;
	}
	$sql = "insert into `member` (" . implode(", ", $keys) . ") values (" . implode(", ", array_fill(0, count($vals), "?")) . ")";
}
query("begin");
check(query($sql, $vals, QERR));
if ($user) check(query("update `activeSemester` set `enrollment` = ?, `section` = ? where `member` = ? and `semester` = ? and `choir` = ?", [$reg, $newsect, $newemail, $SEMESTER, $choir], QERR));
if (! $user) check(query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`, `section`) values (?, ?, ?, ?, ?)", [$newemail, $SEMESTER, $choir, $reg, $newsect], QERR));
if (! $user || $newsect != $oldsect) check(updateSection($newemail, $SEMESTER, $choir, $newsect, $user));
if (! $user) check(query("insert into `attends` (`memberID`, `eventNo`, `shouldAttend`) select ?, `eventNo`, `defaultAttend` from `event` where `choir` = ? and `semester` = ? and (`section` = 0 or `section` = ?) and `type` != 'sectional'", [$newemail, $choir, $SEMESTER, $newsect], QERR));
query("commit");
if (! $user || $user == $email) setcookie("email", cookie_string($newemail), time() + 60 * 60 * 24 * 120, "/", false, false);
if (! $user) setcookie("choir", $choir, time() + 60 * 60 * 24 * 120, "/", false, false);
echo "OK";
?>
