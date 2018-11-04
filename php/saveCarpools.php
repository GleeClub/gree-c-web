<?php
require_once('functions.php');
$eventNo = $_POST['eventNo'];
$carpools = json_decode($_POST['carpools'], true);
//$bigSQLstring='';
foreach($carpools as $value){
	$driver = $value['driver']['email'];
	$id = $value['id'];
	if($id !== 'undefined'){
		query("delete from `carpool` where `carpoolID` = ?", [$id]); //delete the driver
		query("insert into `carpool` (`carpoolID`, `driver`, `eventNo`) values (?, ?, ?)", [$id, $driver, $eventNo]); //add the new driver
		query("delete from `ridesIn` where `carpoolID` = ?", [$id]); //delete the ridesin
		query("insert into `ridesin` (`memberID`, `carpoolID`) values (?, ?)", [$driver, $id]); //add the driver to ridesin, if old driver
	}
	else{
		if($driver !== 'undefined'){ //if it's not a blank carpool
			//make new carpool
			query("insert into `carpool` (`driver`, `eventNo`) values (?, ?)", [$driver, $eventNo]); //add the new driver
			query("insert into `ridesin` (`memberID`, `carpoolID`) select ?, `carpoolID` from `carpool` where `driver` = ? and `eventNo` = ?", [$driver, $driver, $eventNo]); //add the driver to ridesin, if new driver
		}
	}
	if($driver !== 'undefined'){
		foreach($value['passengers'] as $passenger){
			query("insert into `ridesin` (`memberID`, `carpoolID`) values (?, ?)", [$passenger["email"], $id]); //add each passenger to the ridesin
		}
	}
}
?>
