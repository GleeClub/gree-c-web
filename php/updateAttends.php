<?php
require_once('./functions.php');

if (! hasPermission("edit-attendance")) err("Access denied");
if(! isset($_POST['eventNo'])) err("Missing event number");

$eventNo = $_POST['eventNo'];
$memberID = $_POST['email'];
$mode = $_POST['mode'];
$value = $_POST['value'];

//update the attends info
if ($mode == 'did') query("update attends set confirmed = ?, didAttend = ? where memberID = ? and eventNo = ?", [1, $value, $memberID, $eventNo]);
else if ($mode == 'should') query("update attends set confirmed = ?, shouldAttend = ? where memberID = ? and eventNo = ?", [1, $value, $memberID, $eventNo]);
else if ($mode == 'late') query("update attends set minutesLate = ? where memberID = ? and eventNo = ?", [$value, $memberID, $eventNo]);
else err("BAD_MODE");
echo "OK"; // $memberID $eventNo $mode $value";

?>
