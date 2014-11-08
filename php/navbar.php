<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_COOKIE['email'])){
	$userEmail = mysql_real_escape_string($_COOKIE['email']);
}
else{
	echo '
	<ul class="nav">
		<li>
			<a class="brand" href="../index.php">Greasy Web</a>
		</li>
		<li class="divider-vertical"></li>
	</ul>';
}

?>
