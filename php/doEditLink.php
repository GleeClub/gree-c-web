<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword") or die("cannot connect");
mysql_select_db("$SQLcurrentDatabase") or die("cannot select DB");

function checklink($url, $timeout = 20) // Shamelessly stolen from StackOverflow http://stackoverflow.com/questions/244506/how-do-i-check-for-valid-not-dead-links-programatically-using-php
{ // TODO I think make this less "invasive" and time-consuming
	$ch = curl_init();
	$opts = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_URL => $url, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => $timeout);
	curl_setopt_array($ch, $opts);
	curl_exec($ch);
	$ret = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 400 && curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 200;
	curl_close($ch);
	return $ret;
}
$id = mysql_real_escape_string($_POST['id']);
$action = mysql_real_escape_string($_POST['action']);
$name = mysql_real_escape_string($_POST['name']);
$type = mysql_real_escape_string($_POST['type']);
$target = mysql_real_escape_string($_POST['target']);
$song = mysql_real_escape_string($_POST['song']);
if (! isset($_COOKIE['email']) || ! isOfficer($_COOKIE['email'])) die("UNAUTHORIZED");
if ($action == "new")
{
	$query = "insert into `songLink` (`type`, `name`, `target`, `song`) values ('$type', '', '', '$song')";
	if (mysql_query($query))
	{
		$query = "select `id` from `songLink` where `type` = '$type' and `name` = '' and `target` = '' and `song` = '$song'";
		$result = mysql_fetch_array(mysql_query($query));
		echo $result[0];
	}
	else die("FAIL");
}
else if ($action == "upload")
{
	$file = $_FILES['file'];
	if ($file['error'] > 0) die($file['error']);
	$name = $file['name'];
	if ($name == '' || preg_match('/[^a-zA-Z0-9_., -]/', $name) || preg_match('/^\./', $name)) die("BAD_FNAME");
	if (! move_uploaded_file($file['tmp_name'], $docroot . $musicdir . '/' . $name)) die("BAD_UPLOAD");
	$query = "update `songLink` set `target` = '$musicdir/$name' where `id` = '$id'";
	if (mysql_query($query)) echo "OK $musicdir/$name";
	else die("FAIL");
}
else if ($action == "rmfile")
{
	if (! repertoire_delfile($id)) die("NODEL");
	echo "OK";
}
else if ($action == "delete")
{
	if (! repertoire_delfile($id)) die("NODEL"); // Remove associated file
	$query = "delete from `songLink` where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else die("FAIL");
}
else if ($action == "update")
{
	$query = "select `type` from `songLink` where `id` = '$id'";
	$result = mysql_fetch_array(mysql_query($query));
	$type = $result[0];
	$query = "select `storage` from `mediaType` where `typeid` = '$type'";
	$result = mysql_fetch_array(mysql_query($query));
	$storage = $result[0];
	if ($type == 'video') { if(! preg_match('/^[A-Za-z0-9_-]{11}$/', $target)) die("BAD_YOUTUBE"); }
	else if ($storage == 'remote')
	{
		if (! preg_match('/^http:\/\//', $target)) $target = 'http://$target';
		if (! checklink($target)) die("BAD_LINK");
	}
	$query = "update `songLink` set `name` = '$name'" . ($storage == 'remote' ? ", `target` = '$target'" : "") . " where `id` = '$id'";
	if (mysql_query($query)) echo "OK";
	else die("FAIL");
}
else die("FAIL");
?>
