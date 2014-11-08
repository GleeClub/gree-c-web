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
			width: 25%;
		}
		.center{
			width: 25%;
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
	<script type="text/javascript" src="./js/drewJS.js"></script>
</head>

<?php
	
	if(isset($_COOKIE['email'])){
		require_once('./functions.php');
		$userEmail = $_COOKIE['email'];
		mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
		mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	}
	
	if(isset($_POST['eventNo'])){
		$eventNo = $_POST['eventNo'];

		$sql = "select name from event where eventNo='$eventNo'";
		$event = mysql_fetch_array(mysql_query($sql));
		$name = $event['name'];
		
		$html ="<html>
		<p style='text-align: center; font-weight: bold;'>$name Attendance</p> 
		<p id='attendanceList'>
			<table id='$eventNo"."_table'>
				".getEventAttendanceRows($eventNo)."
			</table>
		</p>
		</html>";
	}
	else{
		$html = "<(><)> Something went wrong <(><)>";
	}

	echo $html;
?>

