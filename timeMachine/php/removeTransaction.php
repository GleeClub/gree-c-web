<?php
	require_once('./functions.php');
	$userEmail = $_COOKIE['email'];
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

	if(isset($_POST['checked'])){
		$checked = $_POST['checked'];
		$checkedArr = json_decode($checked);
		foreach($checkedArr as $transactionID){
			$sql = "delete from moneyvalue where moneyvalueID='".mysql_real_escape_string($transactionID)."'";
			mysql_query($sql);
		}
	}
	else
		echo "<html>It didn't work ;(</html>";

?>