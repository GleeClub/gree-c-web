<?php
	require_once('./functions.php');
	$userEmail = $_COOKIE['email'];
	mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
	mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");

	$sql = "select count(email) as count from member where confirmed='1'";
	$result = mysql_fetch_array(mysql_query($sql));
	$count = $result['count'];

	$sql = "select count(email) as count from member where confirmed='1' and section='Tenor 1'";
	$result = mysql_fetch_array(mysql_query($sql));
	$t1Count = $result['count'];

	$sql = "select count(email) as count from member where confirmed='1' and section='Tenor 2'";
	$result = mysql_fetch_array(mysql_query($sql));
	$t2Count = $result['count'];

	$sql = "select count(email) as count from member where confirmed='1' and section='Baritone'";
	$result = mysql_fetch_array(mysql_query($sql));
	$b1Count = $result['count'];

	$sql = "select count(email) as count from member where confirmed='1' and section='Bass'";
	$result = mysql_fetch_array(mysql_query($sql));
	$b2Count = $result['count'];

	$sql = "select firstName,lastName,email,section from member where confirmed='1' order by lastName,firstName,section
";
	$result = mysql_query($sql);

	$html = "
	<table id=memberInfoTable>
		<tr>
			<td colspan='2' style='text-align: right'>T1's:</td>
			<td colspan='2'>$t1Count</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align: right'>T2's:</td>
			<td colspan='2'>$t2Count</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align: right'>B1's:</td>
			<td colspan='2'>$b1Count</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align: right'>B2's:</td>
			<td colspan='2'>$b2Count</td>
		</tr>
		<tr>
			<td colspan='2' style='text-align: right'>Number of members:</td>
			<td colspan='2'>$count</td>
		</tr>
		<tr>
			<td style='text-align: center'>First Name</td>
			<td style='text-align: center'>Last Name</td>
			<td style='text-align: center'>Email</td>
			<td style='text-align: center'>Section</td>
		</tr>";
	
	while($row = mysql_fetch_array($result)){
		$firstName = $row['firstName'];
		$lastName = $row['lastName'];
		$email = $row['email'];
		$section = $row['section'];

		$html.="
		<tr>
			<td style='text-align: left'>$firstName</td>
			<td style='text-align: left'>$lastName</td>
			<td style='text-align: left'>$email</td>
			<td style='text-align: left'>$section</td>
		</tr>
		";
	}

	$html.="
	</table>";
	
	echo $html;

?>