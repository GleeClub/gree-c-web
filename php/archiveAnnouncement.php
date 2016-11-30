<?php
require_once('functions.php');
$announceNo = $_POST['announceNo'];
$sql = "UPDATE `announcement` SET `archived` = 1 WHERE `announcementNo`=" . mysql_real_escape_string($announceNo);
mysql_query($sql);
?>