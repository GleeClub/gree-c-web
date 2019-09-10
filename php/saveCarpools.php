<?php
require_once('functions.php');
$eventNo = $_POST['eventNo'];
$carpools = json_decode($_POST['carpools'], true);
print_r($carpools);
//$bigSQLstring='';
foreach($carpools as $value)
{
	$driver = $value['driver']['email'];
	$id = $value['id'];
	if ($id !== 'undefined')
	{
		query("delete from `ridesin` where `carpoolID` = ?", [$id]); //delete the ridesin
		query("delete from `carpool` where `carpoolID` = ?", [$id]); //delete the driver
	}
	if ($driver !== 'undefined')
	{ //if it's not a blank carpool
		//make new carpool
		$id = query("insert into `carpool` (`driver`, `eventNo`) values (?, ?)", [$driver, $eventNo], QID); //add the new driver
		query("insert into `ridesin` (`memberID`, `carpoolID`) values (?, ?)", [$driver, $id]); //add the driver to ridesin
		foreach($value['passengers'] as $passenger)
		{
			query("insert into `ridesin` (`memberID`, `carpoolID`) values (?, ?)", [$passenger["email"], $id]); //add each passenger to the ridesin
		}
	}
}
?>
