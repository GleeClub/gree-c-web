<?php
require_once('functions.php');
query("update `announcement` set `archived` = 1 where `announcementNo` = ?", [$_POST["announceNo"]]);
?>
