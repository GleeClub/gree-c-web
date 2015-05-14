<style>
th { text-align: left; }
td { padding-right: 40px; }
</style>
<?php
require_once('functions.php');

if (! isUber(getuser())) die("Go home, earthling.");

echo "<table><tr><th>Position</th><th>Member</th></tr>";
$query = mysql_query("select * from `memberType` where `rank` > 0 order by `rank` asc");
while ($row = mysql_fetch_array($query))
{
	$subq = mysql_query("select `email` from `member` where `position` = '$row[typeName]'");
	for ($i = 0; $i < $row['quantity']; $i++)
	{
		$member = mysql_fetch_array($subq);
		echo "<tr><td class='position'>$row[typeName]</td><td data-old='$member[email]'>" . memberDropdown($member['email']) . "</td></tr>";
	}
}
echo "</table>";

?>
