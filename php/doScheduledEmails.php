<?php
require_once('functions.php');

$result = query("SELECT `event`.`eventNo`, `event`.`name`, `event`.`callTime`, `event`.`releaseTime`, `event`.`comments`, `event`.`location`, `uniform`.`name` as `uniform`, `eventType`.`name` as `type`, `choir`.`name` as `choir`, `choir`.`admin` as `fromEmail`, `choir`.`list` as `toEmail`
	FROM `event`, `eventType`, `gig`, `uniform`, `choir`
	WHERE `event`.`type` = `eventType`.`id`
		AND `event`.`eventNo` = `gig`.`eventNo`
		AND `uniform`.`id` = `gig`.`uniform`
		AND (`eventType`.`id` = 'volunteer' OR `eventType`.`id` = 'tutti')
		AND TIMESTAMPDIFF(HOUR, CURRENT_TIMESTAMP, `event`.`callTime`) = 48
		AND `event`.`choir` = `choir`.`id`
		AND `uniform`.`choir` = `choir`.`id`
	ORDER BY `event`.`callTime` ASC", [], QALL);

if (query("select * from `emailSettings` where `id` = ? and `enabled` != '0'`", ["gig-48h"], QCOUNT) == 0) exit();

foreach($result as $event)
{
	$sender = $event["choir"] . " Officers <" . $event["fromEmail"] . ">";
	$recipient = $event["choir"] . " <" . $event["toEmail"] . ">";
	$headers = "Content-type:text/html;\n" .
		"Reply-To: $sender\n" .
		"From: $sender\n" .
		'X-Mailer: PHP/' . phpversion();

	$type = $event['type'];
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
?>
