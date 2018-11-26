<?php
//it would seem you cannot connect to the database from outside a function and inside a function
require_once('functions.php');

if (! $USER) err("Access denied");
if (! $CHOIR) err("Choir not set");
echo "<div class='block'>
		<h2>Speak unto thy people!</h2>
		<br>
		<textarea class='announcement-textarea' id='announcementText' rows='20'></textarea>
		<button class='btn btn-large btn-block' id='addAnnouncementButton'>Send thy message!</button>
	</div>";
?>
