<?php
require_once('functions.php');
mysql_set_charset("utf8");
if (! isOfficer(getuser())) die("DENIED");
$action = $_POST['action'];
$event = mysql_real_escape_string($_POST['event']);
$song = mysql_real_escape_string($_POST['song']);
$order = mysql_real_escape_string($_POST['order']);
if ($action == "add")
{
	$query = mysql_query("select max(`order`) as `num` from `gigSong` where `event` = '$event'");
	$row = mysql_fetch_array($query);
	$next = $row['num'] + 1;
	if (! mysql_query("insert into `gigSong` (`event`, `song`, `order`) values ('$event', '$song', '$next')")) die("FAIL");
	$query = mysql_query("select `title`, `key`, `pitch` from `song` where `id` = '$song'");
	$row = mysql_fetch_array($query);
	echo "<tr id='song$next'><td class='delcol' style='display: table-cell'><a href='#' class='set_del'><i class='icon-remove'></i></a></td><td>$next</td><td><a href='#song:$song'>" . $row['title'] . "</a></td><td>" . $row['key'] . "</td><td>" . $row['pitch'] . "</td></tr>";
}
else if ($action == "remove")
{
	$query = mysql_query("select max(`order`) as `num` from `gigSong` where `event` = '$event'");
	$row = mysql_fetch_array($query);
	$num = $row['num'];
	if (! mysql_query("delete from `gigSong` where `event` = '$event' and `order` = '$order'")) die("FAIL");
	for ($i = $order + 1; $i <= $num; $i++) if (! mysql_query("update `gigSong` set `order` = '" . ($i - 1) . "' where `order` = '$i'")) die("FAIL");
	echo "OK";
}
else if ($action == "arrange")
{
	// $order contains a comma-separated list of the new order based on the old order
	$sql = "update `gigSong` set `order` = case `order` ";
	$new = split(',', $order);
	for ($i = 1; $i <= count($new); $i++) $sql .= "when '" . $new[$i - 1] . "' then '$i' ";
	$sql .= "end where `event` = '$event'";
	if (! mysql_query($sql)) die("FAIL");
	echo "OK";
}
else die("BAD_ACTION");
?>
