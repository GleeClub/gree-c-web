<?php
require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs/db_connect.php');

function getuser()
{
	global $sessionkey;
	if (! isset($_COOKIE['email'])) return false;
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sessionkey, base64_decode($_COOKIE['email']), MCRYPT_MODE_ECB), "\0");
}

function getchoir()
{
	global $sessionkey;
	if (! isset($_COOKIE['choir'])) return false;
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sessionkey, base64_decode($_COOKIE['choir']), MCRYPT_MODE_ECB), "\0");
}

$variables = mysql_fetch_array(mysql_query("select * from variables"));
$docroot = "/var/www/vhosts/mensgleeclub.gatech.edu/httpdocs";
$musicdir = "/music";
$domain = "gleeclub.gatech.edu";
$BASEURL = "http://$domain/buzz";
$SEMESTER = $variables['semester'];
$CHOIR = getchoir();
$USER = getuser();

require_once('general/utility.php');
require_once('general/attendance.php');
require_once('general/carpools.php');
require_once('general/events.php');
require_once('general/messaging.php');

if ($CHOIR) require_once('choir/' . $CHOIR . '/base.php');
?>
