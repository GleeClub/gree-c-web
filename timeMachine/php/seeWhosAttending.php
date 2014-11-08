<?php
session_start();
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];
$eventNo = $_SESSION['eventNo'];

$shouldHtml='<table class="every-other" width="100%"><tr><td><h3>Should Attend</h3></td></tr>';
$shouldntHtml='<table class="every-other" width="100%"><tr><td><h3>Shouldn\'t Attend</h3></td></tr>';
$sql = 'SELECT prefName,lastName,section,`attends`.`confirmed` as confirmed,section,shouldAttend FROM `attends`,`member` WHERE eventNo='.$eventNo.' AND memberID=email and member.confirmed=\'1\' order by confirmed DESC,section ASC,lastName ASC,firstName ASC';
$results = mysql_query($sql);
while($row = mysql_fetch_array($results, MYSQL_ASSOC)){
	$section = $row['section'];
	$prefName = $row['prefName'];
	$lastName = $row['lastName'];
	$confirmed = $row['confirmed'];

	if($section=='Tenor 1')
		$section='T1';
	else if($section=='Tenor 2')
		$section='T2';
	else if($section=='Baritone')
		$section='B1';
	else if($section=='Bass')
		$section='B2';

	if($row['shouldAttend'] == '1'){
		$shouldHtml = $shouldHtml.'
			<tr>
				<td>'.$prefName.' '.$lastName.'</td>
				<td style="text-align:center;width: 40px;">'.$section.'</td>
				<td style="text-align:center">'.($confirmed == '0' ? '<span class="label label-warning">not confirmed</span>' : '<span class="label label-success">confirmed</span>').'</td>
			</tr>
		';
	}
	else{
		$shouldntHtml = $shouldntHtml.'
			<tr>
				<td>'.$prefName.' '.$lastName.'</td>
				<td style="text-align:center;width: 40px;">'.$section.'</td>
				<td style="text-align:center">'.($confirmed == '0' ? '<span class="label label-warning">not confirmed</span>' : '<span class="label label-success">confirmed</span>').'</td>
			</tr>
		';
	}
}

$shouldntHtml .= '</table>';
$shouldHtml .= '</table>';

echo $shouldHtml.$shouldntHtml;
?>