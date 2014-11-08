<?php
	require('./functions.php');
	
	if(isset($_COOKIE['email'])){
		require_once('./functions.php');
		$userEmail = $_COOKIE['email'];
		mysql_connect("$SQLhost", "$SQLusername", "$SQLpassword")or die("cannot connect: ".mysql_error()); 
		mysql_select_db("$SQLcurrentDatabase")or die("cannot select DB");
	}
	//if the person is logged in
	if($userEmail!=null){
		$semesters = explode('^',$_POST['semesters']);
		$memberTypes = explode('^', $_POST['memberTypes']);
		$result= mysql_fetch_array(mysql_query("select * from member where email='$userEmail'"));
		$position = $result['position'];

		if($position!="Member" && $position!="Section Leader"){

			$sql = "select * from member where 1 order by lastName asc, firstName asc";
			$result = mysql_query($sql);
			

			$sql1 = "select attends.eventNo,shouldAttend,didAttend,minutesLate,attends.confirmed,UNIX_TIMESTAMP(callTime) as time,name,typeName,pointValue from attends,event,eventType,member where attends.memberID=member.email and attends.memberID='";

			$sql2 = "' and event.eventNo=attends.eventNo and callTime<=current_timestamp and event.type=eventType.typeNo and (";

			//only show stuff from the semesters that they specified
			$count = 0;
			foreach ($semesters as $semester) {
				if($count==0)
					$sql2.="event.semester='$semester'";
				else
					$sql2.=" or event.semester='$semester'";
				$count++;
			}

			//only show stuff for the member types that they specified
			$count = 0;
			foreach ($memberTypes as $memberType) {
				$confirmed = '0';
				if($memberType=='active')
					$confirmed = '1';
				if($count==0)
					$sql2.=") and (member.confirmed='$confirmed'";
				else
					$sql2.=" or member.confirmed='$confirmed'";
				$count++;
			}

			$sql2.=") order by callTime asc";

			$html = "<div id='attendanceTables'>";

			while($member=mysql_fetch_array($result)){
				$memberID = $member["email"];
				$firstName = $member["firstName"];
				$lastName = $member["lastName"];

				$sql = $sql1.$memberID.$sql2;
				$attendses = mysql_query($sql);

				//make sure the member has some attends relationships
				if(mysql_num_rows($attendses)!=0){
					$tbody = "
						<tbody>";

					$score = 100;
					while($attends=mysql_fetch_array($attendses)){
						$eventNo = $attends['eventNo'];
						$eventName = $attends['name'];
						$type = $attends['typeName'];
						$pointValue = $attends['pointValue'];
						$shouldAttend = $attends['shouldAttend'];
						$didAttend = $attends['didAttend'];
						$minutesLate = $attends['minutesLate'];
						$confirmed = $attends['confirmed'];
						$time = $attends['time'];
						$attendsID = "attends_".$memberID."_$eventNo";

						//name, date and type of the gig
						$date = date("D, M j, g:iA",intval($time));
						$tbody.="
							<tr align=center>
								<td>$eventName</td>
								<td>$date</td>
								<td>$type</td>";

						//should the person have attended
						if($shouldAttend=="1")
							$tbody.="
								<td>Yes</td>";
						else
							$tbody.="
								<td>No</td>";

						//did the person attend
						if($didAttend=="1")
							$tbody.="
								<td>Yes</td>";
						else
							$tbody.="
								<td>No</td>";	

						//the point change
						$pointChange=0;
						if($didAttend=='1'){
							if(($type=="Volunteer Gig" || ($type=="Sectional" && $shouldAttend=='0')) && $score<100){
								$score+=$pointValue;
								$pointChange+=$pointValue;
							}
						}
						elseif($shouldAttend=='1'){
							$score-=$pointValue;
							$pointChange-=$pointValue;
						}
						if($score>100)
								$score=100;
						//make the point change red if it is negative
						if($pointChange>=0)		
							$tbody.="
							<td>$pointChange</td>";
						else
							$tbody.="
							<td><font color=red>$pointChange</font></td>";

						$tbody.="
						</tr>";
					}
					$tbody.="
					</tbody>";

					//make a row to go up top with the person't name and overall score
					$thead = "
						<thead>
							<tr>
								<th>$firstName $lastName</th>
								<th colspan=4></th>
								<th>Score: $score</th>
							</tr>
							<tr>
								<th>Name of the Gig</th>
								<th>Date of the Gig</th>
								<th>Type</th>
								<th>Should Have Attended</th>
								<th>Did Attend</th>
								<th>Point Change</th>
							</tr>
						</thead>";

					$html.="
					<table width=​100% id=​$memberID class=table>​
						$thead
						$tbody
					</table>";
				}
				//if the member had no attends relationships
				else
					$html.="";
			}
			$html.="</div>";
		}

		//if this is a normal user
		else{
			$firstName = $result["firstName"];
			$lastName = $result["lastName"];
			
			$sql = "select attends.eventNo,shouldAttend,didAttend,minutesLate,confirmed,UNIX_TIMESTAMP(callTime) as time,name,typeName,pointValue from attends,event,eventType where attends.memberID='$userEmail' and event.eventNo=attends.eventNo and callTime<=current_timestamp and event.type=eventType.typeNo and (";

			//only show stuff from the semesters that they specified
			$count = 0;
			foreach ($semesters as $semester) {
				if($count==0)
					$sql.="event.semester='$semester'";
				else
					$sql.=" or event.semester='$semester'";
				$count++;
			}
			$sql.=") order by callTime asc";
			$attendses = mysql_query($sql);

			$html = "<div id='attendanceTables'>";
			
			//make sure the member has some attends relationships
			if(mysql_num_rows($attendses)!=0){
				$tbody = "
					<tbody>";

				$score = 100;
				while($attends=mysql_fetch_array($attendses)){
					$eventNo = $attends['eventNo'];
					$eventName = $attends['name'];
					$type = $attends['typeName'];
					$pointValue = $attends['pointValue'];
					$shouldAttend = $attends['shouldAttend'];
					$didAttend = $attends['didAttend'];
					$minutesLate = $attends['minutesLate'];
					$confirmed = $attends['confirmed'];
					$time = $attends['time'];
					$attendsID = "attends_".$memberID."_$eventNo";

					//name, date and type of the gig
					$date = date("D, M j, g:iA",intval($time));
					$tbody.="
						<tr align=center>
							<td>$eventName</td>
							<td>$date</td>
							<td>$type</td>";

					//should the person have attended
					if($shouldAttend=="1")
						$tbody.="
							<td>Yes</td>";
					else
						$tbody.="
							<td>No</td>";

					//did the person attend
					if($didAttend=="1")
						$tbody.="
							<td>Yes</td>";
					else
						$tbody.="
							<td>No</td>";	

					//the point change
					$pointChange=0;
					if($didAttend=='1'){
						if(($type=="Volunteer Gig" || ($type=="Sectional" && $shouldAttend=='0')) && $score<100){
							$score+=$pointValue;
							$pointChange+=$pointValue;
						}
					}
					elseif($shouldAttend=='1'){
						$score-=$pointValue;
						$pointChange-=$pointValue;
					}
					if($score>100)
							$score=100;
					//make the point change red if it is negative
					if($pointChange>=0)		
						$tbody.="
						<td>$pointChange</td>";
					else
						$tbody.="
						<td><font color=red>$pointChange</font></td>";

					$tbody.="
					</tr>";
				}
				$tbody.="
				</tbody>";

				//make a row to go up top with the person't name and overall score
				$thead = "
					<thead>
						<tr>
							<th>$firstName $lastName</th>
							<th colspan=4></th>
							<th>Score: $score</th>
						</tr>
						<tr>
							<th>Name of the Gig</th>
							<th>Date of the Gig</th>
							<th>Type</th>
							<th>Should Have Attended</th>
							<th>Did Attend</th>
							<th>Point Change</th>
						</tr>
					</thead>";

				$html.="
				<table width=​100% id=​$memberID class=table>​
					$thead
					$tbody
				</table>";
			}
			//if the member had no attends relationships
			else
				$html.="";

			$html.= "</div>";
		}
	}
	//if the person isn't logged in
	else{
		$html = "<html><p id='title'><°o°> You're not logged in <°o°></p></html>";
	}

	echo $html;
?>

