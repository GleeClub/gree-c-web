<?php
require_once('functions.php');
require_once("$docroot_external/php/lib/google-api-php-client-2.1.3/vendor/autoload.php");

if (! $USER) die("Not logged in");
if (! isset($_POST['eventNo'])) die("Missing event number");
$eventNo = $_POST['eventNo'];
if (! hasEventPermission("delete", $eventNo)) die("Permission denied");
query("delete from `event` where `eventNo` = ? limit 1", [$eventNo]);

$service = get_gcal();
$service->events->delete($calendar, "calev$eventNo");
?>

