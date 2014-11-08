<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_COOKIE['email'])) {
  	$name = mysql_real_escape_string($_POST['name']);
    $sql = "DELETE FROM `validSemester` WHERE `semester`='$name' LIMIT 1";
  	if(mysql_query($sql))
      echo "<br><h3>Removal Results</h3><br>$name was removed from the database.<br>";
    else
      echo "<br><h3>Removal Results</h3><br>Something went wrong.<br>";
}
else{
	echo "<br><h3>Removal Results</h3><br>
	It would seem that you are not logged in.  Go back and try again.<br>";
}

?>