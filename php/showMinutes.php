<div class="span3 block" id=minutes_list><?php
require_once('functions.php');
if (! $CHOIR) die("Not logged in"); # FIXME
$query = "select `id`, `name` from `minutes` where `choir` = '$CHOIR' order by `date` desc, `name`";
$results = mysql_query($query);
if (! $results) die("Database query failed.");
if ($USER && isOfficer($USER)) echo "<div style=\"padding-top: 5px\"><button class=\"btn\" style=\"padding: 5px; width: 100%\" id=minutes_add>Add Minutes</button></div>";
echo "<table class=\"table\" id=minutes_table>";
while ($result = mysql_fetch_array($results)) echo "<tr><td class=minutes_row id='minutes" . $result['id'] . "' data-id=" . $result['id'] . ">" . $result['name'] . "</td></tr>";
echo "</table>";
?></div>
<div class="span8 block" id=minutes_main>Loading...</div>
