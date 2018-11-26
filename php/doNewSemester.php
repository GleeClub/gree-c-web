<?php
require_once('functions.php');

if (! $USER || ! hasPermission("edit-semester")) err("DENIED");
$name = $_POST['name'];
$sDD = $_POST['sDD'];
$sMM = $_POST['sMM'];
$sYYYY = $_POST['sYYYY'];
$eDD = $_POST['eDD'];
$eMM = $_POST['eMM'];
$eYYYY = $_POST['eYYYY'];

$start = "$sYYYY-$sMM-$sDD 00:00:00";
$end = "$eYYYY-$eMM-$eDD 00:00:00";

query("insert into semester (semester,beginning,end) values (?, ?, ?)". [$name, $start, $end]);
query("update `variables` set `semester` = ?", [$name]);
$cursem = query("select `semester` from `variables`", [], QONE);
if (! $cursem) err("Could not retrieve variables");

//query("update `member` set `confirmed` = 0");

echo "<legend>Results</legend>The current semester is now: " . $cursem["semester"];
?>
