<?php
require_once('../functions.php');

$semester = urldecode($_POST["semester"]);
$item = urldecode($_POST["item"]);

echo '<ul class="nav nav-pills">';
$query = mysql_query("select `semester` from `semester` order by `beginning`");
while ($row = mysql_fetch_array($query))
{
	$cursem = $row["semester"];
	if (isset($semester) && $cursem == $semester) echo "<li class='active'>";
	else echo "<li>";
	echo "<a href='#timeMachine:$cursem'>$cursem</a></li>";
}
echo '</ul>';
if (! isset($semester)) exit(0);
echo '<ul class="nav nav-pills">';
$items = array("events" => "Events", "attendance" => "Attendance", "money" => "Money", "members" => "Members");
foreach ($items as $curitem => $friendly)
{
	if (isset($item) && $curitem == $item) echo "<li class='active'>";
	else echo "<li>";
	echo "<a href='#timeMachine:$semester;$curitem'>$friendly</a></li>";
}
echo '</ul>';
if (! isset($semester)) exit(0);
?>

<div class="span11" id="tmmain"></div>

