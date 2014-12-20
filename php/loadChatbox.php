<?php
require_once('functions.php');
$userEmail = $_COOKIE['email'];

$scroll = $_POST['scroll'];

/**
* $scroll tell whether you want scroll to the bottom after this call (only used on the initial call, when the page loads)
*/
if(!isset($_COOKIE['email'])){
	loginBlock();
	return;
}
$lastSender='';
$html='
<div id="chatboxMessages">
<table id="chatboxMessagesTable" class="table no-highlight">';
$lighter = " class = 'lighter' ";
$sql='SELECT * FROM `chatboxMessage` ORDER BY timeSent asc;';
$results = mysql_query($sql);
while($row = mysql_fetch_array($results)){
	if($lastSender == $row["sender"]){
		$sender = '';
	}
	else{
		$sender = $row["sender"];
		$sql = "SELECT * FROM `member` WHERE email='".$sender."';";
		$result= mysql_fetch_array(mysql_query($sql));
		$sender = $result["prefName"]." ".substr($result['lastName'], 0, 1).": ";
		$timeInt = strtotime($row["timeSent"]);
		$time = date("H:i", $timeInt);
		$day = date("M d", $timeInt);
	}
	$contents = $row["contents"];
	$messageID = $row['messageID'];
	/*if(strpos($contents, 'www.') === 0){
		$contents = '<a href="'.$contents.'" target="_blank">surprise link</a>';
	}
	if(strpos($contents, 'http://') === 0){
		$contents = '<a href="'.$contents.'" target="_blank">surprise link</a>';
	}*/
	
	$temp = '';
	$words = explode(' ', $contents);
	foreach($words as $value){
		$link = '';
		if((strpos($value, 'www.') === 0) || (strpos($value, 'http://') === 0) || (strpos($value, 'https://') === 0)){
			$link = $value;
			$domain = explode('/', $link);
			if(($domain[0] !== "http:") && ($domain[0] !== "https:")){
				$domainDisplay = $domain[0];
			}
			else{
				$domainDisplay = $domain[2];
			}
			//get just 'youtube' or 'imgur'
			//$domainBits = explode('.', $domainDisplay);
			
			$value = '<a href="'.$link.'" target="_blank">'.$link.'</a>';
		}
		if(strpos($link, '.jpg') || strpos($link, '.gif') || strpos($link, '.png')){
			$value = '<div class="btn" onclick="showChatboxImage(this);">show image</div><img class="chatboxImage" src="'.$link.'" onclick="hideChatboxImage(this);"/>';
		}
		$temp .= ' '.$value;
	}
	$contents = $temp;
	
	
	//this goes backwards. the first sql result is the last (most recent) one that appears onscreen. builds from the bottom up.
	$html = $html."
		<tr ".$lighter.">
			<td>
				<span class='chatboxTimestamp'><span>".$day." </span>
				<span>".$time."</span></span>
			</td>
			<td>
				<dl class='dl-horizontal'>
					<dt>
						<span class='chatboxSenderName'>".$sender."</span>
					</dt>
					<dd>
						<span data-messageID='".$messageID."' class='chatboxMessage'>".$contents."</span>
					</dd>
				</dl>
			</td>
		</tr>
	";
	//put in a submit button	
	$lastSender = $row["sender"];
	if($lighter==''){$lighter = " class = 'lighter' ";}
	else{$lighter='';}
}
$html.="
	<tr ".$lighter.">
			<td>
				<span class='chatboxTimestamp'><span></span>
				<span>now</span></span>
			</td>
			<td>
				<dl class='dl-horizontal'>
					<dt>
						<span class='chatboxSenderName'>You:</span>
					</dt>
					<dd>
						<div class='control-group form-inline'>
							<input type='text' id='shoutBox' />
							<div class='btn btn-primary' id='shoutButton'>shout</div>
						</div>
					</dd>
				</dl>
			</td>
		</tr>
	</table>
	</div>"; //make it all the same table so everything lines up

//if this is the first time loading the page
if($scroll=='1'){
	//wrap the whole page in a 'scrolldiv',  and have javascript can scroll to the bottom
	$html = "
	<div id='scrolldiv'>
		$html
	</div>

	<script type='text/javascript'>
		window.scrollBy(0, $('#scrolldiv').height());
	</script>";

}
echo $html;

?>
