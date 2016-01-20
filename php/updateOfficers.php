<?php
require_once('functions.php');
if (! isUber(getuser())) die("DENIED");

$position = mysql_real_escape_string($_POST['position']);
$old = mysql_real_escape_string($_POST['old']);
$new = mysql_real_escape_string($_POST['new']);
if ($old == '' && $new == '') die("OK");
if ($old != '') if (! mysql_query("delete from `memberRole` where `role` = (select `id` from `role` where `name` = '$position') and `member` = '$old'")) die("Couldn't unset old $position from $old"); // TODO Filter by semester
if ($new != '') if (! mysql_query("insert into `memberRole` (`member`, `role`, `semester`) values('$new', (select `id` from `role` where `name` = '$position'), '$CUR_SEM')")) die("Couldn't set new $position to $new");

echo "OK";
