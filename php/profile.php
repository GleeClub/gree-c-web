<?php
require_once('variables.php');
require_once('functions.php');
mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect"); 
mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
$userEmail = $_COOKIE['email'];

$person = $_GET['person'];
$profilePic = profilePic($person);
if($profilePic == ''){
	$profilePic = randomProfilePic();
}

echo "<img class='span5' style='border:1px solid #021a40; margin-right: 10px;' src='$profilePic'>";

echo "<p><b>" .completeNameFromEmail($person)."</b></p>";
echo "<table>";
echo "<tr><td style='color: grey'>Email:</td><td><a href='$person'>$person</a></td></tr>";
echo "<tr><td style='color: grey'>Phone:</td><td><a href='tel:" . phoneNumber($person) . "'>" . phoneNumber($person) . "</a></td></tr>";
echo "<tr><td style='color: grey'>Section:</td><td>".sectionFromEmail($person)."</td></tr>";
echo "<tr><td style='color: grey'>Position:</td><td>".getMemberAttribute('position', $person)."</td></tr>";
echo "<tr><td style='color: grey'>Major:</td><td>".getMemberAttribute('major', $person)."</td></tr>";
echo "<tr><td style='color: grey'>Year (at Tech):</td><td>".getMemberAttribute('techYear', $person)."</td></tr>";
echo "<tr><td style='color: grey'>Year (in Glee):</td><td>".getMemberAttribute('clubYear', $person)."</td></tr>";
if (isOfficer($userEmail)) echo "<tr><td><button class='btn' onclick='chgusr(\"$person\")'>Switch User</button></td><td>&nbsp;</td></tr>";
echo "</table>";
/*
echo "<img class='span5 offset1' src='".$profilePic."' >";
echo "<h2 class='span6 offset5'>".prefFullNameFromEmail($person)."</h2>";
echo "<p class='span6'><strong>Email: </strong>$person</p>";
echo "<p class='span6'><strong>Phone: </strong>".phoneNumber($person)."</p>";
echo "<p class='span6'><strong>Section: </strong>".sectionFromEmail($person)."</p>";
echo "<p class='span6'><strong>Position: </strong>".getMemberAttribute('position', $person)."</p>";
echo "<p class='span6'><strong>Major:</strong> ".getMemberAttribute('major', $person)."</p>";
echo "<p class='span6'><strong>Year (at Tech):</strong> ".getMemberAttribute('techYear', $person)."</p>";
echo "<p class='span6'><strong>Year (in Glee):</strong> ".getMemberAttribute('techYear', $person)."</p>";
*/
?>
