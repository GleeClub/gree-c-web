<?php
//it would seem you cannot connect to the database from outside a function and inside a function
require_once('functions.php');
$userEmail = $_COOKIE['email'];

if(isset($_COOKIE['email'])) {
	//announcement block
	$sql = "SELECT * FROM `announcement` WHERE 1 ORDER BY `timePosted` desc limit 0, 3";
	$result = mysql_query($sql);

	echo "
		<div class='block'>
			<h2>Speak unto thy people!</h2>
			<br>
			<textarea class='announcement-textarea' id='announcementText' rows='20'></textarea>
			<button class='btn btn-large btn-block' id='addAnnouncementButton'>Send thy message!</button>
		</div>";
}

?>
