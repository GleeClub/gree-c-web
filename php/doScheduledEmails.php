<?php
require_once('functions.php');

$sql = "SELECT *, TIMESTAMPDIFF(HOUR, CURRENT_TIMESTAMP, callTime) AS hoursUntil 
	FROM event,eventType 
	WHERE type=typeNo 
		AND (typeName='Volunteer Gig' OR typeName='Tutti Gig') 
		AND TIMESTAMPDIFF(HOUR, CURRENT_TIMESTAMP, callTime)=48
	ORDER BY `callTime`  ASC";

$result = mysql_query($sql);

while($event = mysql_fetch_array($result))
{
	$typeName = $event['typeName'];
	
	if($typeName == "Volunteer Gig" || $typeName == "Tutti Gig")
	{
		$eventNo = $event['eventNo']
		$eventName = $event['name'];
		$callTime = $event['callTime'];
		$releaseTime = $event['releaseTime'];
		$eventComments = $event['comments'];
		$eventLocation = $event['location'];
		$gigResults = mysql_fetch_array(mysql_query("select `uniform` from `gig` where `eventNo` = $eventNo"));
		$uniformCode = $gigResults['uniform'];
		$uniformResults = mysql_fetch_array(mysql_query("select `name` from `uniform` where `id` = '$uniformCode'"));
		$eventUniform = $uniformResults['name'];
		$eventTime = strtotime($callTime);
		$eventTimeDisplay = date("D, M d g:i a", $eventTime);
		$eventReleaseTime = strtotime($releaseTime);
		$eventReleaseTimeDisplay = date("D, M d g:i a", $eventReleaseTime);
		$eventUrl = "$BASEURL/#event:$eventNo";

		$recipient = "Glee Club <gleeclub@lists.gatech.edu>";
		$subject = "$eventName Is in 48 Hours";
		$headers = 'Content-type:text/html;' . "\n" .
			'Reply-To: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
			'From: Glee Club Officers <gleeclub_officers@lists.gatech.edu>' . "\n" .
			'X-Mailer: PHP/' . phpversion();
		$message = "<html><head></head><body>
			<h2><a href='$eventUrl'>$eventName</a></h2>
			<p><b>$typeName</b> from <b>$eventTimeDisplay</b> to $eventReleaseTimeDisplay at <b>$eventLocation</b></p>
			<p>Uniform:  $eventUniform</p>
			<p>$eventComments</p>
			</body></html>";

		mail($recipient, $subject, $message, $headers);
	}
}
?>
