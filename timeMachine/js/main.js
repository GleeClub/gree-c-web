var eventButton = "<div class='btn' onclick='loadDetails(\"current\");'><i class='icon-arrow-left'></i> Event</div>";
var h;

$(document).ready(function() {
	$('.dropdown-toggle').dropdown();
	$('.nav-tabs').button()

	//Since we're moving around with js, to get back and forward to work we use the url hash.
	//To move to a new page, update window.location.hash in the event that moves the user, then 
	//update the function below with an if condition containing the new window.location.hash.
	$(window).bind('hashchange', checkHash);


  $('li').click(function(){
  	//only 1 .active at a time!
  	$(".nav li.active").removeClass("active");
	$(this).addClass("active");
  });

  checkHash();
});

function checkHash() {
		h = window.location.hash.substring(1);
		if(h == 'events') {
			loadTimeMachineEvents();
		} else if(h == 'attendance') {
			loadTimeMachineAttendance();
		} else if(h == 'money') {
			loadMoney();
		} else if(h == 'absenceRequest') {
			seeAbsenceRequests();
		} else if(h == 'members') {
			loadTimeMachineMembers();
		}
}

function loadTimeMachineEvents(){
	$.post(
		'php/eventFilters.php',
		function(data){
			$("#main").html(data);
			$('.btn').click(
				function(){
					toggleFilter($(this).attr('id'));
					appendEvents();
				});
			appendEvents();
		}

	);
}

function loadTimeMachineAttendance(){
	$.post('php/attendanceFilters.php',
		function(data) {
			$("#main").html(data);
			$('.btn').click(
				function(){
					toggleFilter($(this).attr('id'));
					appendAttendance();
				});
			appendAttendance();
		});
}

function loadTimeMachineMembers(){
	$.post('php/memberFilters.php',
		function(data) {
			$("#main").html(data);
			$('.btn').click(
				function(){
					toggleFilter($(this).attr('id'));
					appendMembers();
				});
			appendMembers();
		});
}

function toggleFilter(id){
	$('[id="'+id+'"]').toggleClass('btn-info checked');
}

function appendEvents(){
	var i=0;
	var sCount=0;
	var hCount=0;
	var semesters = new Array();
	var types = new Array();
	var h, s;
	positionLoadScreen('#filters');

	//if a new filter is being applied, get rid of the old data
	if($("#events").length>0)
		$("#events").remove();
	if($("#eventDetails").length>0)
		$("#eventDetails").remove();

	//get a list of the semesters to show
	while(i<$('[name="semester"]').length){
		if($($('[name="semester"]')[i]).is('.checked')){
			semesters[sCount] = $('[name="semester"]')[i].id;
			sCount++;
		}
		i++;
	}

	//get a list of the types of gigs to show
	i = 0;
	hCount=0;
	while(i<$('[name="type"]').length){
		if($($('[name="type"]')[i]).is('.checked')){
			types[hCount] = $('[name="type"]')[i].id;
			hCount++;
		}
		i++;
	}

	//if the user has unchecked all of the semesters or types, just quit now
	if(sCount==0 || hCount==0){
		return;
	}

	//delimit the information so it can be passed to a php page
	h = types.join('^');
	s = semesters.join('^');

	//show the loading screen while the magic happens
	toggleLoadScreen();
	$.post(
		'php/loadAllEvents.php',
		{types:h,semesters:s},
		function(data){	
			//grab the html requested
			$("#main").append(data);

			//make the html elements responsive
			$(".event").click(function(){
				$(".event").removeClass("lighter");
				$(this).addClass("lighter");
				loadDetails($(this).attr("id"));
			});
			//Hide the loading screen.  The magic done happened.
			toggleLoadScreen();
		}
	);
}

function appendAttendance(){
	var i=0;
	var sCount=0;
	var mCount=0;
	var semesters = new Array();
	var memberTypes = new Array();
	var s, m;
	positionLoadScreen('#filters');

	//if a new filter is being applied, get rid of the old data
	if($("#attendanceTables").length>0)
		$("#attendanceTables").remove();

	//get a list of the semesters to show
	while(i<$('[name="semester"]').length){
		if($($('[name="semester"]')[i]).is('.checked')){
			semesters[sCount] = $('[name="semester"]')[i].id;
			sCount++;
		}
		i++;
	}

	//get a list of the member types to show
	i=0;
	while(i<$('[name="member"]').length){
		if($($('[name="member"]')[i]).is('.checked')){
			memberTypes[mCount] = $('[name="member"]')[i].id;
			mCount++;
		}
		i++;
	}

	//if the user has unchecked all of the semesters or member types, just quit now
	if(sCount==0 || mCount==0){
		return;
	}

	//delimit the information so it can be passed to a php page
	s = semesters.join('^');
	m = memberTypes.join('^');

	//show the loading screen while the magic happens
	toggleLoadScreen();
	$.post(
		'php/seeAttendance.php',
		{semesters:s, memberTypes:m},
		function(data){
			$("#main").append(data);
			$(".table").addClass('no-highlight table-bordered every-other');
			//Hide the loading screen.  The magic done happened.
			toggleLoadScreen();
		});
}

function appendMembers(){
	var type;
	positionLoadScreen('#filters');

	//if a new filter is being applied, get rid of the old data
	if($("#editMembersTable").length>0)
		$("#editMembersTable").remove();

	//figure out member types to show
	if($('.checked').length==0)
		return;
	else if($('.checked').length>1)
		type = 'all';
	else
		type = $('.checked')[0].id;

	//show the loading screen while the magic happens
	toggleLoadScreen();
	//get the member info and make it editable
	$.post(
		'php/editMembers.php',{type:type},
		function(data){
			$("#main").append(data);
			$('td').on('dblclick', function(){
				if($(this).children().first().is("input")){
					//console.log($(this).children("input"));
				}
				else{
					var original = $(this).html();
					if($($(this).html()).html() || $('this').children().first().is("img")){
						var contents = $($(this).html()).data("value");
					}
					else{
						var contents = $(this).html();
					}
					var td = $(this);
					$(this).html("<input value='"+contents+"' />").keydown(function(){
						if(event.which == 13){
							var value = $(this).find("input").val();
							var person = $(this).parent().attr('id');
							var attribute = $(this).attr("class");
							$.post(
								'php/saveMemberInfo.php',
								{value:value, person:person, attribute:attribute},
								function(data){
									td.html(data);
							});
						}
						if(event.which == 27){
							td.html(original);
						}
					});
				}
			});
			//Hide the loading screen.  The magic done happened.
			toggleLoadScreen();
		});
}


/**
* eventID : the eventNo of the event
* me : the object on the page that is being updated
*/
function toggleShouldAttend(eventID,me){
	$.post("php/doToggleShouldAttend.php", {eventNo : eventID}, function(data){
		var isAttending = data;
		//reflect the changed attendance status
		$.post("php/loadButtonArea.php", {eventNo: eventID, attending: isAttending}, function(data){
			me.parent().parent().html(data);
			$(".btn-toggle").on('click', function(){
				//stop the click from causing the event info to load
				//event.stopPropagation();
				//get the id of the row that this cell is in
				var eventID = $(this).parent().parent().parent().attr('id');
				var me = $(this);
				toggleShouldAttend(eventID,me);
			});
		});
	});
}

function loadDetails(id){
	$.post(
		'php/loadDetails.php',
		{id:id},
		function(data){
			$("#eventDetails").html(data);
			$("#requestAbsenceButton").click(function(){
				requestAbsence($("#requestAbsenceButton").attr("value"));
			});
			$("#attendingButton").click(function(){
				seeWhosAttending();
			});
			$("#carpoolsButton").click(function(){
				seeCarpools();
			});
			$("#attendanceButton").click(function(){
				//this one is defined in loadDetails.php, because it requires a parameter
			});
			$("#editButton").click(function(){
				editDetails();
			});
			//$("#eventDetailsDetails").html("<p>press a button</p>");
			$(".eventDetailsValue").dblclick(function(){
				editDetails();
			});
			smoothScrollTo("eventDetails");
		}
	);
}

function seeWhosAttending(){
	$.post(
		'php/seeWhosAttending.php',
		function(data){
			$("#eventDetails").html(eventButton+data);
			smoothScrollTo("eventDetails");
		}
	);
}

function seeCarpools(){
	$.post(
		'php/seeCarpools.php',
		function(data){
			$("#eventDetails").html(eventButton+data);
			//$("#eventDetailsDetails").remove();
			//$("eventDetails").removeClass("span3").addClass("span5");
			$("#editCarpoolsButton").click(function(){
				editCarpools();
			});
			$("#backToEvent").on('click', function(){
				loadDetails('current');
			});
			smoothScrollTo("eventDetails");
		}
	);
}

function editDetails(){
	var keys = $(".eventDetialsKey");
	$(".eventDetailsValue").each(function(index){
		//make the first one be date, it grabs the name of the event
		//could use a numbered php array instead of associative?
		$(this).html('<input name="'+keys[index].innerHTML+'" value="'+$(this).html()+'" />');
	});
	//$("#editButton").remove();
	$("#editButtonTd").html("<div class='btn' id='submitDetailsButton'>submit changes</div>");
	$("#submitDetailsButton").click(function(){
		submitDetails();
	});
	$(".eventDetailsValue").keydown(function(){
		if(event.which == 13){
			submitDetails();
		}
		if(event.which == 27){
			var eventNo = $(".lighter").first().attr("id");
			loadDetails(eventNo);
		}
	});
	smoothScrollTo("eventDetails");
}

function submitDetails(){
	var array = $("#eventDetails input").serializeArray();
	$.post(
		'php/submitEventDetails.php',
		array,
		function(data){
			//console.log(data);
			loadDetails(data);
		},
		"html"
	);
}

function now(){
	var date = new Date();
	var ret = "" + date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + (date.getDay() + 1) + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
	return ret;
}

/**
* Smoothly scrolls the the item with the ID specified
*/
function smoothScrollTo(ID){
	var targetID = "#"+ID;
	var targetOffset=$(targetID).offset().top;
	//if there is a nav bar, account for that
	if($(".navbar-inner").length>0){
		var extraOffset =  $(".navbar-inner").height();
		targetOffset -= extraOffset;
	}
	$('body').animate({scrollTop: targetOffset}, $('body').scrollTop()/4);
}

/***************************************************************************
*********************** Money Related Functions ****************************
****************************************************************************/

var newTransactions=0;

/*
* Fills the main div with the money page and resets the new transaction count
*/
function loadMoney() {
	$.post(
		'php/money.php',
		function(data) {
			$("#main").html(data);
		});
	newTransactions=0;
}

/**
* Makes the money page into a form for with which to add transactions and increments the new transaction count
*/
function addMoneyForm(){
	newTransactions++;

	$.post('php/getMembers.php',
		function(data) {
			//construct the dropdown list
			var arr = [];
			var member = [];
			var fname, lname, email;
			arr=eval(data);
			rowID = "new_"+newTransactions;

			var dropDown = "<select id='"+rowID+"_email'>";
			var i=0;
			while(i<arr.length){
				member=eval(arr[i]);
				fname = member[0];
				lname = member[1];
				email = member[2];
				dropDown+="<option value='"+email+"'>"+fname+" "+lname+"</option>";
				i++;
			}
			dropDown+="</select>";

			//check if this is the first new row or not (changes how th buttons at the bottom need to be treated)
			var offset;
			if(newTransactions==1)
				offset=2;
			else
				offset=3;

			//add the new transaction fields
			var table = document.getElementById("moneyForm");
			var rowCount = table.rows.length;
			var row = table.insertRow([table.rows.length-offset]);
			row.id = rowID;
				//dropdown
				var cell = row.insertCell(row.cells.length);
				cell.innerHTML = dropDown;
				//Empty (filler) cells, so fields line up in the right columns
				row.insertCell(row.cells.length);
				row.insertCell(row.cells.length);



				//send email button
				cell = row.insertCell(row.cells.length);
				cell.innerHTML = "Send Receipt: <input type='checkbox' id='" + rowID + "_sendEmail'/>";

				//amount
				cell = row.insertCell(row.cells.length);
				cell.innerHTML = "<input type='text' id='"+rowID+"_amount' placeholder='amount'></input>";
				//description
				cell = row.insertCell(row.cells.length);
				cell.innerHTML = "<input type='text' id='"+rowID+"_description' placeholder='description' maxlength='500'></input>";
				//remove button
				cell = row.insertCell(row.cells.length);
				cell.innerHTML = "<button type='button' class='btn' onclick='removeRow(\""+rowID+"\");'>Cancel Transaction</button>";

			//add an "add more" and a "submit" button, if this is the first new transaction
			if(newTransactions==1){
				//"add more button"
				row = table.insertRow([table.rows.length-2]);
				cell = row.insertCell(row.cells.length);
				cell.innerHTML = "<button type='button' class='btn' onclick='addMoneyForm();'>Add Another Transaction</button>";
			
				//add a submit button
				row = table.rows[table.rows.length-2];
				row.deleteCell(0);
				newElement = document.createElement("button");
				newElement.type = "button";
				newElement.className = 'btn';
				newElement.onclick = addTransactions;
				newElement.innerHTML = "Submit These Transactions";
				cell = row.insertCell(0);
				cell.appendChild(newElement);

				//add a cancel button
				row = table.rows[table.rows.length-1];
				row.deleteCell(0);
				newElement = document.createElement("button");
				newElement.type = "button";
				newElement.className = 'btn';
				newElement.onclick = loadMoney;
				newElement.innerHTML = "Cancel";
				cell = row.insertCell(0);
				cell.appendChild(newElement);
			}
	});

}

/*
* Removes a row, given the row's ID
*/
function removeRow(ID){
	var row = document.getElementById(ID);
	row.parentNode.removeChild(row);
}

/*
* Calls the addTransactions.php page and reloads the money page
*/
function addTransactions(){
	var table = document.getElementById("moneyForm");
	var row, email, amount, desc;
	var emailArr = [];
	var amountArr = [];
	var descArr = [];
	var sendEmailArr = [];

	//pull out the new transaction data
	var i=1;
	var count=0;
	while(i<table.rows.length-3){
		row=table.rows[i];
		//if the row is the row of a new transaction
		if(row.id.indexOf("new_")>-1){
			sendEmailArr[count] = document.getElementById(row.id + "_sendEmail").checked;
			emailArr[count] = document.getElementById(row.id+"_email").value;
			amountArr[count] = document.getElementById(row.id+"_amount").value;
			descArr[count] = document.getElementById(row.id+"_description").value;
			count++;
		}
		i++;
	}

	if(emailArr.length>0){
		var emailList = JSON.stringify(emailArr);
		var amountList = JSON.stringify(amountArr);
		var descList = JSON.stringify(descArr);
		var sendEmailList = JSON.stringify(sendEmailArr);
		$.post('php/addTransactions.php',{ emails: emailList, amounts: amountList, descriptions: descList, sendEmails: sendEmailList }, function(data){
			if(data.indexOf("didn't work")>-1)
				$("#main").html(data);
			else
				loadMoney();
		});
	}
}

/**
* Makes the money page into a form for-with-which to delete transactions
*/
function removeMoneyForm(){
	var newElement;

	//add a "Delete" column to the headings
		var headings = document.getElementById("headings");
		var newCell = headings.insertCell(0);
		newCell.class = "cellwrap";
		newCell.innerHTML = "Delete";

	//add a checkbox to each transaction
		var table = document.getElementById("moneyForm");
		var rowCount = table.rows.length;

		var moneyvalueID;
		var row;
		var i=1;
		while(i < rowCount-2){
			row = table.rows[i];
			moneyvalueID = row.id;
			id = moneyvalueID+"_delete";
			
			newCell = row.insertCell(0);
			newCell.id = id;
			newElement = document.createElement("input");
			newElement.type = "checkbox";
			newElement.value = moneyvalueID;
			newElement.id = moneyvalueID+"_checkbox";
			newCell.appendChild(newElement);
			i++;
		}

	//add delete button
		row = table.rows[table.rows.length-2];
		row.deleteCell(0);
		newElement = document.createElement("button");
		newElement.type = "button";
		newElement.onclick = removeTransaction;
		newElement.innerHTML = "Delete Selected Transactions";
		newCell = row.insertCell(0);
		newCell.appendChild(newElement);

	//add a cancel button
		row = table.rows[table.rows.length-1];
		row.deleteCell(0);
		newElement = document.createElement("button");
		newElement.type = "button";
		newElement.onclick = loadMoney;
		newElement.innerHTML = "Cancel";
		newCell = row.insertCell(0);
		newCell.appendChild(newElement);
}

/**
* Calls the deleteTransaction.php page and reloads the money page
*/
function removeTransaction(){
	var table = document.getElementById("moneyForm");
	var rowCount = table.rows.length;

	var box;
	var checkedList=[];
	var i=1;
	while(i < rowCount-2){
		id = table.rows[i].id+"_checkbox";
		box = document.getElementById(id);
		if(box.checked)
			checkedList[checkedList.length]=box.parentNode.parentNode.id;
		
		i++;
	}
	if(checkedList.length>0){
		var jsonList = JSON.stringify(checkedList);
		$.post('php/removeTransaction.php',{ checked: jsonList });
	}
	loadMoney();
}
/***************************************************************************
******************* End of Money Related Functions *************************
****************************************************************************/

/***************************************************************************
******************* Attendance Related Functions ***************************
****************************************************************************/

function setDidAttend(eventID,memberID,newDidAttend){
	$.post('php/setDidAttend.php', { eventNo: eventID, email: memberID, didAttend: newDidAttend }, function(data){
		document.getElementById(memberID+"_table").innerHTML = data;
	});
}

function updateEventAttendance(eventID){
	$.post('php/seeEventAttendance.php', { eventNo:  eventID}, function(data){
		$('#eventDetails').html(eventButton+data);
		$(".input-mini").on("blur", function(){
			if(this.value==''){
				//console.log(this.placeholder);
			}
			else{
				$(this).parent().addClass('warning');
				var id = $(this).parent().parent().parent().attr('id');
				updateMinutesLate(this.value, id, $(this));
				//$(this).parent().removeClass('warning').addClass('success');
			}
		});
		smoothScrollTo("eventDetails");
	});
}

function setDidAttendEvent(eventID,memberID,newDidAttend){
	$.post('php/setDidAttendEvent.php', { eventNo: eventID, email: memberID, didAttend: newDidAttend }, function(data){
		document.getElementById(eventID+"_table").innerHTML = data;
	});
}

function updateMinutesLate(minutesLate, id, element){
	$.post(
		'php/updateMinutesLate.php',
		{minutesLate:minutesLate, id:id},
		function(data){
			element.parent().removeClass('warning');
			//console.log(data);
		}
	);
}
/***************************************************************************
************** End of Attendance Related Functions *************************
****************************************************************************/

/***************************************************************************
*********************** Loading Screen Functions ***************************
****************************************************************************/

/**
* This must be called for the loadscreen to work.  It puts the html for the screen into the div of your choice
* divID must be in jQuery form (e.g. '#yourID')
*/
function appendLoadScreen(divID){
	if($('#loadingBackground').length>0){
		$('#loadingImage').remove();
		$('#loadingBackground').remove();
	}
	$.post(
		'php/loadScreen.php',
		function(data){
			$(divID).append(data);
			positionLoadScreen(divID);
		}
	);
}

/**
* Postions the loading screen over the div of your choice
* divID must be in jQuery form (e.g. '#yourID')
*/
function positionLoadScreen(divID){
	var position = $(divID).offset();
	var left = position.left;
	var top = position.top;

	$('#loadingBackground').css('position','absolute');
	$('#loadingBackground').css('top',position.top);
	$('#loadingBackground').css('left',position.left);
	$('#loadingBackground').css('width',$(divID).outerWidth(false));
	$('#loadingBackground').css('height',$(divID).height());
	$('#loadingBackground').css('background-color','white');
	$('#loadingBackground').css('opacity',0.4);

	$('#loadingImage').css('position','absolute');
	$('#loadingImage').css('top',position.top+$('#loadingBackground').height()/2-50);
	$('#loadingImage').css('left',position.left+$('#loadingBackground').width()/2-100);
}

/**
* Turns the loading screen on and off (preferable after it has been positioned)
*/
function toggleLoadScreen(){
	if($('#loadingBackground').css('visibility')=='hidden')
		$('#loadingBackground').css('visibility','');
	else
		$('#loadingBackground').css('visibility','hidden');

	if($('#loadingImage').css('visibility')=='hidden')
		$('#loadingImage').css('visibility','');
	else
		$('#loadingImage').css('visibility','hidden');
}

/***************************************************************************
***************** End of Loading Screen Functions **************************
****************************************************************************/


