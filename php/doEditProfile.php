<?php
require_once("functions.php");
$user = $USER; // The user trying to change settings
$email = $USER; // The user being changed
if (! $email) $email = "";

if (isset($_POST["user"]))
{
	$email = $_POST["user"];
	if (! hasPermission("edit-user") && $email != $user) err("You do not have permission to change someone else's settings.");
}

$permitted = array("firstName", "prefName", "lastName", "email", "password", "phone", "picture", "passengers", "onCampus", "location", "about", "major", "minor", "hometown", "techYear", "gChat", "twitter", "gatewayDrug", "conflicts");
$required = array("firstName", "lastName", "email", "phone", "passengers", "onCampus", "major", "hometown");
if (! $user) array_push($required, "choir", "password", "password2", "section");
if (isset($_POST["onCampus"])) $_POST["onCampus"] = "1";
else $_POST["onCampus"] = "0";
foreach ($required as $field) if (! isset($_POST[$field]) || $_POST[$field] == "") err("Missing value for property \"$field\".");

$newemail = $_POST["email"];
$validEmail = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
if (! preg_match($validEmail, $_POST["email"])) err("Invalid email");
$newsect = $_POST["section"];
$active = 1; // 1 if the user is active this semester
$oldsect = 0;
if ($user)
{
	$res = query("select `section` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$email, $SEMESTER, $CHOIR], QONE);
	if (! $res) $active = 0;
	else $oldsect = $res["section"];
}
if (query("select * from `member` where `email` = ? and `email` != ?", [$newemail, $email], QCOUNT) > 0) err("That email address is already in use");

if (isset($_POST["password"]) && $_POST["password"] != "")
{
	if ($_POST["password"] != $_POST["password2"]) err("Passwords do not match");
	$_POST["password"] = md5($_POST["password"]);
}
else unset($_POST["password"]);

if (! preg_match("/[0-9]{9,14}/", $_POST["phone"])) err("Invalid phone number (proper format is just 10 digits)");
if (! preg_match("/[0-9]{1,2}/", $_POST["passengers"])) err("Invalid number of passengers (must be an integer; 0 if you don't have a car)");

$reg = $_POST["registration"];
if (! $user) $choir = $_POST["choir"];
else $choir = $CHOIR;
if ($reg != "class" && $reg != "club") err("Invalid registration");

$keys = [];
$vals = [];
$sql = "";
if ($user)
{
	foreach ($_POST as $key => $value) if (in_array($key, $permitted))
	{
		$keys[] = "`$key` = ?";
		$vals[] = $value;
	}
	$vals[] = $email;
	$sql = "update `member` set " . implode(", ", $keys) . " where `email` = ?";
}
else
{
	foreach ($_POST as $key => $value) if (in_array($key, $permitted))
	{
		$keys[] = "`$key`";
		$vals[] = $value;
	}
	$sql = "insert into `member` (" . implode(", ", $keys) . ") values (" . implode(", ", array_fill(0, count($vals), "?")) . ")";
}
$DB->begin_transaction();
check(query($sql, $vals, QERR));
if ($user && $active) check(query("update `activeSemester` set `enrollment` = ?, `section` = ? where `member` = ? and `semester` = ? and `choir` = ?", [$reg, $newsect, $newemail, $SEMESTER, $choir], QERR));
if (! $user) check(query("insert into `activeSemester` (`member`, `semester`, `choir`, `enrollment`, `section`) values (?, ?, ?, ?, ?)", [$newemail, $SEMESTER, $choir, $reg, $newsect], QERR));
if (! $user || ($active && $newsect != $oldsect)) check(updateSection($newemail, $SEMESTER, $choir, $newsect, $user));
if (! $user) check(query("insert into `attends` (`memberID`, `eventNo`, `shouldAttend`) select ?, `eventNo`, `defaultAttend` from `event` where `choir` = ? and `semester` = ? and (`section` = 0 or `section` = ?) and `type` != 'sectional'", [$newemail, $choir, $SEMESTER, $newsect], QERR));
$DB->commit();
if (! $user || $user == $email) setcookie("email", encrypt2($newemail), time() + 60 * 60 * 24 * 120, "/", false, false);
if (! $user) setcookie("choir", $choir, time() + 60 * 60 * 24 * 120, "/", false, false);
echo "OK";
?>
