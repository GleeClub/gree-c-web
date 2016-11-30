<style>
th { text-align: left; }
td { padding-right: 40px; }
</style>
<?php
require_once('functions.php');

if (! isUber($USER)) die("Go home, earthling.");
if (! $CHOIR) die("Choir is not set");

echo "<table><tr><th>Position</th><th>Member</th></tr>";
$query = mysql_query("select * from `role` where `rank` > 0 and `choir` = '$CHOIR' order by `rank` asc");
while ($row = mysql_fetch_array($query))
{
	$subq = mysql_query("select `member`.`email` from `member`, `memberRole` where `memberRole`.`role` = '$row[id]' and `memberRole`.`member` = `member`.`email`"); // TODO Filter by semester
	for ($i = 0; $i < $row['quantity']; $i++)
	{
		$member = mysql_fetch_array($subq);
		echo "<tr><td class='position'>$row[name]</td><td data-old='$member[email]'>" . ($member ? memberDropdown($member['email']) : memberDropdown("")) . "</td></tr>";
	}
}
echo "</table>";

?>
