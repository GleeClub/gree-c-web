<?php
require_once('functions.php');

if (! hasPermission("process-gig-requests")) die("You do not have permission to access this page");
if (! $CHOIR) die("Choir is not set");

if (isset($_POST["action"]))
{
	if (! isset($_POST["id"])) die("Gig request ID not set");
	$id = mysql_real_escape_string($_POST["id"]);
	if ($_POST["action"] == "accept")
	{
		if (! isset($_POST["event"])) die("Event number not set");
		$event = mysql_real_escape_string($_POST["event"]);
		$query = "update `gigreq` set `status` = 'accepted', `eventNo` = $event where `id` = '$id'";
	}
	else if ($_POST["action"] == "dismiss") $query = "update `gigreq` set `status` = 'dismissed' where `id` = '$id'";
	else if ($_POST["action"] == "restore") $query = "update `gigreq` set `status` = 'pending', `eventNo` = null where `id` = '$id'";
	else die("Unknown action \"" . $_POST["action"] . "\"");
	if (! mysql_query($query)) die("Failed to $action gig request $id: " . mysql_error());
	echo "OK";
	exit(0);
}

$query = mysql_query("select * from `gigreq` where `semester` = '$SEMESTER' order by `timestamp` desc");
if (! $query) die("Error: " . mysql_error());
echo "<table class='table'><tr><th>Requested</th><th>Event</th><th>At</th><th>Contact</th><th>Comments</th><th>Action</th></tr>";
while ($row = mysql_fetch_array($query))
{
	echo "<tr><td>" . $row["timestamp"] . "</td><td>" . $row["name"] . "</td><td>" . date("D, M j, Y", strtotime($row["startTime"])) . "<br>" . date("H:i A", strtotime($row["startTime"])). "<br>" . $row["location"] . "</td><td>" . $row["org"] . "<br>" . $row["cname"] . "<br><a href='tel:" . $row["cphone"] . "'>" . $row["cphone"] . "<br><a href='mailto:" . $row["cemail"] . "'>" . $row["cemail"] . "</a></td><td>" . $row["comments"] . "</td><td data-id='" . $row["id"] . "'>";
	if ($row["status"] == "pending") echo "<button type='button' class='btn btn-success event-create'>Create Event</button>&nbsp;<button type='button' class='btn btn-danger event-dismiss'>Dismiss</button>";
	else if ($row["status"] == "accepted")
	{
		if ($row["eventNo"]) echo "<a class='btn' href='#event:" . $row["eventNo"] . "'>View event</a>";
		else echo "<a class='btn' disabled>Event deleted</a>";
		echo "&nbsp;<button type='button' class='btn event-restore'>Reopen Request</button>";
	}
	else if ($row["status"] == "dismissed") echo "<button type='button' class='btn event-restore'>Reopen Request</a>";
	else die("Unrecognized request status " . $row["status"]);
	echo "</td></tr>";
}
echo "</table>";
