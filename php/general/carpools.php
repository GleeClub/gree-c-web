<?php
/**** Carpool functions ****/

function nullcheck($res)
{
	if (! $res) die("No such member");
	return $res;
}

function passengerSpots($email)
{
	return nullcheck(query("select `passengers` from `member` where `email` = ?", [$email], QONE))["passengers"];
}

function livesAt($email)
{
	return nullcheck(query("select `location` from `member` where `email` = ?", [$email], QONE))["location"];
}

function phoneNumber($email)
{
	return nullcheck(query("select `phone` from `member` where `email` = ?", [$email], QONE))["phone"];
}

function getSectionTypes()
{
	return query("select * from `sectionType`", [], QALL);
}

function getCarpoolDetails($carpoolId)
{
	return query("select * from `ridesin` where `carpoolID` = ?", [$carpoolId], QALL);
}
?>
