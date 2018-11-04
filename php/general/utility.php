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
	if ($email == "") return "";
	$res = query("select `firstName`, `lastName`, `prefName` from `member` where `email` = ?", [$email], QONE);
	if (! $res) return "";
	if (! empty($res['prefName']) && $res['firstName'] != $res['prefName'])
		return $res['firstName'] . ' "' . $res['prefName'] . '" ' . $res['lastName'];
	else
		return $res['firstName'] . " " . $res['lastName'];
}

function fullNameFromEmail($email) {
	if ($email == "") return "";
	return firstNameFromEmail($email) . " " . lastNameFromEmail($email);
}

function firstNameFromEmail($email){
	if ($email == "") return "";
	$res = query("select `firstName` from `member` where `email` = ?", [$email], QONE);
	if (! $res) return "";
	return $res["firstName"];
}

function prefNameFromEmail($email){
	if ($email == "") return "";
	$res = query("select `firstName`, `prefName` from `member` where `email` = ?", [$email], QONE);
	if (! $res) return "";
	if ($res["prefName"] == '') return $res["firstName"];
	return $res["prefName"];
}

function lastNameFromEmail($email){
	if ($email == "") return "";
	$res = query("select `lastName` from `member` where `email` = ?", [$email], QONE);
	if (! $res) return "";
	return $res["lastName"];
}

function prefFullNameFromEmail($email){
	if ($email == "") return "";
	return prefNameFromEmail($email) . " " . lastNameFromEmail($email);
}

function positions($email)
{
	global $SEMESTER, $CHOIR; // TODO Semester filtering
	$result = query("select `role`.`name` from `role`, `memberRole` where `memberRole`.`member` = ? and `memberRole`.`role` = `role`.`id` and (`role`.`choir` = ? or `role`.`name` = 'Webmaster') order by `role`.`rank` asc", [$email, $CHOIR], QALL);
	if (count($result) == 0) return array("Member");
	$ret = array();
	foreach ($result as $row) $ret[] = $row["name"];
	return $ret;
}

function hasPosition($email, $position)
{
	if ($position == "Member") return query("select * from `member` where `email` = ?", [$email], QCOUNT) > 0; // TODO Active semester
	if (array_search($position, positions($email)) !== false) return true;
	return false;
}

function getPosition($position = "Member")
{
	global $SEMESTER, $CHOIR; // TODO Semester filtering
	$ret = array();
	if ($position == "Member")
	{
		foreach (query("select `email` from `member`", [], QALL) as $row) $ret[] = $row["email"];
		return $ret;
	}
	foreach (query("select `memberRole`.`member` as `member` from `role`, `memberRole` where `role`.`name` = ? and `role`.`id` = `memberRole`.`role` and `role`.`choir` = ?", [$position, $CHOIR], QALL) as $row) $ret[] = $row["member"];
	return $ret;
}

function profilePic($email)
{
	$default = "http://lorempixel.com/g/256/256";
	if ($email == "") return $default;
	$res = query("select `picture` from `member` where `email` = ?", [$email], QONE);
	if (! $res) return $default;
	$picture = $res["picture"];
	if ($picture == "") return $default;
	else return $picture;
}

function sectionFromEmail($email, $friendly = 0, $semester = "")
{
	global $SEMESTER, $CHOIR;
	if ($semester == "") $semester = $SEMESTER;
	if ($email == "") return ($friendly ? "" : 0);
	$result = query("select `sectionType`.`id`, `sectionType`.`name` from `activeSemester`, `sectionType` where `activeSemester`.`member` = ? and `activeSemester`.`section` = `sectionType`.`id` and `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ?", [$email, $semester, $choir], QONE);
	if (! $result) return ($friendly ? "" : 0);
	return $friendly ? $result["name"] : $result["id"];
}

function enrollment($email, $semester = "")
{
	global $SEMESTER, $CHOIR;
	if ($semester == "") $semester = $SEMESTER;
	$result = query("select `enrollment` from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$email, $semester, $choir], QONE);
	if (! $result) return "inactive";
	return $result["enrollment"];
}

function hasPermission($perm, $eventType = "any")
{
	global $USER, $CHOIR;
	$allowed = [];
	$basesql = "select `role`.`name` as `roleName` from `role`, `rolePermission` where `rolePermission`.`permission` = ? and `rolePermission`.`role` = `role`.`id` and `role`.`choir` = ?";
	if ($eventType == "any") $query = query($basesql, [$perm, $choir], QALL);
	else $query = query($basesql . " and (`rolePermission`.`eventType` = ? or `rolePermission`.`eventType` is null)", [$perm, $choir, $eventType], QALL);
	foreach ($query as $row) $allowed[] = $row["roleName"];
	if (in_array("Any", $allowed)) return true;
	if (! $USER) return false;
	if (in_array("Member", $allowed)) return true;
	$held = [];
	foreach (query("select `role`.`name` as `roleName` from `role`, `memberRole` where `memberRole`.`member` = ? and `memberRole`.`role` = `role`.`id` and `role`.`choir` = ?", [$USER, $CHOIR], QALL) as $row) $held[] = $row["roleName"];
	//echo("Permission: $perm for $eventType<br>Allowed roles: "); print_r($allowed); echo("<br>Held roles: "); print_r($held); echo("<br>");
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
			if (hasPermission($perm, $type)) return true;
			if (sectionFromEmail($USER) != $sect) return false;
			return hasPermission("$perm-own-section", $type);
		default: die("Unknown event permission $perm");
	}
}

function hasEventPermission($perm, $event)
{
	global $USER;
	$ev = query("select `section`, `type` from `event` where `eventNo` = ?", $event, QONE);
	if (! $ev) die("Permission check failed: no matching event");
	return hasEventTypePermission($perm, $ev["type"], $ev["section"]);
}

function getMemberAttribute($attribute, $email)
{
	$valid = [];
	foreach (query("show columns from `member`", [], QALL) as $row) $valid[] = $row["Field"];
	if (! in_array($attribute, $valid)) die("Invalid member attribute \"$attribute\"");
	$res = query("select `$attribute` from `member` where `email` = ?", [$email], QONE);
	if (! $res) die("No such member");
	return $res[$attribute];
}

function members($cond = "")
{
	global $SEMESTER, $CHOIR;
	$ret = array("" => "(nobody)");
	$res = [];
	if ($cond == "active") $res = query("select `member`.`firstName`, `member`.`lastName`, `member`.`email` from `member`, `activeSemester` where `member`.`email` = `activeSemester`.`member` and `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ? order by `member`.`lastName` asc", [$SEMESTER, $CHOIR], QALL);
	else $res = query("select `firstName`, `lastName`, `email` from `member` order by `lastName` asc", [], QALL);
	foreach ($res as $row) $ret[$row["email"]] = $row["lastName"] . ", " . $row["firstName"];
	return $ret;
}

function memberDropdown($member = "")
{
	return dropdown(members(), "member", $member);
	//return typeahead(members(), "member", $member);
}

function semesters()
{
	$ret = array();
	foreach (query("select `semester` from `semester` order by `beginning` desc", [], QALL) as $row) $ret[$row["semester"]] = $row["semester"];
	return $ret;
}

function fee($type)
{
	global $CHOIR;
	$row = query("select `amount` from `fee` where `choir` = ? and `id` = ?", [$CHOIR, $type], QONE);
	if (! $row) return 0;
	return $row["amount"];
}

function semesterDropdown()
{
	GLOBAL $SEMESTER;
	return dropdown(semesters(), "semester", $SEMESTER);
}

function sections($choir = "")
{
	global $CHOIR;
	if ($choir == "") $choir = $CHOIR;
	$ret = array();
	foreach (query("select * from `sectionType` where (`choir` = ? or `choir` is null) order by `id` desc", [$choir], QALL) as $row) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function uniforms()
{
	global $CHOIR;
	$ret = array();
	foreach (query("select * from `uniform` where `choir` = ?", [$CHOIR], QALL) as $row) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function choirs()
{
	$ret = array();
	foreach (query("select * from `choir`", [], QALL) as $row) $ret[$row["id"]] = $row["name"];
	return $ret;
}

function choirname($choir)
{
	if (! $choir) return "Georgia Tech Choirs";
	$res = query("select `name` from `choir` where `id` = ?", [$choir], QONE);
	if (! $res) return "Georgia Tech Choirs";
	return $res["name"];
}

/**** Misc ****/

// Delete the file with a given ID from the repertoire repository
function repertoire_delfile($id)
{
	global $docroot, $musicdir;
	$res = query("select `songLink`.`target`, `mediaType`.`storage` from `songLink`, `mediaType` where `songLink`.`id` = ? and `mediaType`.`typeid` = `songLink`.`type`", [$id], QONE);
	if (! $res) return true;
	$file = $res["target"];
	if ($file == "") return true;
	if ($res["storage"] != "local") return true;
	if (strpos($file, "/") !== false) return false;
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
		foreach (query("select todo.id, todo.text from `todo`, `todoMembers` where todo.id = todoMembers.todoID and todo.completed = '0' and todoMembers.memberID = ? order by todo.id asc", [$userEmail], QALL) as $row)
		{
			$id = $row['id']; //$row['todoID'];
			$text = $row['text']; //$text['text'];
			$html .= "<div class='block'><label class='checkbox'><input type='checkbox' id='$id'> $text</label></div>";
		}
		$html .= "</div>";
	}
	return $html;
}
?>
