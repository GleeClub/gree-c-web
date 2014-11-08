<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_COOKIE['email'])) {
  	$name = mysql_real_escape_string($_POST['name']);

  	$sql = "UPDATE `variables` SET `semester`='$name' WHERE 1";
  	
  	if(mysql_query($sql)){
	  	$sql = "select `semester` from `variables` WHERE 1";
	  	$cur_sem = mysql_fetch_array(mysql_query($sql));
	  	$cur_sem = $cur_sem['semester'];

  		$sql = "UPDATE `member` SET `confirmed`=0 WHERE 1";
  		if(mysql_query($sql))
        $memberConfirmation = "marked as inactive--they will be marked as active once they log in and confirm themselves.";
      else
        $memberConfirmation = "not marked as inactive.  Something went wrond with that step.";
		echo "
    <br><h3>Semester Change Results</h3><br>
    The current semester is now: $cur_sem<br>
    All members were ".$memberConfirmation;
	}
	else
		echo "<br><h3>Semester Change Results</h3><br>Something went wrong.";
}
else{
	echo "<br><h3>Semester Change Results</h3><br>
	It would seem that you are not logged in.  Go back and try again.";
}

?>