<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];
$eventNo = $_SESSION['eventNo'];

foreach ($_POST as &$value){
	$value = mysql_real_escape_string($value);
}

if(isOfficer($userEmail)){
	$sql='UPDATE `event` SET ';
	if(isset($_POST['Name:'])){
		$sql.='name="'.$_POST['Name:'].'", ';
	}
	if(isset($_POST['Date:'])){
		$date = $_POST['Date:'];
		//$date = str_replace(',', '', $_POST['Date:']); //this might not be necessary
	}
	if(isset($_POST['Call_Time:'])){
		$callTime = $_POST['Call_Time:'];
		//take the day of the week out of the date
		strtok($date," ");
		$shortDate = strtok(" ")." ".strtok(" ")." ".strtok(" ");//date without day o/t week
		$callTime = strtotime($callTime." ".$shortDate);
		$callTime = date('Y-m-d H:i:s', $callTime);//make SQL happy
		$sql.='callTime="'.$callTime.'", ';
	}
	if(isset($_POST['Release_Time:'])){
		$releaseTime = $_POST['Release_Time:'];
		$releaseTime = strtotime($releaseTime." ".$shortDate);
		$releaseTime = date('Y-m-d H:i:s', $releaseTime);//make SQL happy
		$sql.='releaseTime="'.$releaseTime.'", ';
	}
	if(isset($_POST['Uniform:'])){
		$sql.='uniform="'.$_POST['Uniform:'].'", ';
	}
	if(isset($_POST['Comments:'])){
		$sql.='comments="'.$_POST['Comments:'].'", ';
	}
	if(isset($_POST['Location:'])){
		$sql.='location="'.$_POST['Location:'].'", ';
	}
	if(isset($_POST['Point_Value:'])){
		$sql.='pointValue="'.$_POST['Point_Value:'].'", ';
	}
	
	$gigSQL = 'UPDATE `gig` SET ';
	if(isset($_POST['Contact_Name:'])){
		$gigSQL.='contactName="'.$_POST['Contact_Name:'].'" ';
	}
	if(isset($_POST['Contact_Email:'])){
		$gigSQL.='contactEmail="'.$_POST['Contact_Email:'].'", ';
	}
	if(isset($_POST['Contact_Phone:'])){
		$gigSQL.='contactPhone="'.$_POST['Contact_Phone:'].'", ';
	}
	if(isset($_POST['Price:'])){
		$gigSQL.='price="'.$_POST['Price:'].'", ';
	}
	
	//take out last comma
	$gigSQL = substr($gigSQL, 0, -2);
	$sql = substr($sql, 0, -2);
	$gigSQL .=' ';
	$sql .=' ';
	
	$gigSQL .= "WHERE eventNo=$eventNo;";
	$sql .= "WHERE eventNo=$eventNo;";
	//echo $sql." ".$gigSQL;
	mysql_query($sql);
	mysql_query($gigSQL);
	echo $eventNo;
}
else{
	echo "<p>Access Denied.</p>";
}

?>
