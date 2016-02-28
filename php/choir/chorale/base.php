<?php
function attendance($email, $mode, $semester = '', $media = 'normal')
{
	// Type:
	// 0 for grade
	// 1 for officer table
	// 2 for member table
	// 3 for gig count
	global $CUR_SEM;
	$WEEK = 604800;
	if ($semester == '') $semester = $CUR_SEM;
	
	$score = 100;
	$gigcount = 0;
	$eventRows = '';
	$tableOpen = '<table>';
	$tableClose = '</table>';
	if ($mode == 1)
	{
		$eventRows = '<thead>
			<th>Event</th>
			<th>Date</th>
			<th>Type</th>
			<th>Should Have<br>Attended</th>
			<th>Did Attend</th>
			<th>Minutes Late</th>
			<th>Point Change</th>
			<th>Partial Score</th>
		</thead>';
	}
	else if ($mode == 2)
	{
		$tableOpen = '<table width="100%" id="defaultSidebar" class="table no-highlight table-bordered every-other">';
		$eventRows = '<thead>
			<th><span class="heading">Event</span></th>
			<th><span class="heading">Should have attended?</span></th>
			<th><span class="heading">Did attend?</span></th>
			<th><span class="heading">Point Change</span></th>
		</thead>';
	}
	if ($mode == 3) return $gigcount;
	// Bound the final score between 0 and 100
	if ($score > 100) $score = 100;
	if ($score < 0) $score = 0;
	$score = round($score, 2);
	if ($mode == 0) return $score;
	else return $tableOpen . $eventRows . $tableClose;
}

?>
