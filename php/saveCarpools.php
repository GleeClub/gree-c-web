<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$eventNo = $_SESSION['eventNo'];
$carpools = json_decode($_POST['carpools'], true);
//$bigSQLstring='';
foreach($carpools as $value){
	$driver = $value['driver']['email'];
	$id = $value['id'];
	if($id !== 'undefined'){
		//delete the driver
		$sql = "DELETE FROM `carpool` WHERE carpoolID=$id;";//do i need to be more specific?
		echo $sql;
		//$bigSQLstring .= $sql;
		mysql_query($sql);
		
		//add the new driver
		$sql = "INSERT INTO `carpool` (carpoolID, driver, eventNo) VALUES ($id, '".$driver."', $eventNo);";
		//$bigSQLstring .= $sql;
		echo ' '.$sql;
		mysql_query($sql);
		
		//$passengers = '';
		//delete the ridesin
		$sql = "DELETE FROM `ridesin` WHERE carpoolID=$id;";
		//$bigSQLstring .= $sql;
		echo ' '.$sql;
		mysql_query($sql);
		
		//add the driver to ridesin, if old driver
		$sql = "INSERT INTO `ridesin` (memberID, carpoolID) VALUES ('".$driver."', ".$id.");";
		//$bigSQLstring .= $sql;
		echo ' '.$sql;
		mysql_query($sql);
	}
	else{
		if($driver !== 'undefined'){ //if it's not a blank carpool
			//make new carpool
			//add the new driver
			$sql = "INSERT INTO `carpool` (driver, eventNo) VALUES ('".$driver."', $eventNo);";
			//$bigSQLstring .= $sql;
			echo ' '.$sql;
			mysql_query($sql);
			
			//add the driver to ridesin, if new driver
			$sql = "SELECT `carpoolID` FROM `carpool` WHERE driver='$driver' AND eventNo=$eventNo;";
			$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
			$sql = "INSERT INTO `ridesin` (memberID, carpoolID) VALUES ('".$driver."', ".$result['carpoolID'].");";
			//$bigSQLstring .= $sql;
			$id = $result['carpoolID'];
			echo ' '.$sql;
			mysql_query($sql);
		}
	}
	if($driver !== 'undefined'){
		foreach($value['passengers'] as $passenger){
			//add each passenger to the ridesin
			//$passengers += $passenger['email'].', ';
			/*if($id == 'undefined'){
				$id = $result['carpoolID'];
			}*/
			$sql = "INSERT INTO `ridesin` (memberID, carpoolID) VALUES ('".$passenger['email']."', $id);";
			//$bigSQLstring .= $sql;
			echo ' '.$sql;
			mysql_query($sql);
		}
	}
}
//print_r($carpools);
//echo $bigSQLstring;
//mysql_query($bigSQLstring);

?>