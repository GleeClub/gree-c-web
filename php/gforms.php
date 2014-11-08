<?php

$type = $_GET['type'];
if($type == "feedback") {
	echo '<iframe src="https://spreadsheets.google.com/embeddedform?formkey=dEhPZURMTXVkRzdPaTBoX095MXJUbVE6MQ" width="550" height="900" scrolling="no" frameborder="0" marginheight="0" marginwidth="0">chugchugchug...</iframe>';
} else if($type == "song") {
	echo '<iframe src="https://spreadsheets.google.com/embeddedform?formkey=dG16eTk3WVUtZzhZMmdfWDVrUlJkZHc6MQ" width="500" height="700" frameborder="0" marginheight="10" marginwidth="10">Loading...</iframe>';
}

?>
