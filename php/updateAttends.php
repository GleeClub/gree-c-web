<?php

require_once('./functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_COOKIE['email'])){
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
		else
		{
			echo "BAD_MODE";
			exit (1);
		}
		mysql_query($sql);

		//get the user's first and last name
		//$sql = "select * from member where email='$memberID'";
		//$member = mysql_fetch_array(mysql_query($sql));
		//$firstName = $member['firstName'];
		//$lastName = $member['lastName'];
		echo "OK"; // $memberID $eventNo $mode $value";
	}
	else echo "Something went wrong :/";
}

?>
