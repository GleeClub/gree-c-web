<?php
require_once('functions.php');

$result = query("SELECT `event`.`eventNo`, `event`.`name`, `event`.`callTime`, `event`.`releaseTime`, `event`.`comments`, `event`.`location`, `uniform`.`name` as `uniform`, `eventType`.`name` as `type`
	FROM `event`, `eventType`, `gig`, `uniform`
	WHERE `event`.`type` = `eventType`.`id`
		AND `event`.`eventNo` = `gig`.`eventNo`
		AND `uniform`.`id` = `gig`.`uniform`
		AND (`eventType`.`id` = 'volunteer' OR `eventType`.`id` = 'tutti')
		AND TIMESTAMPDIFF(HOUR, CURRENT_TIMESTAMP, `event`.`callTime`) = 48
		AND `event`.`choir` = ?
	ORDER BY `event`.`callTime` ASC", [$CHOIR], QALL);

$row = query("select `admin`, `list` from `choir` where `id` = ?", [$CHOIR], QONE);
if (! $row) die("Bad choir");
$sender = $row['admin'];
$recipient = $row['list'];
//$recipient = "Matthew Schauer <awesome@gatech.edu>";
$headers = "Content-type:text/html;\n" .
	"Reply-To: $sender\n" .
	"From: $sender\n" .
	'X-Mailer: PHP/' . phpversion();

foreach($result as $event)
{
	$type = $event['type'];
	if($type == "Volunteer Gig" || $type == "Tutti Gig")
	{
		$eventNo = $event['eventNo'];
		$eventName = $event['name'];
		$callTime = date("D, M d g:i a", strtotime($event['callTime']));
		$releaseTime = date("D, M d g:i a", strtotime($event['releaseTime']));
		$eventComments = $event['comments'];
		$eventLocation = $event['location'];
		$eventUniform = $event['uniform'];
		$eventUrl = "$BASEURL/#event:$eventNo";

		$subject = "$eventName Is in 48 Hours";
		$message = "<html><head></head><body>
			<h2><a href='$eventUrl'>$eventName</a></h2>
			<p><b>$type</b> from <b>$callTime</b> to $releaseTime at <b>$eventLocation</b></p>
			<p>Uniform:  $eventUniform</p>
			<p>$eventComments</p>
			</body></html>";

		mail($recipient, $subject, $message, $headers);
	}
}
?>
