<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

if(isset($_POST['type']))
	$memberType = $_POST['type'];
else
	$memberType = 'all';

switch($memberType){
	case 'active':
		$c= "confirmed=1";
		break;
	case 'inactive':
		$c= "confirmed=0";
		break;
	default:
		$c= "1";
		break;
}

$sql = "select * from member where $c";

$html = "<table class='table every-other no-highlight' id='editMembersTable'>";
$html .= "
		<thead>
		<tr>
			<th>Confirmed</th>
			<th>First Name</th>
			<th>Pref Name</th>
			<th>Last Name</th>
			<th>Section</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Position</th>
			<th>Picture</th>
			<th>Sectional</th>
			<th>Registration</th>
			<th>Tie</th>
			<th>Tie Number</th>
			<th>Passengers</th>
			<th>On campus</th>
			<th>Location</th>
			<th>About</th>
			<th>Major</th>
			<th>Tech Year</th>
			<th>Club Year</th>
			<th>Gchat</th>
			<th>Twitter</th>
			<th>Gateway Drug</th>
			<th>Conflicts</th>
		</tr>
		</thead>";

//$members = getConfirmedMembers();
$members = mysql_query($sql);

//print_r(mysql_fetch_array($members, MYSQL_ASSOC));
while($row = mysql_fetch_array($members, MYSQL_ASSOC)){
	//use class for the SQL attribute name we're editing
	//use tr's id for the person we're editing
	$html .="<tr id='".$row['email']."'>
		<td class='confirmed'>".($row["confirmed"] == '0' ? '<span data-value="0" class="label">inactive</span>' : '<span data-value="1" class="label label-info">active</span>')."</td>
		<td class='firstName'>".$row["firstName"]."</td>
		<td class='prefName'>".$row["prefName"]."</td>
		<td class='lastName'>".$row["lastName"]."</td>
		<td class='section'>".$row["section"]."</td>
		<td class='email'><a data-value='".$row["email"]."' href='mailto:".$row["email"]."'>".$row["email"]."</a></td>
		<td class='phone'>".$row["phone"]."</td>
		<td class='position'>".positionFromEmail($row["email"])."</td>
		<td class='picture'><img data-value='".$row['picture']."' src='".$row["picture"]."' /></td>
		<td class='sectional'>".$row["sectional"]."</td>
		<td class='registration'>".($row["registration"] == '0' ? '<span data-value="0" class="label">Club</span>' : '<span data-value="1" class="label label-info">Class</span>')."</td>
		<td class='tie'>".($row["tie"] == '0' ? '<span data-value="0" class="label">No tie</span>' : '<span data-value="1" class="label label-warning">Has tie</span>')."</td>
		<td class='tieNum'>".($row["tieNum"] == '-1' ? '<span data-value="-1" class="label label-warning">Thief</span>' : '<span data-value="'.$row['tieNum'].'" class="label">'.$row['tieNum'].'</span>')."</td>
		<td class='passengers'><span data-value='".$row['passengers']."' class='badge'>".$row["passengers"]."</span></td>
		<td class='onCampus'>".($row["onCampus"] == '0' ? '<span data-value="0" class="label">off campus</span>' : '<span data-value="1" class="label label">on campus</span>')."</td>
		<td class='location'>".$row["location"]."</td>
		<td class='about'>".$row["about"]."</td>
		<td class='major'>".$row["major"]."</td>
		<td class='techYear'>".$row["techYear"]."</td>
		<td class='clubYear'>".$row["clubYear"]."</td>
		<td class='gChat'>".$row["gChat"]."</td>
		<td class='twitter'>".$row["twitter"]."</td>
		<td class='gatewayDrug'>".$row["gatewayDrug"]."</td>
		<td class='conflicts'>".$row["conflicts"]."</td>
	";
	$html .= "</tr>";
}

$html .='</table>';
echo $html;

?>