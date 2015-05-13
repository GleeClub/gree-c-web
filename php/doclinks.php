<?php
require_once('functions.php');
if (! getuser() || ! isOfficer(getuser())) die("Go home, earthling.");

if (isset($_POST['name']))
{
	$name = mysql_real_escape_string($_POST['name']);
	$url = mysql_real_escape_string($_POST['url']);
	if (! mysql_query("update `gdocs` set `url` = '$url' where `name` = '$name'")) die("Couldn't update $name link");
	echo "OK";
}
else
{
	echo "<style>th { text-align: left; } .docurl { width: 800px; max-width: 100%; }</style>";
	echo "<table><tr><th>Document</th><th>Location</th></tr>";
	$query = mysql_query("select `name`, `url` from `gdocs`");
	while ($row = mysql_fetch_array($query))
	{
		echo "<tr><td>$row[name]</td><td><input type='text' class='docurl' name='$row[name]' value='$row[url]'><button type='button' class='btn urlchange'>Change</button></td></tr>";
	}
	echo "</table>";
}

?>
