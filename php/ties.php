<?
require_once('functions.php');
if (! hasPermission("view-ties")) die("DENIED");

function tietable($query)
{
	global $SEMESTER;
	echo "<table class='table'><tr><th>#</th><th>Status</th><th>Comments</th><th>Actions</th></tr>";
	foreach (query($query, [], QALL) as $row)
	{
		if ($row['borrower'] == '')
		{
			if ($row['status'] == "Circulating") $status = "Not checked out";
			else $status = $row['status'];
			$rowstyle = '';
		}
		else
		{
			if ($row['status'] == "Circulating") $stat = "Checked out to";
			else if ($row['status'] == "Lost") $stat = "Lost by";
			else $stat = "<span style='color: red'>$row[status]</span>; held by";
			$status = "$stat <a href='#profile:$row[borrower]'>$row[borrowerName]</a> since $row[dateOut]";
			$active = query("select * from `activeSemester` where `member` = ? and `semester` = ?", [$row["borrower"], $SEMESTER], QCOUNT) > 0;
			if ($active) $status .= "<br><span style='color: green'>Active</span> this semester";
			else $status .= "<br><span style='color: red'>Inactive</span> this semester";
			$deposit = query("select sum(`amount`) as `total` from `transaction` where `type` = 'deposit' and `memberID` = ?", [$row["borrower"]], QONE);
			if ($deposit['total'] >= fee("tie")) $depok = true;
			else $depok = false;
			if ($depok) $status .= "<br>Tie deposit <span style='color: green'>paid</span>";
			else $status .= "<br>Tie deposit <span style='color: red'>unpaid</span>";
			if ($active && $depok) $rowstyle = "class='success'";
			else $rowstyle = "class='error'";
			if ($row['status'] != "Circulating") $rowstyle="";
		}
		echo "<tr $rowstyle><td>$row[tie]</td><td>$status</td><td>$row[comments]</td><td><button type='button' class='btn btn-link tie_hist'>History</button> <button type='button' class='btn btn-link tie_edit'>Edit</button></td></tr>";
	}
	echo "</table>";
}

echo "<style>th { text-align: left; }</style>";
$sqlbase = "select `id` as `tie`, `status`, `member` as `borrower`, (select concat_ws(' ', `firstName`, `lastName`) from `member` where `member`.`email` = `borrower`) as `borrowerName`, `dateOut`, `comments` from (select `id`, `status`, `comments` from `tie`) as `ties` left outer join (select `member`, `dateOut`, `tie` from `tieBorrow` where `dateIn` is null) as `borrows` on `ties`.`id` = `borrows`.`tie`";
tietable($sqlbase . " where `ties`.`status` = 'Circulating'");
echo "<button type='button' class='btn btn-link' onclick='$(\"#tie_inactive\").toggle()'>Inactive ties</button>";
echo "<div id='tie_inactive' style='display: none'>";
tietable($sqlbase . " where `ties`.`status` != 'Circulating'");
echo "</div><span class='pull-right'><input type='text' id='tie_newnum' placeholder='#' style='width: 30px'><button type='button' class='btn' id='tie_add'>Add tie</button></span>";

?>
