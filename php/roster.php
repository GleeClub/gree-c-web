<?php

require_once('functions.php');
echo "<div id='roster_table' style='width: 100%'><img style='width: 28px; height: 28px; display: block; margin: 0px auto' src='/images/loading.gif'></div>";

if (! hasPermission("view-users")) die("Not authorized");
echo "<br><br>";
	//<div class='btn-group'><button class='btn filter active' data-toggle='button' data-cond='b2'>B2</button><button class='btn filter active' data-toggle='button' data-cond='b1'>B1</button><button class='btn filter active' data-toggle='button' data-cond='t2'>T2</button><button class='btn filter active' data-toggle='button' data-cond='t1'>T1</button></div>
echo "<span class='pull-left'><div class='btn-toolbar' style='display: inline-block' id='roster_filters'>
	<div class='btn-group'><button class='btn filter active' data-toggle='button' data-cond='active'>Active</button><button class='btn filter' data-toggle='button' data-cond='club'>Club</button><button class='btn filter' data-toggle='button' data-cond='class'>Class</button></div>
	<div class='btn-group'><button class='btn filter' data-toggle='button' data-cond='dues'>Dues unpaid</button></div>
	</div><span class='spacer'></span><a class='tablelink' data-format='normal' href='php/memberTable.php'>Print</a> &middot; <a class='tablelink' data-format='csv' href='php/memberTable.php'>CSV</a>
	</span>";
	//<div class='btn-group'><button class='btn filter' data-toggle='button' data-cond='fail'>Below 80%</button></div>
echo "<span class='pull-right' id='roster_ops'>";
if (hasPermission("edit-grading"))
{
	$result = query("select `gigreq` from `semester` where `semester` = ?", [$SEMESTER], QONE);
	if (! $result) die("Bad semester");
	$gigreq = $result['gigreq'];
	echo "Volunteer gig requirement:  <input type='text' id='gigreq' style='width: 20px; margin-bottom: 0px' value='$gigreq'><button class='btn' onclick='setGigReq($(\"#gigreq\").attr(\"value\"))'>Go</button><span class='spacer'></span><div style='display: inline-block'><input type='checkbox' style='margin-top: -16px' name='gigcheck' onclick='setGigCheck($(this).attr(\"checked\"))'";
	$result = query("select `gigCheck` from `variables`", [], QONE);
	if (! $result) die("Missing variables");
	if ($result['gigCheck']) echo " checked";
	echo "> <div style='display: inline-block'>Include gig requirement<br>in grade calculation</div></div>";
}
if (hasPermission("edit-transaction"))
{
	echo "<span class='spacer'></span><div class='btn-group'><button class='btn dropdown-toggle' data-toggle='dropdown' href='#'>Dues <span class='caret'></span></button><ul class='dropdown-menu'>";
	echo "<li><a href='#' id='semdues' onclick='addDues(); return false;' data-placement='right' data-toggle='tooltip' title='Adds a $20 fee to the account of every active member who does not yet have a dues charge for this semester'>Apply semester dues</a></li>";
	echo "<li><a href='#' id='latefee' onclick='addLateFee(); return false;' data-placement='right' data-toggle='tooltip' title='Adds a $5 fee to the account of every active member whose dues balance for this semester is not $0'>Add late fee</a></li></ul></div>";
}
echo "</div>";

