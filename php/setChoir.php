<?php
require_once("functions.php");
if (! isset($_POST["choir"])) die("Missing choir argument");
$choir = $_POST["choir"];
if (query("select * from `choir` where `id` = ?", [$choir], QCOUNT) < 1) die("Bad value for choir argument");
setcookie('choir', $choir, time() + 60*60*24*120, '/', false, false);
// TODO Set last choir value in database for restoring on login
echo "OK";
?>
