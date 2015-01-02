<?php
require_once('functions.php');

$sql = "SELECT `event`.`eventNo`, `event`.`name`, `event`.`callTime`, `event`.`releaseTime`, `event`.`comments`, `event`.`location`, `uniform`.`name` as `uniform`, `eventType`.`typeName` as `type`
	FROM `event`, `eventType`, `gig`, `uniform`
	WHERE `event`.`type` = `eventType`.`typeNo`
		AND `event`.`eventNo` = `gig`.`eventNo`
		AND `uniform`.`id` = `gig`.`uniform`
		AND (`eventType`.`typeName` = 'Volunteer Gig' OR `eventType`.`typeName` = 'Tutti Gig')
		AND TIMESTAMPDIFF(HOUR, CURRENT_TIMESTAMP, `event`.`callTime`) = 48
	ORDER BY `event`.`callTime` ASC";

$result = mysql_query($sql);

$recipient = "Glee Club <gleeclub@lists.gatech.edu>";
//$recipient = "Matthew Schauer <awesome@gatech.edu>";
$headers = 'Content-type:text/html;' . "\n" .
	'Reply-To: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
	'From: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
	'X-Mailer: PHP/' . phpversion();

while($event = mysql_fetch_array($result))
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
