<?php
require_once('functions.php');
require_once("$docroot_external/php/lib/google-api-php-client-2.1.3/vendor/autoload.php");

if (! $USER) die("Not logged in");
if (! isset($_POST['eventNo'])) die("Missing event number");
$eventNo = mysql_real_escape_string($_POST['eventNo']);
$query = mysql_query("select `type` from `event` where `eventNo` = $eventNo");
if (! $query) die(mysql_error());
$row = mysql_fetch_array($query);
if (! hasEventPermission("delete", $eventNo)) die("Permission denied");
mysql_query("DELETE FROM `event` WHERE `eventNo` = $eventNo LIMIT 1");

$service = get_gcal();
$service->events->delete($calendar, "calev$eventNo");
?>

