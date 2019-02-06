<?php
require_once('/var/www/vhosts/mensgleeclub.gatech.edu/httpsdocs/creds.php');

function encrypt2($string)
{
	global $serverkey;
	return openssl_encrypt($string, "aes-256-cbc", $serverkey);
}

function decrypt2($string)
{
	global $serverkey;
	return openssl_decrypt($string, "aes-256-cbc", $serverkey);
}

function getuser()
{
	$auth = "";
	//if (isset($_POST["identity"])) $auth = $_POST["identity"];
	if (isset($_SERVER["HTTP_X_IDENTITY"])) $auth = $_SERVER["HTTP_X_IDENTITY"];
	else if (isset($_COOKIE["email"])) $auth = $_COOKIE["email"];
	else return false;
	return decrypt2($auth);
}

function getchoir()
{
	//if (isset($_POST["choir"])) $choir = $_POST["choir"];
	if (isset($_COOKIE["choir"])) $choir = $_COOKIE["choir"];
	else return false;
	if (query("select `id` from `choir` where `id` = ?", [$choir], QCOUNT) != 1) return false;
	return $choir;
}

$CHOIR = getchoir();
$USER = getuser();

function refValues($arr)
{
	$refs = array();
	foreach($arr as $key => $value) $refs[$key] = &$arr[$key];
	return $refs;
}

function json_error($err)
{
	echo "{ \"status\": \"internal_error\", \"message\": \"JSON encoding error: $err\"}";
}

function utf8ize($mixed) // https://stackoverflow.com/questions/10199017/how-to-solve-json-error-utf8-error-in-php-json-decode // TODO Remove
{
	if (is_array($mixed))
		foreach ($mixed as $key => $value)
			$mixed[$key] = utf8ize($value);
	else if (is_string($mixed))
		return utf8_encode($mixed);
	return $mixed;
}

function json_reply($arr)
{
	header("Content-Type: application/json; charset=utf-8");
	$ret = json_encode($arr);
	switch (json_last_error())
	{
		case JSON_ERROR_NONE: echo($ret); break;
		case JSON_ERROR_DEPTH: json_error("DEPTH"); break;
		case JSON_ERROR_STATE_MISMATCH: json_error("STATE_MISMATCH"); break;
		case JSON_ERROR_CTRL_CHAR: json_error("CTRL_CHAR"); break;
		case JSON_ERROR_SYNTAX: json_error("SYNTAX"); break;
		case JSON_ERROR_UTF8: json_error("UTF8"); break;
		case JSON_ERROR_RECURSION: json_error("RECURSION"); break;
		case JSON_ERROR_INF_OR_NAN: json_error("INF_OR_NAN"); break;
		case JSON_ERROR_UNSUPPORTED_TYPE: json_error("UNSUPPORTED_TYPE"); break;
		case JSON_ERROR_INVALID_PROPERTY_NAME: json_error("INVALID_PROPERTY_NAME"); break;
		case JSON_ERROR_UTF16: json_error("UTF16"); break;
		default: json_error("Unknown"); break;
	}
	exit(0);
}

function err($msg, $info = null)
{
	global $USER, $CHOIR;
	if ($info)
	{
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		$content = print_r(array("info" => $info, "environment" => array("USER" => $USER, "CHOIR" => $CHOIR), "backtrace" => $backtrace), true);
		mail("Matthew Schauer <awesome@gatech.edu>", "Gree-C-Web Internal Error", $content);
	}
	json_reply(array("status" => $info ? "internal_error" : "error", "message" => $msg));
}

const QNONE = 1; // Return nothing
const QALL = 2; // Fetch and return all rows
const QONE = 3; // Fetch first row, or NULL if none matches
const QCOUNT = 4; // Return number of matching rows
const QID = 5; // Return ID of inserted row
const QERR = 6; // Return error message rather than die()ing, or NULL on success

function qerror($q, $values, $err, $return)
{
	$info = array("message" => "Database query failed", "cause" => $err, "query" => $q, "values" => $values);
	if ($return) // TODO Get rid of this dichotomy -- can we get rid of QERR entirely and always call err()?
	{
		$message = print_r(array("info" => $info, "backtrace" => debug_backtrace()), true);
		mail("Matthew Schauer <awesome@gatech.edu>", "Gree-C-Web Internal Error", $message);
		return $msg;
	}
	else err("A database query failed", $info);
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
		else return qerror($q, $values, "Query argument $i is of invalid type $type", $fetch == QERR);
		$i++;
	}
	$stmt = $DB->prepare($q);
	if (! $stmt) return qerror($q, $values, $DB->error, $fetch == QERR);
	if (count($values) > 0) call_user_func_array(array($stmt, "bind_param"), refValues(array_merge(array($types), $values)));
	if (! $stmt->execute()) return qerror($q, $values, $stmt->error, $fetch == QERR);
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
	else return qerror($q, $values, "Unknown query mode $fetch", false);
	$res->free();
	$stmt->close();
	return $ret;
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
$application = "Gree-C-Web";
?>
