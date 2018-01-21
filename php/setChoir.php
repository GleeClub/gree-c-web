<?php
require_once("functions.php");
if (! isset($_POST["choir"])) die("Missing choir argument");
$choir = mysql_real_escape_string($_POST["choir"]);
$query = mysql_query("select * from `choir` where `id` = '$choir'");
if (! $query) die(mysql_error());
if (mysql_num_rows($query) < 1) die("Bad value for choir argument");
setcookie('choir', $choir, time() + 60*60*24*120, '/', false, false);
# TODO Set last choir value in database for restoring on login
echo "OK";
?>
