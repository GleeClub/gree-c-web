<?php
require_once('functions.php');
if (! isOfficer(getuser())) die("DENIED");

$position = mysql_real_escape_string($_POST['position']);
$old = mysql_real_escape_string($_POST['old']);
$new = mysql_real_escape_string($_POST['new']);
if ($old != '') if (! mysql_query("update `member` set `position` = 'Member' where `email` = '$old'")) die("Couldn't unset old $position from $old");
if ($new != '') if (! mysql_query("update `member` set `position` = '$position' where `email` = '$new'")) die("Couldn't set new $position to $new");

echo "OK";
