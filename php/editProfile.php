<div class="span11 block">
<?php
require_once('./functions.php');
if ($USER && !$CHOIR) die("You need to choose a choir from the dropdown menu in the top right corner before editing your profile.");
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
if ($USER)
{
	$userinfo = query("select * from `member` where `email` = ?", [$USER], QONE);
	if (! $userinfo) err("Invalid user");
	$row = query("select `enrollment` from `activeSemester` where `member` = ? and `semester` = ?", [$USER, $SEMESTER], QONE);
	if (! $row) $userinfo["registration"] = "inactive";
	else $userinfo["registration"] = $row["enrollment"];
	$query = query("select `section` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$USER, $SEMESTER, $CHOIR], QONE);
	if ($query) $userinfo["section"] = $query["section"];
}

$fields = array(
	// array(machine name, friendly name, field type, required, choices)
	array("firstName", "First Name", "text", 1),
	array("prefName", "Preferred Name", "text", 0),
	array("lastName", "Last Name", "text", 1),
	array("section", "Section", "select", 1, $USER ? sections() : array()),
	array("email", "Email", "text", 1),
	array("password", "Password", "password", 1),
	array("password2", "Confirm password", "password", 1),
	array("phone", "Phone number (digits only)", "text", 1),
	array("picture", "Picture (URL)", "text", 0),
	array("registration", "Are you in the class or club?", "radio", 1, array("class" => "Class", "club" => "Club")),
	array("passengers", "How many passengers (<i>aside from yourself</i>) can ride in your car?  (0 if you don't have a car)", "number", 1),
	array("onCampus", "Do you live on campus?", "bool", 1),
	array("location", "Where do you live? (for carpool purposes)", "text", 1),
	array("about", "About you (public)", "text", 0),
	array("major", "Your major", "text", 1),
	array("minor", "Your minor", "text", 0),
	array("hometown", "Your hometown", "text", 1),
	array("techYear", "How many years have you been at GT?", "number", 0),
	array("gChat", "GChat screen name", "text", 0),
	array("twitter", "Twitter handle", "text", 0),
	array("gatewayDrug", "How did you hear about this organization?", "text", 0),
	array("conflicts", "Any conflicts we should know about", "text", 0),
);
if (! $USER) array_splice($fields, 3, 0, array(array("choir", "Organization (If you are in several, choose one to start and add yourself to the others later)", "select", 1, choirs())));

$form = "";
foreach ($fields as $field)
{
	$name = $field[0];
	echo "<div class='control-group'><label class='control-label wider" . ($field[3] ? " required" : "") . "' for='" . $name . "'>" . $field[1] . "</label><div class='controls'>";
	if ($field[2] == "text" || $field[2] == "password" || $field[2] == "number" || $field[2] == "date") echo "<input type='" . $field[2] . "' name='" . $name . "'" . ($USER && $field[2] != "password" ? " value='" . $userinfo[$name] . "'" : "") . ">";
	else if ($field[2] == "bool") echo "<input type='checkbox' name='" . $name . "'" . ($USER && $userinfo[$name] != 0 ? " checked" : "") . ">";
	else if ($field[2] == "select") echo dropdown($field[4], $name, ($USER ? $userinfo[$name] : ""));
	else if ($field[2] == "radio") echo radio($field[4], $name, ($USER ? $userinfo[$name] : ""));
	echo "</div></div>";
}
echo $form;
?>
<button type="button" class="btn" id="editProfileSubmit">Submit</button>
</form>
</div>
