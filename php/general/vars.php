<?php
require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpsdocs/creds.php');

function refValues($arr)
{
	$refs = array();
	foreach($arr as $key => $value) $refs[$key] = &$arr[$key];
	return $refs;
}

const QNONE = 1; // Return nothing
const QALL = 2; // Fetch and return all rows
const QONE = 3; // Fetch first row, or NULL if none matches
const QCOUNT = 4; // Return number of matching rows
const QID = 5; // Return ID of inserted row
const QERR = 6; // Return error message rather than die()ing, or NULL on success

function qerror($q, $err, $return)
{
	$msg = "Failed to perform query \"$q\": $err";
	if ($return) return $msg;
	die($msg);
}

function query($q, $values = [], $fetch = QNONE)
{
	global $DB;
	$types = "";
	$i = 1;
	foreach ($values as $v)
	{
		$type = gettype($v);
		if ($type == "integer" || $type == "double" || $type == "string") $types .= $type[0];
		else return qerror($q, "Query argument $i is of invalid type $type", $fetch == QERR);
		$i++;
	}
	$stmt = $DB->prepare($q);
	if (! $stmt) return qerror($q, $DB->error, $fetch == QERR);
	if (count($values) > 0) call_user_func_array(array($stmt, "bind_param"), refValues(array_merge(array($types), $values)));
	if (! $stmt->execute()) return qerror($q, $stmt->error, $fetch == QERR);
	if ($fetch == QNONE || $fetch == QERR)
	{
		$stmt->free;
		return NULL;
	}
	if ($fetch == QID)
	{
		$ret = $stmt->insert_id;
		$stmt->free;
		return $ret;
	}
	$res = $stmt->get_result();
	if ($fetch == QALL) $ret = $res->fetch_all(MYSQLI_ASSOC);
	else if ($fetch == QONE)
	{
		if ($res->num_rows < 1) return NULL;
		return $res->fetch_assoc();
	}
	else if ($fetch == QCOUNT) $ret = $res->num_rows;
	else return qerror($q, "Unknown query mode $fetch", false);
	$res->free();
	$stmt->close();
	return $ret;
}

function getuser()
{
	global $sessionkey;
	$auth = "";
	//if (isset($_POST["identity"])) $auth = $_POST["identity"];
	if (isset($_SERVER["HTTP_X_IDENTITY"])) $auth = $_SERVER["HTTP_X_IDENTITY"];
	else if (isset($_COOKIE["email"])) $auth = $_COOKIE["email"];
	else return false;
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sessionkey, base64_decode($auth), MCRYPT_MODE_ECB), "\0");
}

function getchoir()
{
	global $sessionkey;
	//if (isset($_POST["choir"])) $choir = $_POST["choir"];
	if (isset($_COOKIE["choir"])) $choir = $_COOKIE["choir"];
	else return false;
	if (query("select `id` from `choir` where `id` = ?", [$choir], QCOUNT) != 1) return false;
	return $choir;
}

$DB->set_charset("utf8");
$variables = query("select * from `variables`", [], QONE);
if (! $variables) die("Variables table is empty");
$webroot = "/var/www/vhosts/mensgleeclub.gatech.edu";
$docroot = "$webroot/httpsdocs/buzz";
$docroot_external = "$webroot/httpsdocs";
$musicdir = "/music";
$domain = "gleeclub.gatech.edu";
$BASEURL = "https://$domain/buzz";
$SEMESTER = $variables["semester"];
$CHOIR = getchoir();
$USER = getuser();
$application = "Gree-C-Web";
?>
