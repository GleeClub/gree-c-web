<?php
require_once('functions.php');
if (! $USER || ! isOfficer($USER)) die("Go home, earthling.");
if (! $CHOIR) die("Choir not set");

if (isset($_POST['name']))
{
	$name = mysql_real_escape_string($_POST['name']);
	$url = mysql_real_escape_string($_POST['url']);
	if (preg_match("[^A-Za-z0-9 _-]")) die("Permitted characters in name: A-Z a-z 0-9 underscore hyphen space");
	if ($_POST['action'] == "delete")
	{
		if (! mysql_query("delete from `gdocs` where `name` = '$name' and `choir` = '$CHOIR'")) die("Couldn't delete $name link: " . mysql_error());
	}
	else if (mysql_num_rows(mysql_query("select * from `gdocs` where `name` = '$name'")) > 0)
	{
		if (! mysql_query("update `gdocs` set `url` = '$url' where `name` = '$name' and `choir` = '$CHOIR'")) die("Couldn't update $name link: " . mysql_error());
	}
	else
	{
		if (! mysql_query("insert into `gdocs` (`name`, `choir`, `url`) values ('$name', '$CHOIR', '$url')")) die("Couldn't create $name link: " . mysql_error());
	}
	echo "OK";
}
else
{
	echo "<style>th { text-align: left; } .docurl { width: 800px; max-width: 100%; margin-bottom: 0px !important; }</style>";
	echo "<table><tr><th>Document</th><th>Location</th></tr>";
	$query = mysql_query("select `name`, `url` from `gdocs`");
	while ($row = mysql_fetch_array($query))
	{
		echo "<tr><td>$row[name]</td><td><input type='text' class='docurl' name='$row[name]' value='$row[url]'><button type='button' class='btn urlchange'>Change</button><button type='button' class='btn urldel'><i class='icon-remove'></i></td></tr>";
	}
	echo "<tr><td><input class='docurl' id='newname' type='text' style='width: 15em'></td><td><button id='urladd' type='button' class='btn'><i class='icon-plus'></i></button></td></tr></table>";
}

?>
