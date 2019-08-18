<?php
require_once('functions.php');
if (! $CHOIR) err("Choir not set");
echo "<div class='span6 block'>";
	foreach (query("select * from `announcement` where `choir` = ? order by `timePosted` desc", [$CHOIR], QALL) as $announcement)
	{
		$timestamp = strtotime($announcement['timePosted']);
		$dayPosted = date( 'M j, Y', $timestamp);
		$timePosted = date( 'g:i a', $timestamp);
		$op = $announcement['memberID'];
		$mid = $announcement['announcementNo'];
		$name = memberName($op, "pref");
		$text = nl2br(htmlspecialchars($announcement["announcement"]));
		echo "<div class='block'><p><b>$dayPosted $timePosted</b><br>".$text."<br><br><small style='color:grey'>&mdash; $name</small></p></div>";
	}
	echo "</div>";
?>

