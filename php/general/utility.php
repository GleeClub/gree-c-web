<?php
/**** Utility functions ****/

function encrypt2($string)
{
	return base64_encode($string ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.");
}

function decrypt2($string)
{
	return base64_decode($string) ^ "12345678900987654321qwertyuiopasdfghjklzxcvbnm,.";
}

function cookie_string($value)
{
	global $sessionkey;
	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $value, MCRYPT_MODE_ECB));
}

function valid_date($string)
{
	$re_date = '/^\s*\d\d\d\d-\d\d-\d\d\s*$/';
	if (! preg_match($re_date, $string)) return false;
	$arr = preg_split('/-/', $string);
	return checkdate($arr[1], $arr[2], $arr[0]);
	return true;
}

function valid_time($string)
{
	$re_time = '/^\s*(1[012]|0?[0-9])(:[0-5][0-9])?\s*[AaPp][Mm]\s*$/';
	return preg_match($re_time, $string);
}

/**** Semester and member info functions ****/

function getCurrentSemester() {
	$sql = "SELECT semester FROM variables";
	$arr = mysql_fetch_array(mysql_query($sql));
	return $arr['semester'];
}

function dropdown($options, $name, $selected = '', $disabled = 0)
{
	$ret = "<select name='$name' class='$name'" . ($disabled ? " disabled" : "") . ">";
	foreach ($options as $value => $option) $ret .= "<option value='$value'" . ($value == $selected ? " selected" : "") . ">$option</option>";
	$ret .= "</select>";
	return $ret;
}

function typeahead($options, $name, $value = '') // TODO Make this work
{
	$id = $name . rand();
	return "<input type='text' name='$name' id='$id' value='$value' data-provide='typeahead' autocomplete='off'><script>$('#$id').typeahead(source: typeaheadCallback)</script>";
}

function radio($options, $name, $selected = '', $disabled = 0)
{
	$ret = "";
	foreach ($options as $value => $option) $ret .= "<span class='radio-option'><input type='radio' name='$name' value='$value'" . ($selected == $value ? " checked" : "" ) . ($disabled ? " disabled" : "") . "> $option</span>";
	return $ret;
}

// First "Pref" Last if pref exists && pref != first
function completeNameFromEmail($email) {
	if ($email == '') return '';
	$sql = "SELECT firstName, lastName, prefName FROM `member` WHERE email='$email';";
	$res = mysql_fetch_array(mysql_query($sql));
	if(!empty($res['prefName']) && $res['firstName'] != $res['prefName'])
		return $res['firstName'] . ' "' . $res['prefName'] . '" ' . $res['lastName'];
	else
		return $res['firstName'] . " " . $res['lastName'];
}

function fullNameFromEmail($email) {
	return firstNameFromEmail($email) . " " . lastNameFromEmail($email);
}

function firstNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT firstName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["firstName"];
}

function prefNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT prefName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	if ($result["prefName"] == '') return firstNameFromEmail($email);
	return $result["prefName"];
}

function lastNameFromEmail($email){
	if ($email == '') return '';
	$sql = "SELECT lastName FROM `member` WHERE email='$email';";
	$result= mysql_fetch_array(mysql_query($sql));
	return $result["lastName"];
}

function prefFullNameFromEmail($email){
	return prefNameFromEmail($email).' '.lastNameFromEmail($email);
}

function positions($email)
{
	global $SEMESTER, $CHOIR; // TODO Semester filtering
	$result = mysql_query("select `role`.`name` from `role`, `memberRole` where `memberRole`.`member` = '" . mysql_real_escape_string($email) . "' and `memberRole`.`role` = `role`.`id` and (`role`.`choir` = '$CHOIR' or `role`.`name` = 'Webmaster') order by `role`.`rank` asc");
	if (mysql_num_rows($result) == 0) return array("Member");
	$ret = array();
	while ($row = mysql_fetch_array($result)) $ret[] = $row["name"];
	return $ret;
}

function hasPosition($email, $position)
{
	if ($position == "Member")
	{
		if (mysql_num_rows(mysql_query("select * from `member` where `email` = '" . mysql_real_escape_string($email) . "'"))) return true; # TODO Active semester
		return false;
	}
	if (array_search($position, positions($email)) !== false) return true;
	return false;
}

function getPosition($position = "Member")
{
	global $SEMESTER, $CHOIR; // TODO Semester filtering
	$ret = array();
	if ($position == "Member")
	{
		$result = mysql_query("select `email` from `member`");
		while ($row = mysql_fetch_array($result)) $ret[] = $row["email"];
		return $ret;
	}
	$result = mysql_query("select `memberRole`.`member` as `member` from `role`, `memberRole` where `role`.`name` = '" . mysql_real_escape_string($position) . "' and `role`.`id` = `memberRole`.`role` and `role`.`choir` = '$CHOIR'");
	while($row = mysql_fetch_array($result)) $ret[] = $row["member"];
	return $ret;
}

function profilePic($email)
{
	$default = "http://lorempixel.com/g/256/256";
	if ($email == '') return $default;
	$sql = "SELECT picture FROM member WHERE email='$email';";
	$result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
	if ($result['picture'] == '') return $default;
	else return $result['picture'];
}

function sectionFromEmail($email, $friendly = 0, $semester = "")
{
	global $SEMESTER, $CHOIR;
	if ($semester == "") $semester = $SEMESTER;
	if ($email == '') return ($friendly ? "" : 0);
	$sql = mysql_query("select `sectionType`.`id`, `sectionType`.`name` from `activeSemester`, `sectionType` where `activeSemester`.`member` = '$email' and `activeSemester`.`section` = `sectionType`.`id` and `activeSemester`.`semester` = '$semester' and `activeSemester`.`choir` = '$CHOIR'");
	if (mysql_num_rows($sql) == 0) return ($friendly ? "" : 0);
	$result = mysql_fetch_array($sql, MYSQL_ASSOC);
	return $friendly ? $result['name'] : $result['id'];
}

function enrollment($email, $semester = '')
{
	global $SEMESTER, $CHOIR;
	if ($semester == '') $semester = $SEMESTER;
	$query = mysql_query("select `enrollment` from `activeSemester` where `member` = '$email' and `semester` = '$semester' and `choir` = '$CHOIR'");
	if (mysql_num_rows($query) != 1) return "inactive";
	$result = mysql_fetch_array($query);
	return $result['enrollment'];
}

function hasPermission($perm, $eventType = "")
{
	// FIXME Issues with mysql_real_escape-ing zero or multiple times
	global $USER, $CHOIR;
	$query = mysql_query("select `role`.`name` as `roleName` from `role`, `rolePermission` where `rolePermission`.`permission` = '$perm' and `rolePermission`.`role` = `role`.`id` and `role`.`choir` = '$CHOIR'" . ($eventType == "" ? "" : " and `rolePermission`.`eventType` = '$eventType'"));
	if (! $query) die("Permission check failed: Failed to fetch permitted roles: " . mysql_error());
	$allowed = [];
	while ($row = mysql_fetch_array($query)) $allowed[] = $row["roleName"];
	if (in_array("Any", $allowed)) return true;
	if (! $USER) return false;
	if (in_array("Member", $allowed)) return true;
	$query = mysql_query("select `role`.`name` as `roleName` from `role`, `memberRole` where `memberRole`.`member` = '$USER' and `memberRole`.`role` = `role`.`id` and `role`.`choir` = '$CHOIR'"); // TODO I feel like we could combine these two queries.
	if (! $query) die("Permission check failed: Failed to fetch member roles: " . mysql_error());
	$held = [];
	while ($row = mysql_fetch_array($query)) $held[] = $row["roleName"];
	if (in_array("President", $held) || in_array("Webmaster", $held)) return true;
	if (count(array_intersect($allowed, $held)) > 0) return true;
	return false;
}

function hasEventTypePermission($perm, $type = "any", $sect = 0)
{
	// TODO Special types any and all
	global $USER;
	switch ($perm) {
		case "view":
			return true; // TODO
		case "view-private":
			return hasPermission("view-event-private-details", $type);
		case "create":
		case "modify":
		case "delete":
			return hasPermission("$perm-event", $type);
		case "view-attendance":
		case "edit-attendance":
			if (hasPermission($perm)) return true;
			if (sectionFromEmail($user) != $sect) return false;
			return hasPermission("$perm-own-section");
		default: die("Unknown event permission $perm");
	}
}

function hasEventPermission($perm, $event)
{
	global $USER;
	$query = mysql_query("select `section`, `type` from `event` where `eventNo` = '$event'");
	if (! $query) die("Permission check failed: " . mysql_error());
	if (mysql_num_rows($query) != 1) die("Permission check failed: no matching event");
	$ev = mysql_fetch_array($query);
	return hasEventTypePermission($perm, $ev["type"], $ev["section"]);
}

/*function canEditEvents($email, $type = "any")
{
	if (isUber($email)) return true;
	$permissions = array(
		"any" => array("Ombudsman", "Liaison"),
		"ombuds" => array("Ombudsman"),
		"volunteer" => array("Liaison"),
		"tutti" => array("Liaison")
	);
	if (! array_key_exists($type, $permissions)) return false;
	foreach (positions($email) as $pos) if (in_array($pos, $permissions[$type])) return true;
	return false;
}

function attendancePermission($email, $event)
{
	if (isOfficer($email)) return true; # FIXME
	if (! hasPosition($email, "Section Leader")) return false;
	$result = mysql_fetch_array(mysql_query("select `section`, `type` from `event` where `eventNo` = '$event'"));
	if ($result['type'] != 'sectional') return false;
	$eventSection = $result['section'];
	if ($eventSection == 0) return true;
	if (sectionFromEmail($email) == $eventSection) return true;
	return false;
}*/

function getMemberAttribute($attribute, $email){
        $sql = "SELECT $attribute FROM member WHERE email='$email';";
        $result = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
        return $result[$attribute];
}

function members($cond = "")
{
	global $SEMESTER, $CHOIR;
	$ret = array("" => "(nobody)");
	$sql = "";
	if ($cond == "active") $sql = "select `member`.`firstName`, `member`.`lastName`, `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = '$SEMESTER' and `activeSemester`.`choir` = '$CHOIR' order by `member`.`lastName` asc";
	else $sql = "select `firstName`, `lastName`, `email` from `member` order by `lastName` asc";
	$results = mysql_query($sql);
	while ($row = mysql_fetch_array($results)) $ret[$row['email']] = $row['lastName'] . ", " . $row['firstName'];
	return $ret;
}

function memberDropdown($member = '')
{
	return dropdown(members(), "member", $member);
	//return typeahead(members(), "member", $member);
}

function semesters()
{
	$ret = array();
	$results = mysql_query("select `semester` from `semester` order by `beginning` desc");
	while ($row = mysql_fetch_array($results)) $ret[$row['semester']] = $row['semester'];
	return $ret;
}

function fee($type)
{
	global $CHOIR;
	$query = mysql_query("select `amount` from `fee` where `choir` = '$CHOIR' and `id` = '$type'");
	if (mysql_num_rows($query) == 0) return 0;
	$row = mysql_fetch_array($query);
	return $row["amount"];
}

function semesterDropdown()
{
	GLOBAL $SEMESTER;
	return dropdown(semesters(), "semester", $SEMESTER);
}

function sections($choir = '')
{
	$ret = array();
	global $CHOIR;
	if ($choir == '') $choir = $CHOIR;
	$results = mysql_query("select * from `sectionType` where (`choir` = '$choir' or `choir` is null) order by `id` desc");
	while ($row = mysql_fetch_array($results)) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function uniforms()
{
	$ret = array();
	global $CHOIR;
	$result = mysql_query("select * from `uniform` where `choir` = '$CHOIR'");
	while ($row = mysql_fetch_array($result)) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function choirs()
{
	$ret = array();
	$result = mysql_query("select * from `choir`");
	while ($row = mysql_fetch_array($result)) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function choirname($CHOIR)
{
	if (! $CHOIR) return "Georgia Tech Choirs";
	$row = mysql_fetch_array(mysql_query("select `name` from `choir` where `id` = '$CHOIR'"));
	return $row["name"];
}

/**** Misc ****/

// Delete the file with a given ID from the repertoire repository
function repertoire_delfile($id)
{
	global $docroot, $musicdir;
	$query = "select `target`, `type` from `songLink` where `id` = '$id'";
	$result = mysql_fetch_array(mysql_query($query));
	$file = $result[0];
	if ($file == '') return true;
	$type = $result[1];
	$query = "select `storage` from `mediaType` where `typeid` = '$type'";
	$result = mysql_fetch_array(mysql_query($query));
	if ($result[0] != 'local') return true;
	if (strpos($file, '/') !== false) return false;
	return unlink($docroot . $musicdir . "/" . $file);
}

function loginBlock(){
$html = '
	<div class="span3 block">
		<form class="form-inline" action="php/checkLogin.php" method="post">
		  <input type="text" class="input-medium" id="signInEmail" placeholder="gburdell3@gatech.edu" name="email" />
		  <input type="password" class="input-medium" id="signInPassword" placeholder="password" name="password" />
		  <button type="submit" value="Sign In" class="btn">Sign in</button>
		</form>
		<a href="#forgotPassword">Forgot Password?</a>
	</div>
';
echo $html;
}

function todoBlock($userEmail, $form, $list)
{
	$html = '';
	if ($form)
	{
		if(hasPermission("add-multi-todo"))
		{
			$html .= "<p>
				Names: <input id='multiTodo'>
				Todo: <br /><input id='todoText'>
				<br /><button class='btn' id='multiTodoButton'>Add Todo</button>
			</p>";

		}
		else
		{
			$html .= "<p>
				<input id='newTodo'>
				<button class='btn' id='newTodoButton'>Add Todo</button>
			</p>";
		}
	}
	if ($list)
	{
		$html .= "<div id='todos'>";
		//$sql = "SELECT * FROM `todoMembers` where memberID='$userEmail' ORDER BY todoID ASC;";
		$sql = "select todo.id, todo.text from `todo`, `todoMembers` where todo.id = todoMembers.todoID and todo.completed = '0' and todoMembers.memberID = '$userEmail' order by todo.id asc";
		$todos = mysql_query($sql);
		while ($row = mysql_fetch_array($todos, MYSQL_ASSOC)){
			$id = $row['id']; //$row['todoID'];
			$text = $row['text']; //$text['text'];
			$html .= "<div class='block'><label class='checkbox'><input type='checkbox' id='$id'> $text</label></div>";
		}
		$html .= "</div>";
	}
	return $html;
}
?>
