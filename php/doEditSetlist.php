<?php
require_once('functions.php');
$action = $_POST["action"];
$event = $_POST["event"];
$song = $_POST["song"];
$order = $_POST["order"];
if (! hasEventPermission("edit-setlist", $event)) err("DENIED");
if ($action == "add")
{
	$next = query("select max(`order`) as `num` from `gigSong` where `event` = ?", [$event], QONE)["num"] + 1; // FIXME Won't work if gigSong table is empty?
	query("insert into `gigSong` (`event`, `song`, `order`) values (?, ?, ?)", [$event, $song, $next]);
	$row = query("select `title`, `key`, `pitch` from `song` where `id` = ?", [$song], QONE);
	if (! $row) err("Song not found");
	echo "<tr id='song$next'><td class='delcol' style='display: table-cell'><a href='#' class='set_del'><i class='icon-remove'></i></a></td><td>$next</td><td><a href='#song:$song'>" . $row['title'] . "</a></td><td>" . $row['key'] . "</td><td>" . $row['pitch'] . "</td></tr>";
}
else if ($action == "remove")
{
	$num = query("select max(`order`) as `num` from `gigSong` where `event` = ?", [$event], QONE)["num"]; // FIXME Won't work if gigSong table is empty
	query("delete from `gigSong` where `event` = ? and `order` = ?", [$event, $order]);
	for ($i = $order + 1; $i <= $num; $i++) query("update `gigSong` set `order` = ? where `order` = ?", [$i - 1, $i]); // TODO This can probably be done in one query
	echo "OK";
}
else if ($action == "arrange")
{
	// $order contains a comma-separated list of the new order based on the old order
	$sql = "update `gigSong` set `order` = case `order` ";
	$vars = [];
	$new = explode(',', $order);
	for ($i = 1; $i <= count($new); $i++)
	{
		$sql .= "when ? then ? ";
		$vars[] = $new[$i - 1];
		$vars[] = $i;
	}
	$sql .= "end where `event` = ?";
	$vars[] = $event;
	query($sql, $vars);
	echo "OK";
}
else err("BAD_ACTION");
?>
