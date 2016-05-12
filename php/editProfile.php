<div class="span11 block">
<?php
require_once('./functions.php');
echo "<h2>" . ($USER ? "Edit" : "Create") . " Profile</h2>";
?>
<p>Required items are marked with red asterisks.  Note that this registration is not mandatory.  If you are unwilling to provide any of the required information, let an officer know and we will work out alternate means of registration.</p>
<form id="register" class="form-horizontal">
<style>
label.control-label.wider { width: 400px; margin-right: 20px; }
label.control-label.required::after { color: red; content: " *"; }
span.radio-option { margin-right: 30px; }
</style>
<?php
$userinfo = array();
if ($USER) $userinfo = mysql_fetch_array(mysql_query("select * from `member` where `email` = '$USER'"));
$query = mysql_query("select `enrollment` from `activeSemester` where `member` = '$USER' and `semester` = '$SEMESTER'");
if (mysql_num_rows($query) == 0) $userinfo["registration"] = "inactive";
else
{
	$res = mysql_fetch_array($query);
	$userinfo["registration"] = $res['enrollment'];
}

$fields = array(
	// machine name => array(friendly name, field type, required)
	"firstName" => array("First Name", "text", 1),
	"prefName" => array("Preferred Name", "text", 0),
	"lastName" => array("Last Name", "text", 1),
	"section" => array("Section", "select", 1, sections()),
	"email" => array("Email", "text", 1),
	"password" => array("Password", "password", 1),
	"password2" => array("Confirm password", "password", 1),
	"phone" => array("Phone number (digits only)", "text", 1),
	"picture" => array("Picture (URL)", "text", 0),
	"registration" => array("Are you in the class or club?", "radio", 1, array("class" => "Class", "club" => "Club")),
	"passengers" => array("How many passengers (<i>aside from yourself</i>) can ride in your car?  (0 if you don't have a car)", "number", 1),
	"onCampus" => array("Do you live on campus?", "bool", 1),
	"location" => array("Where do you live? (for carpool purposes)", "text", 1),
	"about" => array("About you (public)", "text", 0),
	"major" => array("Your major", "text", 1),
	"minor" => array("Your minor", "text", 0),
	"hometown" => array("Your hometown", "text", 1),
	"techYear" => array("How many years have you been at GT?", "number", 0),
	"gChat" => array("GChat screen name", "text", 0),
	"twitter" => array("Twitter handle", "text", 0),
	"gatewayDrug" => array("How did you hear about this organization?", "text", 0),
	"conflicts" => array("Any conflicts we should know about", "text", 0),
);

$form = "";
foreach ($fields as $name => $field)
{
	echo "<div class='control-group'><label class='control-label wider" . ($field[2] ? " required" : "") . "' for='" . $name . "'>" . $field[0] . "</label><div class='controls'>";
	if ($field[1] == "text" || $field[1] == "password" || $field[1] == "number" || $field[1] == "date") echo "<input type='" . $field[1] . "' name='" . $name . "'" . ($USER && $field[1] != "password" ? " value='" . $userinfo[$name] . "'" : "") . ">";
	else if ($field[1] == "bool") echo "<input type='checkbox' name='" . $name . "'" . ($USER && $userinfo[$name] != 0 ? " checked" : "") . ">";
	else if ($field[1] == "select") echo dropdown($field[3], $name, ($USER ? $userinfo[$name] : ""));
	else if ($field[1] == "radio") echo radio($field[3], $name, ($USER ? $userinfo[$name] : ""));
	echo "</div></div>";
}
echo $form;
?>
<button type="button" class="btn" id="editProfileSubmit">Submit</button>
</form>
</div>
