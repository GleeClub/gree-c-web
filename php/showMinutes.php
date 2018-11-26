<div class="span3 block" id=minutes_list><?php
require_once('functions.php');
if (! $CHOIR) err("Not logged in"); # FIXME
if ($USER && hasPermission("edit-minutes")) echo "<div style=\"padding-top: 5px\"><button class=\"btn\" style=\"padding: 5px; width: 100%\" id=minutes_add>Add Minutes</button></div>";
echo "<table class=\"table\" id=minutes_table>";
foreach (query("select `id`, `name` from `minutes` where `choir` = ? order by `date` desc, `name`", [$CHOIR], QALL) as $result)
	echo "<tr><td class=minutes_row id='minutes" . $result['id'] . "' data-id=" . $result['id'] . ">" . $result['name'] . "</td></tr>";
echo "</table>";
?></div>
<div class="span8 block" id=minutes_main>Loading...</div>
