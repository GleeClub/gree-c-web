<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
echo "<div class='span6 block'>";
	//announcements
	$sql = "SELECT * FROM `announcement` ORDER BY `timePosted` DESC";
	$result = mysql_query($sql);
	while($announcement=mysql_fetch_array($result)){
		$timestamp = strtotime($announcement['timePosted']);
		$dayPosted = date( 'M j, Y', $timestamp);
		$timePosted = date( 'g:i a', $timestamp);
		$op = $announcement['memberID'];
		$mid = $announcement['announcementNo'];
		$name = prefNameFromEmail($op);
		echo "<div class='block'><p><b>$dayPosted $timePosted</b><br />" . $announcement['announcement']."<br /><small style='color:grey'>&mdash;$name</small></p></div>";
			
	}
	echo "</div>";

?>