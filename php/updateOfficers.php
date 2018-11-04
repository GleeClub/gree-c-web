<?php
require_once('functions.php');
if (! hasPermission("edit-officers")) die("DENIED");

$position = $_POST['position'];
$old = $_POST['old'];
$new = $_POST['new'];
if ($old == '' && $new == '') die("OK");
if ($old != '') query("delete from `memberRole` where `role` = (select `id` from `role` where `name` = ?) and `member` = ?", [$position, $old]); // TODO Filter by semester
if ($new != '') query("insert into `memberRole` (`member`, `role`, `semester`) values(?, (select `id` from `role` where `name` = ?), ?)", [$new, $position, $SEMESTER]);

echo "OK";
