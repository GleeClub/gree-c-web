<?php
require_once('functions.php');

if(isset($_COOKIE['email'])) {
  	$name = mysql_real_escape_string($_POST['name']);
  	$sDD = $_POST['sDD'];
  	$sMM = $_POST['sMM'];
  	$sYYYY = $_POST['sYYYY'];
  	$eDD = $_POST['eDD'];
  	$eMM = $_POST['eMM'];
  	$eYYYY = $_POST['eYYYY'];

  	$start = "$sYYYY-$sMM-$sDD 00:00:00";
  	$end = "$eYYYY-$eMM-$eDD 00:00:00";

  	$sql = "insert into validSemester (semester,beginning,end) values ('$name','$start','$end')";
  	mysql_query($sql);

  	$sql = "UPDATE `variables` SET `semester`='$name' WHERE 1";
  	
  	if(mysql_query($sql)){
	  	$sql = "select `semester` from `variables` WHERE 1";
	  	$cur_sem = mysql_fetch_array(mysql_query($sql));
	  	$cur_sem = $cur_sem['semester'];

  		$sql = "UPDATE `member` SET `confirmed`=0 WHERE 1";
  		mysql_query($sql);

		echo "<legend>Results</legend>The current semester is now: $cur_sem";
	}
	else
		echo "<legend>Results</legend>Something went wrong.";
}
else{
	echo "<legend>Results</legend>
	It would seem that you are not logged in.  Go back and try again.";
}

?>