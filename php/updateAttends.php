<?php

require_once('./functions.php');

if(isset($_COOKIE['email']))
{
	$userEmail = $_COOKIE['email'];
	$position = positionFromEmail($userEmail);

	if(isset($_POST['eventNo']) && $position == "President" || $position == "VP"){
		$eventNo = mysql_real_escape_string($_POST['eventNo']);
		$memberID = mysql_real_escape_string($_POST['email']);
		$mode = mysql_real_escape_string($_POST['mode']);
		$value = mysql_real_escape_string($_POST['value']);

		//update the attends info
		if ($mode == 'did') $sql = "update attends set confirmed='1', didAttend='$value' where memberID='$memberID' and eventNo='$eventNo'";
		else if ($mode == 'should') $sql = "update attends set confirmed='1', shouldAttend='$value' where memberID='$memberID' and eventNo='$eventNo'";
		else if ($mode == 'late') $sql = "update attends set minutesLate='$value' where memberID='$memberID' and eventNo='$eventNo'";
		else die("MAD_MODE");
		mysql_query($sql);

		echo "OK"; // $memberID $eventNo $mode $value";
	}
	else echo "Something went wrong :/";
}

?>
