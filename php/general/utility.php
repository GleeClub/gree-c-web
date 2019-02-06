<?php
/**** Utility functions ****/

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

function memberName($email, $kind = "full", $member = null)
{
	if (! $member) $member = query("select `firstName`, `lastName`, `prefName` from `member` where `email` = ?", [$email], QONE);
	$first = $member["firstName"];
	$pref = $member["prefName"];
	$last = $member["lastName"];
	$usePref = ($pref) && ($first != $pref);
	switch ($kind)
	{
	case "first": return $first;
	case "pref":
		if ($usePref) return $pref;
		return $first;
	case "last": return $last;
	case "full": // Pref Last
		if ($usePref) return "$pref $last";
		return "$first $last";
	case "complete": // First "Pref" Last
		if ($usePref) return "$first \"$pref\" $last";
		return "$first $last";
	case "real": // First Last
		return "$first $last";
	case "all":
		$full = "$first $last";
		if ($usePref) $full = "$pref $last";
		return array("first" => $first, "pref" => $pref, "last" => $last, "full" => $full);
	}
}

// TODO Try to optimize this -- since it's called on each member for the roster, we want to use as few queries as possible.
// TODO Ensure that all queries are restricted to the current $CHOIR
function memberInfo($member)
{
	global $USER, $SEMESTER, $CHOIR;
	$ret = [];
	$info = query("select * from `member` where `email` = ?", [$member], QONE);
	if (! $info) err("Invalid member");
	$ret["positions"] = positions($member); // TODO
	$ret["name"] = memberName($email, "all", $info);
	if ($info["about"] == "") $ret["about"] = null; // "I don't have a quote";
	else $ret["about"] = $info["about"];
	if ($info["picture"] == "") $ret["picture"] = null; // "http://lorempixel.com/g/256/256";
	else $ret["picture"] = $info["picture"];
	$ret["email"] = $info["email"];
	$ret["phone"] = $info["phone"];
	$ret["location"] = $info["location"];
	$ret["onCampus"] = $info["onCampus"] != "0";
	$ret["car"] = $info["passengers"];
	$ret["major"] = $info["major"];
	$ret["year"] = $info["techYear"];
	$active = query("select * from `activeSemester` where `member` = ? and `semester` = ? and `choir` = ?", [$member, $SEMESTER, $CHOIR], QONE);
	$ret["section"] = $active ? $active["section"] : null;
	if ($USER == $member || hasPermission("view-user-private-details"))
	{
		$semesters = [];
		//foreach (query("select `semester`.`semester` from `activeSemester`, `semester` where `activeSemester`.`member` = ? and `activeSemester`.`semester` = `semester`.`semester` order by `semester`.`beginning` desc", [$member], QALL) as $row)
		//	$semesters[] = $row["semester"];
		//$ret["activeSemesters"] = $semesters;
		$ret["enrollment"] = $active ? $active["enrollment"] : "inactive";
		$attendance = attendance($member);
		$ret["hometown"] = $info["hometown"];
		$ret["gigs"] = $attendance["gigCount"];
		$ret["score"] = $attendance["finalScore"];
	}
	if ($USER == $member || hasPermission("view-transactions"))
	{
		$ret["balance"] = intval(query("select sum(`amount`) as `balance` from `transaction` where `memberID` = ?", [$member], QONE)["balance"]);
		$ret["dues"] = intval(query("select sum(`amount`) as `balance` from `transaction` where `memberID` = ? and `type` = 'dues' and `semester` = ?", [$member, $SEMESTER], QONE)["balance"]);
	}
	return $ret;
}

function listMembers($conditions = ["active"])
{
	global $SEMESTER, $CHOIR;
	$condqueries = array(
	"active" => array(
		"exists (select * from `activeSemester` where `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ? and `activeSemester`.`member` = `member`.`email`)",
		[$SEMESTER, $CHOIR]
	),
	"inactive" => array(
		"not exists (select * from `activeSemester` where `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ? and `activeSemester`.`member` = `member`.`email`)",
		[$SEMESTER, $CHOIR]
	),
	"class" => array(
		"(select `enrollment` from `activeSemester` where `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ? and `activeSemester`.`member` = `member`.`email`) = 'class'",
		[$SEMESTER, $CHOIR]
	),
	"club" => array(
		"(select `enrollment` from `activeSemester` where `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ? and `activeSemester`.`member` = `member`.`email`) = 'club'",
		[$SEMESTER, $CHOIR]
	),
	"dues" => array(
		"(select sum(`transaction`.`amount`) from `transaction` where `transaction`.`semester` = ? and `transaction`.`type` = 'dues' and `transaction`.`memberID` = `member`.`email`) < 0",
		[$SEMESTER]
	),
	);
	if (count($conditions) == 0) $res = query("select `email` from `member` order by `lastName` asc", [], QALL);
	else
	{
		$conds = [];
		$vars = [];
		foreach ($conditions as $cond)
		{
			$query = $condqueries[$cond];
			if (! $query) err("Invalid member filter \"$cond\"");
			$conds[] = $query[0];
			foreach ($query[1] as $var) $vars[] = $var;
		}
		$res = query("select * from `member` where " . implode(" and ", $conds) . " order by `lastName` asc", $vars, QALL);
	}
	$ret = [];
	foreach ($res as $row) $ret[$row["email"]] = memberName($row["email"], "full", $row);
	return $ret;
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

function sectionFromEmail($email, $friendly = 0, $semester = "") // TODO Delete
{
	global $SEMESTER, $CHOIR;
	if ($semester == "") $semester = $SEMESTER;
	if ($email == "") return ($friendly ? "None" : 0);
	$result = query("select `sectionType`.`id`, `sectionType`.`name` from `activeSemester`, `sectionType` where `activeSemester`.`member` = ? and `activeSemester`.`section` = `sectionType`.`id` and `activeSemester`.`semester` = ? and `activeSemester`.`choir` = ?", [$email, $semester, $CHOIR], QONE);
	if (! $result) return ($friendly ? "None" : 0);
	return $friendly ? $result["name"] : $result["id"];
}

function permissions()
{
	global $USER, $CHOIR;
	if (hasPosition($USER, "President") || hasPosition($USER, "Webmaster")) $query = query("select `name` from `permission`", [], QALL);
	else $query = query("select `permission` from `rolePermission` where `role` in (select distinct `role`.`id` from `role`, `memberRole` where `memberRole`.`member` = ? and `memberRole`.`role` = `role`.`id` and `role`.`choir` = ? or `role`.`rank` = 99)", [$USER, $CHOIR], QALL);
	$ret = [];
	foreach ($query as $row) $ret[] = $row["permission"];
	return $ret;
}

function hasPermission($perm, $eventType = "any")
{
	global $USER, $CHOIR;
	if (! $CHOIR) return false;
	$allowed = [];
	$basesql = "select `role`.`name` as `roleName` from `role`, `rolePermission` where `rolePermission`.`permission` = ? and `rolePermission`.`role` = `role`.`id` and `role`.`choir` = ?";
	if ($eventType == "any") $query = query($basesql, [$perm, $CHOIR], QALL);
	else $query = query($basesql . " and (`rolePermission`.`eventType` = ? or `rolePermission`.`eventType` is null)", [$perm, $CHOIR, $eventType], QALL);
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
		case "edit-setlist":
			return hasPermission($perm, $type);
		case "create":
		case "modify":
		case "delete":
			return hasPermission("$perm-event", $type);
		case "view-attendance":
		case "edit-attendance":
			if (hasPermission($perm, $type)) return true;
			if (sectionFromEmail($USER) != $sect) return false;
			return hasPermission("$perm-own-section", $type);
		default: err("Failed to check permissions", "Unknown event permission $perm");
	}
}

function hasEventPermission($perm, $event)
{
	global $USER;
	$ev = query("select `section`, `type` from `event` where `eventNo` = ?", [$event], QONE);
	if (! $ev) err("Failed to check permissions", "Permission check failed: no matching event");
	return hasEventTypePermission($perm, $ev["type"], $ev["section"]);
}

function memberDropdown($member = "")
{
	$nobody = array("" => "(nobody)");
	return dropdown($nobody + listMembers(), "member", $member);
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
	global $docroot_external, $musicdir;
	$res = query("select `songLink`.`target`, `mediaType`.`storage` from `songLink`, `mediaType` where `songLink`.`id` = ? and `mediaType`.`typeid` = `songLink`.`type`", [$id], QONE);
	if (! $res) return;
	$file = $res["target"];
	if ($file == "") return;
	if ($res["storage"] != "local") return;
	if (strpos($file, "/") !== false) err("Failed to delete file", "Bad path in file $file");
	$path = $docroot_external . $musicdir . "/" . $file;
	if (! file_exists($path)) return;
	if (! unlink($path)) err("Failed to delete file", "Unlink failed");
}

function todoBlock($userEmail, $form, $list)
{
	$html = '';
	if ($form)
	{
		if (hasPermission("add-multi-todo"))
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
