<div class="span3 block" id=minutes_list><?php
require_once('functions.php');
$query = "select name from `minutes` order by date desc, name";
$results = mysql_query($query);
if (! $results)
{
	echo "Database query failed.";
	exit(1);
}
if (isset($_COOKIE['email']) && isOfficer($_COOKIE['email'])) echo "<div style=\"padding-top: 5px\"><button class=\"btn\" style=\"padding: 5px; width: 100%\" id=minutes_add>Add Minutes</button></div>";
echo "<table class=\"table\" id=minutes_table>";
while ($result = mysql_fetch_array($results)) echo "<tr><td class=minutes_row>$result[0]</td></tr>";
echo "</table>";
?></div>
<div class="span8 block" id=minutes_main>Select a meeting to the left.</div>
