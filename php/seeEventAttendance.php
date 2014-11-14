<?php session_start();?>

<head>
	<style>
		#title{
			font-size:30px;
			text-align: center;
		}
		#form{
			table-layout: fixed;
			word-wrap: break-word;
		}
		.cellwrap{
			width: 20%;
		}
		.center{
			width: 20%;
			text-align: center;
		}
		.headings{
			font-weight: bold;
			text-align: center;	
		}
		tr.topborder td {
			border-top: 1pt solid black;
		}
		.topRow {
			font-weight: bold;
			text-align: center;	
			border-top: 1pt solid black;
			border-bottom: 1pt solid black;
			border-left: 1pt solid black;
			border-right: 1pt solid black;
		}
		.data {
			border-top: 1px dotted #000000;
			border-bottom: 1px dotted #000000;
		}
	</style>
</head>

<?php
require_once('./functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

if(isset($_COOKIE['email']))
{
	$userEmail = $_COOKIE['email'];

	if(isset($_POST['eventNo']) && isOfficer($userEmail))
	{
		$eventNo = $_POST['eventNo'];

		$sql = "select `name` from `event` where `eventNo` = '$eventNo'";
		$event = mysql_fetch_array(mysql_query($sql));
		$name = $event['name'];
		
		$html ="<div class='pull-right'><button class='btn' onclick='excuseall($eventNo)'>Excuse All</button></div>
		<p style='text-align: center; font-weight: bold;'>$name Attendance</p> 
		<p id='attendanceList'><table id='$eventNo"."_table'>" . getEventAttendanceRows($eventNo) . "</table></p>";
	}
}
else $html = "<(><)> Something went wrong <(><)>";

echo $html;
?>

