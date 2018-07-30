<?php
require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpsdocs/db_connect.php');

function getuser()
{
	global $sessionkey;
	$auth = "";
	if (isset($_POST['identity'])) $auth = $_POST['identity'];
	else if (isset($_COOKIE['email'])) $auth = $_COOKIE['email'];
	else return false;
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sessionkey, base64_decode($auth), MCRYPT_MODE_ECB), "\0");
}

function getchoir()
{
	global $sessionkey;
	if (isset($_POST['choir'])) $choir = $_POST['choir'];
	else if (isset($_COOKIE['choir'])) $choir = $_COOKIE['choir'];
	else return false;
	$choir = mysql_real_escape_string($choir);
	if (mysql_num_rows(mysql_query("select `id` from `choir` where `id` = '$choir'")) != 1) return false;
	return $choir;
}

$variables = mysql_fetch_array(mysql_query("select * from variables"));
$webroot = "/var/www/vhosts/mensgleeclub.gatech.edu";
$docroot = "$webroot/httpsdocs/buzz";
$docroot_external = "$webroot/httpdocs";
$musicdir = "/music";
$domain = "gleeclub.gatech.edu";
$BASEURL = "https://$domain/buzz";
$SEMESTER = $variables['semester'];
$CHOIR = getchoir();
$USER = getuser();
$application = "Gree-C-Web";

require_once('general/utility.php');
require_once('general/attendance.php');
require_once('general/carpools.php');
require_once('general/events.php');

if ($CHOIR) require_once('choir/' . $CHOIR . '/base.php');
?>
