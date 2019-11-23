var h;

$(document).ready(function() {
	var timer;
	$('.dropdown-toggle').dropdown();
	$('.nav-tabs').button()
	//Update the unread messages badge

	//Since we're moving around with js, to get back and forward to work we use the url hash.
	//To move to a new page, update window.location.hash in the event that moves the user, then 
	//update the function below with an if condition containing the new window.location.hash.
	$(window).bind('hashchange', checkHash);
	$('.search-query').typeahead({
		source: typeaheadCallback,
		updater: typeaheadUpdater
	});
	$('.search-query').on('focus', function() { $('.search-query').attr('value', '') });

	$('li').click(function() {
		//only 1 .active at a time!
		$(".nav li.active").removeClass("active");
		$(this).addClass("active");
	});
	checkHash();
});

var idArr;
function typeaheadCallback(query, process)
{
	$.post("php/getMembers.php", 
		{nameType: "both"},
		function(data) {
			process(readableFromJSON(data));
		});
}
function typeaheadUpdater(item)
{
	window.location.hash = "profile:"+idArr[item];
	return item;
}

//Given a JSON array, make it human-readable
function readableFromJSON(json) {
	idArr = [];
	var arr = [];
	var tmp;
	json = JSON.parse(json);
	$.each(json, function(i, fb) {
		tmp = JSON.parse(fb);
		arr.push(tmp[0] + ' ' + tmp[1]);
		idArr[tmp[0] + ' ' + tmp[1]] = tmp[2];
	});
	return arr;
}

function loggedIn()
{
	var ret = false;
	$.ajax({type: "get", url: "api.php", data: { action: "user" }, async: false, success: function(data) { ret = data.authenticated; }});
	return ret;
	//return document.cookie.indexOf("email") != -1;
}

var timer = null;
function checkHash()
{
	h = window.location.hash.substring(1);
	clearInterval(timer);
	if (h == 'forgotPassword') loadForgotPassword();
	else if (h == 'editProfile') editProfile();
	else if (h == 'minutes') showMinutes();
	else if (! loggedIn() && h.indexOf(':') <= 0) loadLogin();
	else if (h == "stats" || h == '') loadStats();
	else if (h == 'events') loadEvents('all');
	else if (h == 'event') addOrRemoveEvent();
	else if (h == 'feedback') feedbackForm();
	else if (h == 'suggestSong') songForm();
	else if (h == 'absenceRequest') seeAbsenceRequests();
	else if (h == 'roster') roster();
	else if (h == 'addAnnouncement') addAnnouncement();
	else if (h == 'semester') loadAddSemester();
	else if (h == 'repertoire') showRepertoire();
	else if (h == 'announcements') loadAnnouncements();
	else if (h == 'ties') loadTies();
	else if (h == 'settings') loadSettings();
	else if (h == 'money') loadMoney();
	else if (h == 'timeMachine') timeMachine();
	else if (h == 'gigreqs') gigreqs();
	else if (h.indexOf(':') > 0)
	{
		var query = h.substring(0, h.indexOf(':'));
		var arg = h.substring(h.indexOf(':') + 1);
		if (query == 'minutes') showMinutes(arg);
		else if (query == 'doc') loaddoc(arg);
		else if (! loggedIn()) loadLogin();
		else if (query == 'events') loadEvents(arg);
		else if (query == 'event') loadEvents('all', arg);
		else if (query == 'profile') loadProfile(arg);
		else if (query == 'song') showRepertoire(arg);
		else if (query == 'timeMachine')
		{
			params = arg.split(";");
			if (params.length < 1) timemachine();
			else if (params.length < 2) timeMachine(params[0]);
			else timeMachine(params[0], params[1]);
		}
		else $('#main').html("What's a " + query + "?");
	}
	else $('#main').html("I don't exist.");
}

function loadLogin()
{
	$.post('php/loadLogin.php', function(data) { $("#main").html(data); });
}

function signIn()
{
       var email = $('#email').prop('value');
       var password = $('#password').prop('value');
       $.post("php/checkLogin.php", { email : email, password : password }, function(data) {
               if (data != 'OK') alert(data.message);
               else location.reload();
       });
       return false;
}

function editProfile()
{
	$.post('php/editProfile.php', function(data) {
		$('#main').html(data);
		$('select.choir').on('change', function() { $.post('php/getSections.php', { choir : $(this).val() }, function(data) { $("select.section").replaceWith(data); }); });
		$("#editProfileSubmit").click(doEditProfile);
		$('select.choir').trigger('change');
	});
}

function doEditProfile() {
	var array = $("#register").serialize();
	$.post('php/doEditProfile.php', array, function(data) {
		if (data != "OK") alert(data);
		else
		{
			alert("Information updated");
			window.location.href = ".";
		}
	});
}

function loadStats() {
	//$("#main").html("stats go here!");
	$.post(
		'php/stats.php',
		function(data){
			//alert(data);
			$('#main').html(data);
			requestNotificationsPermission();
			$('#newTodoButton').on('click', function(){
				submitNewTodo();
			});
			/*$('#multiTodo').typeahead({
				source: typeaheadCallback,
				updater: typeaheadUpdater
			});*/
			$('#multiTodo').tokenInput("php/searchMembers.php", {
				theme:"facebook",
				preventDuplicates:true,
			});
			$('#multiTodoButton').on('click', submitMultiTodo);
			$("#allAnnounceButton").on('click', function() {
				window.location.hash = 'announcements';
			});
			$(".archiveButton").tooltip({
				title: "Archive this announcement",
			});
			$('.gradetip').tooltip();
			listenOnTodos();
		}
	);

}

function archiveAnnouncement(mid) {
	$.post('php/archiveAnnouncement.php', {
			announceNo:mid,
		},
		function() {
			$("#announce"+mid).hide();
		});
}

function loadAnnouncements() {
	$.post('php/loadAnnouncements.php',
		function(data) {
			$('#main').html(data);
		});
}

function archiveAnnouncement(mid) {
	$.post('php/archiveAnnouncement.php', {
			announceNo:mid,
		},
		function() {
			$("#announce"+mid).hide();
		});
}

function loadEvents(type, id){
	//console.log('load all events!');
	$.post(
		'php/loadAllEvents.php',
		{ type : type },
		function(data){
			//console.log('done');
			$("#main").html(data);
			if (typeof id != 'undefined')
				loadDetails(id);
			$(".event").click(function(){
				$(".event").removeClass("lighter");
				$(this).addClass("lighter");
				var id = $(this).attr("id");
				history.replaceState({}, document.title, window.location.protocol + '//' + window.location.host + window.location.pathname + '#event:' + id); // Gross
				loadDetails(id);
			});
			$(".btn-confirm").on('click', function(){
				//stop the click from causing the event info to load
				//event.stopPropagation();

				//get the id of the row that this cell is in
				var eventID = $(this).parent().parent().attr('id');

				var isAttending = 1;
				var me = $(this);
				$.post("php/loadButtonArea.php", {eventNo: eventID, attending: isAttending}, function(data){
					//load in the new buttons
					me.parent().html(data);
					//if there is a toggle button, make sure it works
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
			$(".btn-deny").on('click', function(){
				//stop the click from causing the event info to load
				//event.stopPropagation();
				
				//get the id of the row that this cell is in
				var eventID = $(this).parent().parent().attr('id');
				
				var isAttending = 0;
				var me = $(this);
				$.post("php/loadButtonArea.php", {eventNo: eventID, attending: isAttending}, function(data){
					me.parent().html(data);
					//if there is a toggle button, make sure it works
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
			$(".btn-toggle").on('click', function(){
				//stop the click from causing the event info to load
				//event.stopPropagation();
				//get the id of the row that this cell is in
				var eventID = $(this).parent().parent().parent().attr('id');
				var me = $(this);
				toggleShouldAttend(eventID,me);
			});
		}
	);
}

function loadDetails(id){
	$("#eventDetails").html("<img style=\"display: block; margin: 0 auto\" src=\"/images/loading.gif\">");
	$.post(
		'php/loadDetails.php',
		{ id : id },
		function(data){
			if (data == "NULL") return;
			if (typeof data == "object" && "status" in data && (data.status == "error" || data.status == "internal_error")) {
				$("#eventDetails").html(data.message);
				return;
			}
			$("#eventDetails").html(data);
			$("#requestAbsenceButton").click(function(){
				//requestAbsence($("#requestAbsenceButton").attr("value"));
				requestAbsence(id);
			});
			$("#attendingButton").click(function(){
				seeWhosAttending(id);
			});
			$("#carpoolsButton").click(function(){
				seeCarpools(id);
			});
			$("#attendanceButton").click(function(){
				//this one is defined in loadDetails.php, because it requires a parameter
			});
			$("#setlistButton").click(function(){
				setlist(id);
			});
			$("#editButton").on('click', function(){
				editDetails(id);
			});
			//$("#eventDetailsDetails").html("<p>press a button</p>");
			$(".eventDetailsValue").on('dblclick', editDetails);
			smoothScrollTo("eventDetails");
		}
	);
}

function setlist(id)
{
	function fixorder(table)
	{
		var i = 1;
		table.children('tr').each(function() {
			$(this).attr('id', 'song' + i);
			$(this).children(':eq(1)').html(i);
			i += 1;
		});
	}

	function add_del_handler()
	{
		$('.set_del').click(function() {
			var row = $(this).parent().parent();
			$.post('php/doEditSetlist.php', { action : "remove", event : id, order : row.attr('id').replace('song', '') }, function(data) {
				if (data != 'OK') alert('Error: ' + data);
				row.remove();
				fixorder($('#set_table tbody'));
			});
			return false;
		});
	}

	$.post('php/setlist.php', { event : id }, function(data) {
		var eventButton = "<div class='btn' onclick='loadDetails(" + id + ");'><i class='icon-arrow-left'></i> Event</div>";
		$("#eventDetails").html(eventButton + data);
		var editing = 0;
		$("#set_edit").click(function() {
			if (editing == 0)
			{
				$('#set_edit').html("Done");
				$('#helpnote').css('display', 'block');
				$('#add_set_row').css('display', 'table-row');
				$('.delcol').css('display', 'table-cell');
				$('#set_table tbody').disableSelection().sortable({ update : function() {
					$.post('php/doEditSetlist.php', { action : "arrange", event : id, order : $(this).sortable('toArray').toString().replace(/song/g, '') }, function(data) {
						if (data != 'OK') alert('Error: ' + data);
						fixorder($('#set_table tbody'));
					});
				} });
				$('#set_add_button').click(function() {
					$('#set_empty').css('display', 'none');
					$.post('php/doEditSetlist.php', { action : "add", event : id, song : $('#set_new').children(':selected').prop('value') }, function(data) {
						$('#set_table tbody').append(data);
						add_del_handler();
					});
				});
				add_del_handler();
				editing = 1;
			}
			else
			{
				$('#set_edit').html("Edit");
				$('.delcol').css('display', 'none');
				$('#add_set_row').css('display', 'none');
				$('#helpnote').css('display', 'none');
				$('#set_table tbody').enableSelection().sortable('disable');
				editing = 0;
			}
		});
	});
}

function loadForgotPassword() {
	$.post( 
		'php/forgotPassword.php',
		function(data) {
			$("#main").html(data);
			$("#sendLinkButton").click(sendPasswordResetEmail);
		}
		);
}

function sendPasswordResetEmail() {
	var array = $("#forgotPasswordForm").serializeArray();
	$.post(
		'php/generateEmail.php',
		array,
		function(data) {
			alert(data);
			loadLogin();
		});
}

function seeWhosAttending(eventNo){
	$.post(
		'php/seeWhosAttending.php',
		{ eventNo : eventNo },
		function(data){
			var eventButton = "<div class='btn' onclick='loadDetails(" + eventNo + ");'><i class='icon-arrow-left'></i> Event</div>";
			$("#eventDetails").html(eventButton+data);
			smoothScrollTo("eventDetails");
		}
	);
}

function seeCarpools(id){
	$.post(
		'php/seeCarpools.php',
		{ eventNo : id },
		function(data){
			var eventButton = "<div class='btn' onclick='loadDetails(" + id + ");'><i class='icon-arrow-left'></i> Event</div>";
			$("#eventDetails").html(eventButton+data);
			//$("#eventDetailsDetails").remove();
			//$("eventDetails").removeClass("span3").addClass("span5");
			$("#editCarpoolsButton").click(function(){
				editCarpools(id);
			});
			$("#backToEvent").on('click', function(){
				loadDetails('current');
			});
			smoothScrollTo("eventDetails");
		}
	);
}

function peopleWithoutRides(id){
	$("#events").html("<img style=\"display: block; margin: 0 auto\" src=\"/images/loading.gif\">");
	$.post(
	'php/peopleWithoutRides.php',
	{ eventNo : id },
	function(data){
		$("#events").html(data);
		$(".person").addClass("person-hover");
		$(".person").click(function(){
			personClicked($(this));
		});
	});
}

function addPersonToCarpool(carpool, person){
	//the term 'carpool' is used loosely...
	//console.log("carpool: "+carpool.html()+" person: "+person.html());
	//console.log("driver: "+carpool.hasClass("driver"));
	if(carpool.hasClass("driver")){
		//var name = person.find("td").eq(1).html();
		//var number = carpool.parent().attr("id");
		//console.log("make "+name+" driver of carpool "+number);
		$("#events").append(carpool.children());
		carpool.html(person);
	}
	if(carpool.hasClass("passengers")){
		//console.log("person: "+person+" carpool: "+carpool);
		carpool.append(person);
		//person.remove(); //does it for me!
		person.removeClass("lighter");
	}
	if($(".driver").last().html() !== 'add new driver here first'){
		$('#carpools').append("<div class='carpool block'><div class='driver block'>add new driver here first</div><div class='passengers block'></div></div>");
	}
	resetClassesAndClicks();
}

function resetClassesAndClicks(){
	$(".person").addClass("person-hover");
	$('.driver').removeClass('person-hover');
	$('.passengers').removeClass('person-hover');
	$('.driver').removeClass('lighter');
	$('.passengers').removeClass('lighter');
	$('.person').removeClass('lighter');
	$("#events").removeClass("person-hover");
	$(".person").on('click', function(e){
		if (!e)
		  e = window.event;

		//IE9 & Other Browsers
		if (e.stopPropagation) {
		  e.stopPropagation();
		}
		//IE8 and Lower
		else {
		  e.cancelBubble = true;
		}

		personClicked($(this));

	});
	$(".driver").off('click');
	$(".passengers").off('click');
	$("#events").off('click');
}

function personClicked(person){
	//console.log("Person clicked: " + person.html());
	$(".person").removeClass("lighter");
	person.addClass("lighter");
	$(".person").off('click'); //this doesn't work so well?
	$(".person").removeClass("person-hover");
	var h = person.find(".passengerSpots").html();
	if(h ? (h==null ? false : true) : false){//can be a driver
		$('.driver').addClass('person-hover');
		$(".driver").click(function(){
			addPersonToCarpool($(this), person);
		});
	}
	else{//can be a passenger
		//nothing? since everyone can be a passenger?
	}
	$('.passengers').addClass('person-hover');
	$(".passengers").on('click', function(){
		addPersonToCarpool($(this), person);
	});
	if(person.parent().attr('id') != 'events') {
		$("#events").addClass("person-hover");
		$("#events").on('click', function() {
			$("#events").append(person);
			resetClassesAndClicks();
		});
	}
}

function editCarpools(id){
	//$(".span5").hide();
	//$(".span3").removeClass("span3").addClass("span5");
	peopleWithoutRides(id);
	$('#carpools').append("<div class='carpool block'><div class='driver block'>add new driver here first</div><div class='passengers block'></div></div>");
	$("#editCarpoolsButton").html('save carpools').off().on('click', function(){
		saveCarpools(id);
	});
}

function saveCarpools(id){
	var carpools = jQueryToJSON($('.carpool')); //make this php-friendly
	$("#events").html("<img style=\"display: block; margin: 0 auto\" src=\"/images/loading.gif\">");
	$.post(
	'php/saveCarpools.php',
	{ carpools : carpools, eventNo : id },
	function(data){
		//console.log(data);
		//$('.span5').show();
		//$('.span5').eq(1).removeClass('span5').addClass('span3');
		//$('.span5').eq(1).removeClass('span5').addClass('span3'); //tricksy
		//console.log('h is ');
		loadEvents('all');
		seeCarpools(id);
	});
}

function jQueryToJSON(array){
	var string = '[';
	var driver = '';
	var passengers = '';
	var id='';
	array.each(function(index){
		driver = '"id":"'+$(this).attr('id')+'", "driver":{"email":"'+$(this).find(".driver .person").attr("id")+'"}';
		passengers = '"passengers":[';
		$(this).find('.passengers .person').each(function(){
			passengers += '{"email":"'+$(this).attr('id')+'"},';
		});
		passengers = passengers.substr(0,(passengers.length-1));
		if(passengers.length == 13){
			passengers = '"passengers":""';
		}
		else
		{
			passengers += "]";
		}
		string += "{"+driver+", "+passengers+"},";
	});
	//carpool = {'driver':{'email':'cernst3@gatech.edu'}, 'passengers':[{'email':'joe@joe.com'},{'email':'josh@josh.com'}]}
	string = string.substr(0,(string.length-1));
	string += "]";
	return string;
}

function editDetails(eventNo){
	editEvent(eventNo, "#eventDetails", function(eventNo) { loadDetails(eventNo); }, function() { $("#eventDetails").html("Event deleted"); $("tr.lighter").remove(); }, [{ name : "Back to<br>Event", onclick : "loadDetails(" + eventNo + ")" }]);
	smoothScrollTo("eventDetails");
}

function submitDetails(){
	var array = $("#eventDetails input").serializeArray();
	$('.eventDetailsValue').on('dblclick', editDetails); // FIXME What does this do?
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

function createNotification(options){
	if (options.notificationType == 'simple') {
		var notification = window.webkitNotifications.createNotification(options.icon, options.title, options.body);
		if(options.onclick){notification.onclick = options.onclick;}
		else{notification.onclick = function(){
							this.cancel();
							this.close();
							}}
		if(options.onclose){notification.onclose = options.onclose;}
		if(options.ondisplay){notification.ondisplay = options.ondisplay;}
		if(options.onerror){notification.onerror = options.onerror;}
		if(options.onshow){notification.onshow = options.onshow;}
		notification.show();
		return notification;
	}
	else if(options.notificationType == 'html'){
		return window.webkitNotifications.createHTMLNotification(options.url);
	}
}

function loadTies()
{
	$.post('php/ties.php', function(data) {
		$('#main').html(data);
		$('.tie_hist').on('click', function() {
			var row = $(this).parent().parent();
			if (row.data('tab') == 'hist')
			{
				row.data('tab', '');
				$('#tie_detail').remove();
				return;
			}
			$('#tie_detail').prev('tr').data('tab', '');
			$('#tie_detail').remove();
			row.data('tab', 'hist');
			var id = row.children('td').first().html();
			$.post('php/tie.php', { action : 'history', tie : id }, function(data) {
				row.after("<tr id='tie_detail'><td colspan='4'><div style='margin: 10px; padding: 10px; border: 1px solid black'>" + data + "</div></td></tr>");
				$('.hist_del').on('click', function() {
					var btn = $(this);
					var histid = btn.data('id');
					$.post('php/tie.php', { action : 'histdel', id : histid }, function(data) {
						if (data != 'OK') alert(data);
						else btn.parent().parent().remove();
					});
				});
			});
		});
		$('.tie_edit').on('click', function() {
			var row = $(this).parent().parent();
			if (row.data('tab') == 'edit')
			{
				row.data('tab', '');
				$('#tie_detail').remove();
				return;
			}
			$('#tie_detail').prev('tr').data('tab', '');
			$('#tie_detail').remove();
			row.data('tab', 'edit');
			var id = row.children('td').first().html();
			$.post('php/tie.php', { action : 'editform', tie : id }, function(data) {
				row.after("<tr id='tie_detail'><td colspan='4'><div style='margin: 10px; padding: 10px; border: 1px solid black'>" + data + "</div></td></tr>");
				$('#tie_form').on('submit', function() {
					var newid = $('#tie_num').attr('value');
					var stat = $('#tie_status').attr('value');
					var comments = $('#tie_comments').attr('value');
					$.post('php/tie.php', { action : 'update', tie : id, newid : newid, status : stat, comments : comments }, function(data) {
						if (data != 'OK') alert(data);
						loadTies();
					});
					return false;
				});
				$('.tie_delete').on('click', function() {
					var tie = $(this).data('tie');
					var row = $(this).parent().parent().parent().parent().parent().parent();
					if (confirm("Delete tie " + tie + " and its history?"))
					{
						$.post('php/tie.php', { action : 'delete', tie : tie }, function(data) {
							if (data != 'OK') alert(data);
							else
							{
								row.prev('tr').remove();
								row.remove();
							}
						});
					}
				});
			});
		});
		$('#tie_add').on('click', function() {
			$.post('php/tie.php', { action : 'add', tie : $('#tie_newnum').prop('value') }, function(data) {
				if (data != 'OK') alert(data);
				loadTies();
			});
		});
	});
}

function loadUniformActions()
{
	$(".uniform-change").on("click", function() {
		var id = $(this).parent().data("uniform");
		var name = $(this).parent().parent().find(".uniform-name").attr("value");
		var desc = $(this).parent().parent().find(".uniform-desc").val();
		$.post("php/admin.php", { type : "uniform", action : "edit", id : id, name : name, desc : desc }, function(data) {
			if (data != "OK") alert(data);
			else alert("Uniform updated.");
		});
	});
	$(".uniform-delete").on("click", function() {
		var id = $(this).parent().data("uniform");
		var row = $(this).parent().parent();
		$.post("php/admin.php", { type : "uniform", action : "delete", id : id }, function(data) {
			if (data != "OK") alert(data);
			else row.remove();
		});
	});
	$(".uniform-add").on("click", function() {
		var id = $("#new-uniform-id").attr("value");
		var name = $("#new-uniform-name").attr("value");
		var desc = $("#new-uniform-desc").val();
		var row = $(this).parent().parent();
		$.post("php/admin.php", { type : "uniform", action : "add", id : id, name : name, desc : desc }, function(data) {
			var res = data.split("\n");
			if (res[0] != "OK") alert(data);
			else
			{
				row.replaceWith(res[1]);
				loadUniformActions();
			}
		});
	});
}

function loadSettings()
{
	$.post('php/admin.php', function(data) {
		$('#main').html(data);
		$('select.member').change(function() {
			var tracker = $(this).parent();
			var position = $(this).parent().prev('td').html();
			var newm = $(this).attr('value');
			var old = tracker.data('old');
			$.post('php/updateOfficers.php', { position : position, old : old, new : newm }, function(data) {
				if (data != 'OK') alert(data);
				else tracker.data('old', newm);
			});
		});
		$('.urlchange').on('click', function() {
			var name = $(this).parent().parent().find('.docurl').attr('name');
			var url = $(this).parent().parent().find('.docurl').attr('value');
			$.post('php/admin.php', { type : "doclink", name : name, url : url }, function(data) {
				alert(data);
			})
		});
		$('.urldel').on('click', function() {
			var row = $(this).parent().parent();
			var name = $(this).parent().find('.docurl').attr('name');
			$.post('php/admin.php', { type : "doclink", name : name, action : "delete" }, function(data) {
				if (data != "OK") alert(data);
				else row.remove();
			})
		});
		$('#urladd').on('click', function() {
			$.post('php/admin.php', { type : "doclink", name : $('#newname').attr('value'), url : "" }, function(data) {
				if (data != "OK") alert(data);
				loadSettings(); // FIXME
			});
		});
		$(".dues-submit").on("click", function() {
			var input = $(this).parent().find("input");
			var item = input.data("item");
			var amount = input.attr("value");
			$.post('php/admin.php', { type : "dues", item : item, amount : amount }, function(data) {
				alert(data);
			});
		});
		loadUniformActions();
	});
}

function updateRolePerm(input)
{
	var enable = input.attr("checked") == "checked" ? "true" : "false";
	$.post("php/admin.php", { type : "perm", role : input.data("role"), perm : input.data("perm"), evtype : input.data("evtype"), enable : enable }, function(data) {
		if (data != "OK") alert("Update failed: " + data);
	});
}

function loadProfile(email)
{
	$.get('php/profile.php', { person : email }, function(data) {
		$("#main").html(data);
		$('.info_toggle').on('click', function() {
			var target = $('#tabbox');
			var tab = $(this).data('tab');
			var member = email;
			if (target.data('tab') == tab)
			{
				target.html('');
				target.data('tab', '');
				target.css('border', 'none');
				return false;
			}
			getRosterData(tab, member, target);
			target.data('tab', tab);
			return false;
		});
		
	});
}

function getRosterData(tab, member, target)
{
	$.post('php/rosterData.php', { tab : tab, email : member }, function(data) {
		target.html(data); //.children('.roster_' + $(this).data('tab')).toggle();
		$('.gradetip').tooltip();
		target.css('border', '1px solid #888');
		var row = target.parent().parent().prev('tr').children();
		//$('#semdues').tooltip();
		//$('#latefee').tooltip();
		$('.attendbutton').on('click', function() {
			var mode = $(this).data('mode');
			var eventid = $(this).data('event');
			var val = $(this).data('val');
			if (mode == 'late') val = $(this).siblings('input[name="attendance-late"]').prop('value');
			$.post('php/updateAttends.php', { mode : mode, email : member, eventNo : eventid, value : val }, function(data) {
				if (data == "OK")
				{
					row.find('span.gradecell').html('...');
					row.find('span.gigscell').html('...');
					getRosterData(tab, member, target);
					$.post('php/rosterData.php', { tab : 'col', col : 'score', email : member }, function(data) { row.find('span.gradecell').replaceWith(data); });
					$.post('php/rosterData.php', { tab : 'col', col : 'gigs', email : member }, function(data) { row.find('span.gigscell').replaceWith(data); });
				}
				else alert(data);
			});
		});
		$('.semesterbutton').on('click', function() {
			var row = $(this).parent().parent().parent();
			var sem = row.data('semester');
			var val = $(this).data('val');
			$.post('php/updateConfirmed.php', { email : member, semester : sem, confirmed : val }, function(data) {
				if (data != 'OK') alert(data);
				else if (val == 0) row.find('select.section').prop("disabled", true);
				else row.find('select.section').prop("disabled", false);
			});
		});
		$('.section').on('change', function() {
			var sem = $(this).parent().parent().data('semester');
			var val = $(this).attr('value');
			$.post('php/updateConfirmed.php', { email : member, semester : sem, section : val }, function(data) {
				if (data != 'OK') alert(data);
			});
		});
		$('.tie_checkout').on('click', function() {
			var member = $(this).data('member');
			var tienum = $(this).parent().children('.tienum').prop('value');
			$.post('php/tie.php', { member : member, action : 'checkout', tie : tienum }, function(data) {
				if (data == 'OK')
				{
					row.find('span.tiecell').html('...');
					getRosterData(tab, member, target);
					$.post('php/rosterData.php', { tab : 'col', col : 'tie', email : member }, function(data) { row.find('span.tiecell').replaceWith(data); });
				}
				else alert(data);
			});
		});
		$('.tie_return').on('click', function() {
			var member = $(this).data('member');
			$.post('php/tie.php', { member : member, action : 'return' }, function(data) {
				if (data == 'OK')
				{
					row.find('span.tiecell').html('...');
					getRosterData(tab, member, target);
					$.post('php/rosterData.php', { tab : 'col', col : 'tie', email : member }, function(data) { row.find('span.tiecell').replaceWith(data); });
				}
				else alert(data);
			});
		});
		var editing = 0;
		$('.edit_member').on('click', function() {
			if (editing == 0)
			{
				$(this).html('Done');
				editing = 1;
				$.post('php/rosterData.php', { tab : 'details_edit', email : member }, function(data) {
					target.children('.detail_table').html(data);
				});
			}
			else
			{
				var array = target.children('.detail_table').find('input').serializeArray();
				$.post('php/doEditProfile.php', array, function(data) {
					if (data == 'OK')
					{
						getRosterData(tab, member, target);
						$(this).html('Edit');
						editing = 0;
					}
					else alert(data);
				});
			}
		});
		$('.transac_edit').on('click', function() {
			var id = $(this).parent().parent().data('id');
			$.post('php/doEditTransaction.php', { action : $(this).data('action'), id : id }, function(data) {
				if (data != 'OK') alert(data);
				row.find('span.moneycell').html('...');
				row.find('span.duesell').html('...');
				row.find('span.tiecell').html('...');
				getRosterData(tab, member, target);
				$.post('php/rosterData.php', { tab : 'col', col : 'balance', email : member }, function(data) { row.find('span.moneycell').replaceWith(data); });
				$.post('php/rosterData.php', { tab : 'col', col : 'dues', email : member }, function(data) { row.find('span.duescell').replaceWith(data); });
				$.post('php/rosterData.php', { tab : 'col', col : 'tie', email : member }, function(data) { row.find('span.tiecell').replaceWith(data); });
			});
			return false;
		});
	});
}

function roster()
{
	$.post('php/roster.php', function(data) {
		$('#main').html(data);
		function member_table(cur)
		{
			if (typeof cur != 'undefined') cur.toggleClass('active'); // Ick
			var cond = '';
			$("#roster_filters button.active").each(function() { cond += $(this).data("cond") + ","; });
			cond = cond.replace(/,$/, "");
			if (typeof cur != 'undefined') cur.toggleClass('active');
			$("a.tablelink").each(function() {
				$(this).attr("href", ($(this).attr("href").split("?")[0] + "?format=" + $(this).data("format") + "&filter=" + cond));
			});
			$.get('php/memberTable.php', { filter : cond }, function(data) {
				$('#roster_table').html(data);
			});
		}
		$('.filter').on('click', function() { member_table($(this)); });
		$('.fmt_tbl').on('click', function() { formatted_table($(this).data('format')); return false; });
		member_table();
	});
}

function chgusr(user)
{
	$.post('php/chgusr.php', { user : user }, function(data) { location.href = '#'; location.reload(); });
}

function setChoir(choir)
{
	$.post('php/setChoir.php', { choir : choir }, function(data) {
		if (data != 'OK') alert(data);
		else location.reload();
	});
}

function delusr(user)
{
	if (confirm("This will irreversibly delete all of " + user + "'s data.  Proceed?")) $.post('php/doDeleteMember.php', { email : user }, function(data) {
		if (data != 'OK') alert(data);
		else { location.href = '#'; alert("User deleted successfully"); }
	});
}

var ntrans = 0;

function loadMoney()
{
	$.post('php/money.php', {}, function(data) {
		$('#main').html(data);
		ntrans = 0;
	});
}

/**
* Makes the money page into a form for with which to add transactions and increments the new transaction count
*/
function addMoneyForm()
{
	ntrans++;
	$.post('php/money.php', { action : 'row' }, function(html) {
		$('#transac').append(html);
		function setamt(row)
		{
			var type = row.find('.ttype').attr('value');
			var field = row.find('.amount');
			var amt = field.data('amount-' + type);
			if (typeof amt == 'undefined') field.attr('value', "");
			else field.attr('value', amt);
		}
		$('.name').on('change', function() {
			var row = $(this).parent().parent();
			$.post('php/money.php', { action : 'values', member : $(this).attr('value') }, function(data) {
				row.find('.amount').data('amount-deposit', data);
				setamt(row);
			});
		});
		$('.ttype').on('change', function() { setamt($(this).parent().parent()); });
		if (ntrans == 1)
		{
			$('#roster_ops').append("<span id='trans_ops'><span class='spacer'></span><button type='button' class='btn' id='cancel_all_trans'>Cancel All Transactions</button><span class='spacer'></span><button type='button' class='btn' id='trans_submit'>Submit These Transactions</button></span>");
			$('#cancel_all_trans').on('click', function() {
				$('.trans_row').remove();
				$('#trans_ops').remove();
				ntrans = 0;
			});
			$('#trans_submit').on('click', function() {
				var table = document.getElementById("moneyForm");
				var row, email, amount, desc;
				var emailArr = [];
				var amountArr = [];
				var descArr = [];
				var sendEmailArr = [];
				var typeArr = [];
				var semArr = [];
				var i = 0;
				var err = false;
				$('.trans_row').each(function() {
					sendEmailArr[i] = $(this).children().children('.receipt').prop('checked');
					emailArr[i] = $(this).children().children('.member').prop('value');
					amountArr[i] = $(this).children().children('.amount').prop('value');
					descArr[i] = $(this).children().children('.description').prop('value');
					typeArr[i] = $(this).children().children('.ttype').prop('value');
					semArr[i] = $(this).children().children('.semester').prop('value');
					$(this).children().children('.amount').css('background-color', '');
					if (! /^-?\d+$/.test(amountArr[i]))
					{
						$(this).children().children('.amount').css('background-color', '#f88');
						err = true;
					}
					i++;
				});
				if (err) return false;
				if (i == 0)
				{
					$('.trans_row').remove();
					$('#trans_ops').remove();
				}
				else
				{
					var emailList = JSON.stringify(emailArr);
					var amountList = JSON.stringify(amountArr);
					var descList = JSON.stringify(descArr);
					var sendEmailList = JSON.stringify(sendEmailArr);
					var typeList = JSON.stringify(typeArr);
					var semList = JSON.stringify(semArr);
					$.post('php/addTransactions.php', { emails: emailList, amounts: amountList, descriptions: descList, types: typeList, semesters: semList, sendEmails: sendEmailList }, function(data) {
						if (data != 'OK') alert(data);
						loadMoney();
					});
				}
			});
		}
		$('.cancel').unbind('click');
		$('.cancel').on('click', function() {
			$(this).parent().parent().remove();
			ntrans--;
			if (ntrans == 0) $('#trans_ops').remove();
		});
	});
}

function addDues()
{
	$.post('php/doSemDues.php', { type : 'dues' }, function(data) { if (data != 'OK') alert('Error:  ' + data); else roster(); });
}

function addLateFee()
{
	$.post('php/doSemDues.php', { type : 'late' }, function(data) { if (data != 'OK') alert('Error:  ' + data); else roster(); });
}

function setGigCheck(value)
{
	$.post('php/rosterAction.php', { action : 'gigcheck', value : (value == 'checked' ? 1 : 0) }, function(data) { if (data != 'OK\n') alert('Error:  ' + data); else roster(); });
}

function setGigReq(value)
{
	$.post('php/rosterAction.php', { action : 'gigreq', value : value }, function(data) { if (data != 'OK\n') alert('Error:  ' + data); else roster(); });
}

function requestNotificationsPermission()
{
	if(!window.webkitNotifications || window.webkitNotifications.checkPermission() == 0 || window.webkitNotifications.checkPermission() == 1) $('#notificationsButton').hide();
	else
	{
		$('#notificationsButton').on('click', function(){
			//console.log('ask for permissions');
			/*if(window.webkitNotifications.checkPermission() == 0) { // 0 is PERMISSION_ALLOWED
				var notif = createNotification({ notificationType: 'simple',
				icon:'placekitten.com/100/100',
				title:'Notification!',
				body:'You can have notifications',
				onclick:function(){
					//alert('notification clicked');
					this.cancel();
					this.close();
					},
				onclose:function(){
					//alert('notification closed');
					},
				ondisplay:function(){
					//alert('notification displayed');
					},
				onerror:function(){
					//alert('notification error :(');
					},
				onshow:function(){
					//alert('notification showed');
					}
				});
			}
			else{
				window.webkitNotifications.requestPermission();
			}*/
		});
	}
}

function confirm_account()
{
	var reg = '';
	if ($('#confirm_class').hasClass('active')) reg = 'class';
	else if ($('#confirm_club').hasClass('active')) reg = 'club';
	else { alert("You must select \"Class\" or \"Club\" to confirm your account."); return; }
	var loc = $('#confirm_location').prop('value');
	var sect = $('select.section').prop('value');
	$.post('php/doConfirmAccount.php', { registration : reg, location : loc, section : sect }, function(data) { if (data != 'OK') alert("Error: " + data); $('#confirmModal').modal('hide'); });
}

function feedbackForm()
{
	$.post('php/gforms.php?type=feedback', function(data) { $('#main').html(data); });
}

function songForm()
{
	$.post('php/gforms.php?type=song', function(data) { $('#main').html(data); });
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
***************** Absence Request Related Functions ************************
****************************************************************************/

/**
* Fill the main div with absence request data
*/
function seeAbsenceRequests(){
	$.post('php/seeAbsenceRequests.php', function(data){
		$('#main').html(data);
	});	
}

/**
* Approve a pending absence, and update the row so the user sees the change (and can toggle it)
*/
function approveAbsence(eventID,memberID){
	$.post('php/updateAbsenceRequest.php', { eventNo: eventID, email: memberID, action: "approve" }, function(data){
		var id="request_"+memberID+"_"+eventID;
		document.getElementById(id).innerHTML = data;
	});
}

/**
* Deny a pending absence, and update the row so the user sees the change (and can toggle it)
*/
function denyAbsence(eventID,memberID){
	$.post('php/updateAbsenceRequest.php', { eventNo: eventID, email: memberID, action: "deny" }, function(data){
		var id="request_"+memberID+"_"+eventID;
		document.getElementById(id).innerHTML = data;
	});
}

/**
* Toggle the status of an approved/denied absence request, and update the row so the user sees the change (and can toggle it)
*/
function toggleRequestState(eventID,memberID){
	$.post('php/updateAbsenceRequest.php', { eventNo: eventID, email: memberID, action: "toggle" }, function(data){
		var id="request_"+memberID+"_"+eventID;
		document.getElementById(id).innerHTML = data;
	});
}

/**
* Fill the eventDetails div with an absence request form, and scroll to the div so they can see it
*/
function requestAbsence(eventID){
	$.post('php/requestAbsencePage.php', { eventNo:  eventID}, function(data){
		var eventButton = "<div class='btn' onclick='loadDetails(" + eventID + ");'><i class='icon-arrow-left'></i> Event</div>";
		$('#eventDetails').html(eventButton+data);
		$("#submitAbsenceRequest").click(function(){
			submitAbsenceRequest(eventID);
		});
		smoothScrollTo("eventDetails");
	});
}

/**
* Submit the request for the user, and fill the eventDetails div with confirmation or error feedback.
*/
function submitAbsenceRequest(eventID){
	if($("#absenceRequestTable").length>0){
		var replacementEmail = $(".replacement").attr("value");
		var reasonText = $("#reason").attr("value");
		$.post('php/requestAbsence.php', { eventNo:  eventID, replacement: replacementEmail, reason: reasonText }, function(data){
			var eventButton = "<div class='btn' onclick='loadDetails(" + eventID + ");'><i class='icon-arrow-left'></i> Event</div>";
			$('#eventDetails').html(eventButton+data);

			//if they didn't specify a reason, give them a chance to try again
			if($("#retryAbsenceButton").length>0){
				$("#retryAbsenceButton").click(function(){
					//requestAbsence($("#retryAbsenceButton").attr("value"));
					requestAbsence(eventID);
				});
			}
		});
	}
}
/***************************************************************************
************* End of Absence Request Related Functions *********************
****************************************************************************/

/***************************************************************************
******************* Attendance Related Functions ***************************
****************************************************************************/

function updateEventAttendance(eventID)
{
	$.post('php/seeEventAttendance.php', { eventNo : eventID }, function(data) {
		var eventButton = "<div class='btn' onclick='loadDetails(" + eventID + ");'><i class='icon-arrow-left'></i> Event</div>";
		$('#eventDetails').html(eventButton+data);
		smoothScrollTo("eventDetails");
	});
}

function setDidAttendEvent(eventID, memberID, newDidAttend)
{
	$.post('php/doAttendance.php', { eventNo: eventID, email: memberID, action: 'did', value: newDidAttend }, function(data) {
		document.getElementById("attends_"+memberID+"_"+eventID).innerHTML = data;
	});
}

function setShouldAttendEvent(eventID, memberID, newShouldAttend)
{
	$.post('php/doAttendance.php', { eventNo: eventID, email: memberID, action: 'should', value: newShouldAttend }, function(data) {
		document.getElementById("attends_"+memberID+"_"+eventID).innerHTML = data;
	});
}

function setMinutesLate(eventID, memberID, newMinutesLate)
{
	$.post('php/doAttendance.php', { eventNo: eventID, email: memberID, action: 'late', value: newMinutesLate }, function(data) {
		document.getElementById("attends_"+memberID+"_"+eventID).innerHTML = data;
	});
}

function setConfirmed(eventID, memberID, confirmed)
{
	$.post('php/doAttendance.php', { eventNo: eventID, email: memberID, action: 'confirmed', value: confirmed }, function(data) {
		document.getElementById("attends_"+memberID+"_"+eventID).innerHTML = data;
	});
}

function excuseall(eventID)
{
	$.post('php/doAttendance.php', { action : 'excuse_all', eventNo : eventID }, function(data) { updateEventAttendance(eventID); });
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

function should_attend(eventID, memberID, newShouldAttend)
{
	$.post('php/loadButtonArea.php', { eventNo: eventID, attending: newShouldAttend }, function(data) {
		$('tr#' + eventID).children().last().html(data);
		loadDetails(eventID);
	});
}

function is_confirmed(eventID, memberID, confirmed)
{
	$.post('php/loadButtonArea.php', { eventNo: eventID, attending: 1 }, function(data) {
		$('tr#' + eventID).children().last().html(data);
		loadDetails(eventID);
	});
}

/***************************************************************************
************** End of Attendance Related Functions *************************
****************************************************************************/

/***************************************************************************
******************* Announcement Related Functions *************************
****************************************************************************/

function addAnnouncement(){
	$.post(
		'php/addAnnouncement.php',
		function(data){
			$('#main').html(data);
			$("#addAnnouncementButton").on('click',function(){
				$("#addAnnouncementButton").off('click');

				//add the announcement to the db
				var text = $('#announcementText').val();
				doAddAnnouncement(text);

				//then load the stats page
				window.location.hash = 'stats';
			});
		}
	);
}

function doAddAnnouncement(text){
	$.post(
		'php/doAddAnnouncement.php', 
		{text:text},
		function(data){}
	);
}

/*
        todo stuff
*/

function submitNewTodo(){
	var text = $('#newTodo').val();
	console.log(text);
	$.post(
		'php/doAddTodo.php',
		{message:text},
		function(data){
			$('#newTodo').val('');
			refreshTodos();
		}
		);
}

function submitMultiTodo(){
	var text = $('#todoText').val();
	var people = $('#multiTodo').val();
	$.post(
		'php/doAddMultiTodo.php',
		{message:text, userList:people},
		function(data){
			$('#todoText').val('');
			$('#multiTodo').val('');
			refreshTodos();
		}
		);
}

function refreshTodos(){
	$.post(
		'php/refreshTodos.php',
		function(data){
			$('#todos').html(data);
			listenOnTodos();
		}
		);
}

function listenOnTodos(){
	$('#todos input').on('click', function(){
		completeTodo($(this).attr('id'));
	});
}

function completeTodo(id){
	console.log(id);
	if($('#'+id).is(':checked')){
		//console.log('make this completed');
		$.post(
			'php/completeTodo.php',
			{id:id, status:'complete'},
			function(data){
				refreshTodos();
			}
			);
	}
	else{
		//console.log('make this not completed');	
		$.post(
			'php/completeTodo.php',
			{id:id, status:'incomplete'},
			function(data){
				refreshTodos();
			}
			);
	}
}

/*
        end todo stuff
*/

/***************************************************************************
************** End of Announcement Related Functions *************************
****************************************************************************/

/***************************************************************************
****** Constitution and Handbook Related Functions *************************
****************************************************************************/

function loaddoc(name)
{
	$.post('php/loaddoc.php', { name : name }, function(data)
	{
		var content = data.split('\n');
		if (content[0] != 'OK') alert(data);
		else $('#main').html("<iframe id=\"docwin\" src=\"" + content[1] + "\" style=\"border: none; width: 100%; height: 60em\"></iframe>");
		// There's no easy way to make this iframe fit its content because the request is cross-origin....
	});
}

/***************************************************************************
******* End of Constitution and Handbook Related Functions *****************
****************************************************************************/

/***************************************************************************
************************* Semester Functions *******************************
****************************************************************************/

/**
* Load a page where the President can add new semesters and change the current semester.
*/
function loadAddSemester(){
	$.post(
		'php/semesterPage.php',
		function(data){
			$('#main').html(data);
			$('.semesterDiv').addClass('span8 block inline-block');

			var position = $('#newSemesterDiv').offset();
			$('.semesterDiv').css('position','absolute');
			$('.semesterDiv').css('top',position.top);
			$('.semesterDiv').css('left',position.left);
			$('.semesterDiv').css('visibility','hidden');

			$('.semesterChoice').click(function(){
				$('.btn-info').removeClass('btn-info');
				$(this).addClass('btn-info');
				$('.semesterDiv').css('visibility','hidden');
				var divID = '#'+$(this).val();
				$(divID).css('visibility','');
			});
		}
	);
}

/**
* Check the fields of the new semester form and make sure they are valid
*/
function checkAddSemesterFields(){
	$(".DD, .MM, .YYYY, .semesterName").blur( function() {
		//the regex defining valid input
		var MM = new RegExp("(^0[1-9]$)|(^1[0-2]$)");
		var DD = new RegExp("(^0[1-9]$)|(^[1-2]\\d$)|(^3[0-1]$)");
		var YYYY = new RegExp("(^199\\d$)|(^[2-9]\\d\\d\\d$)");

		//check all of the regex
		$.each($('.MM'), function(){
			if(!MM.exec($(this).val())){
				$(this).attr('style',"background-color: red;");
				$(this).addClass('invalid');
			}
			else{
				$(this).attr('style',"background-color: white;");
				$(this).removeClass('invalid');
			}
		});

		$.each($('.DD'), function(){
			if(!DD.exec($(this).val())){
				$(this).attr('style',"background-color: red;");
				$(this).addClass('invalid');
			}
			else{
				$(this).attr('style',"background-color: white;");
				$(this).removeClass('invalid');
			}
		});

		$.each($('.YYYY'), function(){
			if(!YYYY.exec($(this).val())){
				$(this).attr('style',"background-color: red;");
				$(this).addClass('invalid');
			}
			else{
				$(this).attr('style',"background-color: white;");
				$(this).removeClass('invalid');
			}
		});

		$.each($('.semesterName'), function(){
			if($(this).val().length<1){
				$(this).attr('style',"background-color: red;");
				$(this).addClass('invalid');
			}
			else{
				$(this).attr('style',"background-color: white;");
				$(this).removeClass('invalid');
			}
		});

		//if all of the input is valid
		if($('.invalid').length==0){
			$('.semesterSubmit').removeAttr("disabled");
			//give the submit button the power to insert semesters into the database
			$('.semesterSubmit').not('.changeToNewSemester').click(function(){
				submitNewSemester();
			});
			//if the button is also meant to change the semester, give it that power as well
			$('.semesterSubmit.changeToNewSemester').click(function(){
				submitNewSemester();
				changeToNewSemester(true);
			});
		}
		else
			$('.semesterSubmit').attr('disabled',true);
	});
}

/**
* Check the fields of the remove semester form and make sure they are valid
*/
function checkRemoveSemesterFields(){
	$('.semesterRemove').click(function(){
		if(window.confirm("Are you sure that you want to delete all of the event and attendance info for this entire semester?"))
			removeSemester();
		else
			alert('Nothing was done.');
	});

	$(".semesterSelect").change(function(){
		var val = $(this).val();
		if(val==''){
			$('.semesterRemove').attr('disabled',true);
		}
		else{
			$('.semesterRemove').removeAttr('disabled');
		}
	}); 
}

/**
* Check the fields of the change semester form and make sure they are valid
*/
function checkChangeSemesterFields(){
	$('.semesterChange').click(function(){
		if(window.confirm("Are you sure that you want to switch to the selected semester?  If you do, everything on the main site will look like it is "+$("#changeSemesterName").val()))
			changeToNewSemester(false);
		else
			alert('Nothing was done.');
	});

	$(".changeSemesterName").change(function(){
		var val = $(this).val();
		if(val==''){
			$('.semesterChange').attr('disabled',true);
		}
		else{
			$('.semesterChange').removeAttr('disabled');
		}
	}); 
}

function submitNewSemester(){
  	var name = $('#newSemesterName').val();
  	var sDD = $('#sDD').val();
  	var sMM = $('#sMM').val();
  	var sYYYY = $('#sYYYY').val();
  	var eDD = $('#eDD').val();
  	var eMM = $('#eMM').val();
  	var eYYYY = $('#eYYYY').val();

	$.post(
		'php/doAddNewSemester.php',
		{name:name, sDD:sDD, sMM:sMM, sYYYY:sYYYY, eDD:eDD, eMM:eMM, eYYYY:eYYYY},
		function(data){
			$('#newSemesterDiv').html(data);
		}
	);
}

function changeToNewSemester(newSem){
	var name, divID;
	if(newSem){
		name = $('#newSemesterName').val();
		$.post(
			'php/doChangeSemester.php',
			{name:name},
			function(data){
				$('#newSemesterDiv').append(data);
			}
		);
	}
	else{
		name = $('#changeSemesterName').val();
		$.post(
			'php/doChangeSemester.php',
			{name:name},
			function(data){
				$('#changeSemesterDiv').html(data);
			}
		);
	}
}

function removeSemester(){
	var name = $('#rmSemesterName').val();
  	$.post(
		'php/doRemoveSemester.php',
		{name:name},
		function(data){
			$('#removeSemesterDiv').html(data);
		}
	);
}

/***************************************************************************
********************* End of Semester Functions ****************************
****************************************************************************/

/***************************************************************************
********************* Add/Remove Event Functions ***************************
****************************************************************************/

function editEvent(id, element, callback = function() { alert("Success"); }, deleteCallback = function() { alert("Event deleted."); }, extraButtons = [])
{
	if (id > 0) var params = { id : id };
	else if (id < 0) var params = { gigreq: -id };
	else var params = {};
	$.post('php/editEvent.php', params, function(data) {
		var content = '<div style="float: right">';
		for (var i = 0; i < extraButtons.length; i++) content += '<button type="button" class="btn" onclick="' + JSON.stringify(extraButtons[i].onclick).slice(1, -1) + '">' + extraButtons[i].name + '</button>';
		content += '</div>';
		content += '<h3>' + (id == 0 ? "Add" : "Edit") + ' Event</h3><div id="event-data">' + data + '</div><div class="pull-right">';
		if (id > 0) content += '<button type="button" class="btn" id="event_delete">Delete Event</button><span class="spacer" style="padding: 0 5px"></span>';
		content += '<button type="button" class="btn" id="event_submit">Submit</button></div>';
		$(element).html(content);
		$('#event_general').show();
		if (id > 0) $('select[name="type"]').attr('disabled', 'true');
		$('select[name="type"]').on('change', function() {
			$('#event_gig').hide();
			$('#event_rehearsal').hide();
			var type = $(this).prop('value');
			if (type == 'rehearsal' || type == 'sectional') $('#event_rehearsal').show();
			else if (type == 'volunteer' || type == 'tutti') $('#event_gig').show();
			$('input[name="gigcount"]').prop('checked', type == 'volunteer');
			if (id <= 0) $('input[name="defaultAttend"]').prop('checked', type != 'ombuds' && type != 'other');
			if (type == 'sectional') $('#event_row_section').show();
			else $('#event_row_section').hide();
			if (id <= 0)
			{
				$('input[name="gigcount"]').prop('checked', type == 'volunteer');
				var points = 10;
				if (type == 'ombuds') points = 5;
				if (type == 'other') points = 0;
				if (type == 'sectional') points = 5;
				else if (type == 'tutti') points = 35;
				$('#event_row_points').find('input').prop('value', points);
				if (type == 'rehearsal' || type == 'sectional') $('select[name="repeat"]').prop('value', 'weekly');
				else $('select[name="repeat"]').prop('value', 'no');
			}
			$('select[name="repeat"]').trigger('change');
		});
		$('select[name="repeat"]').on('change', function() {
			if ($(this).prop('value') != 'no') $('#event_row_until').show();
			else $('#event_row_until').hide();
		});
		$('input[name="public"]').on('change', function() {
			if($(this).prop('checked'))
			{
				$('#event_row_summary').show();
				$('#event_row_description').show();
			}
			else
			{
				$('#event_row_summary').hide();
				$('#event_row_description').hide();
			}
		});
		$('input[name="donedate"]').datepicker();
		$('input[name="calldate"]').datepicker().on('changeDate', function() {
			$('input[name="donedate"]').prop('value', $('#event_row_calldate').find('input').prop('value'));
		});
		$('input[name="until"]').datepicker();
		$('#event_submit').on('click', function() {
			var disabled = $('#event-data').find(':input:disabled').removeAttr('disabled');
			var details = $('#event-data').find('input').serializeArray();
			details = details.concat($('#event-data').find('textarea').serializeArray());
			details = details.concat($('#event-data').find('select').serializeArray());
			disabled.attr('disabled', 'disabled');
			var submit = '';
			if (id <= 0) submit = 'php/doNewEvent.php';
			else
			{
				submit = 'php/doEditDetails.php';
				details.push({ name : 'id', value : id });
			}
			$.post(submit, details, function(data) {
				if (data.match(/^\d+$/))
				{
					if (callback) callback(data);
				}
				else alert("Error: " + data);
			});
		});
		$('#event_delete').on('click', function() {
			if (confirm("Really delete this event?")) $.post('php/doRemoveEvent.php', { eventNo: id }, function(data) { if (deleteCallback) deleteCallback(data); });
		});
		$('select[name="type"]').trigger('change');
		//$('select[name="repeat"]').trigger('change');
		$('input[name="public"]').trigger('change');
	});
}

function addOrRemoveEvent()
{
	$("#main").html('<div class="span5 block" id="add_event">');
	editEvent(0, "#add_event", function(id) { window.location.hash = "event:" + id; });
	$("#main").append('</div>');
	removeEventDiv();
}

function removeEventDiv() {
	$.post(
		'php/removeEvent.php',
		function(data) {
			$("#main").append(data);
			$('.removeFilterBtn').click(
				function(){
					toggleFilter($(this).attr('id'));
					appendRemoveableEvents();
			});
			appendLoadScreen('#filters');

	});
}

function toggleFilter(id){
	$('[id="'+id+'"]').toggleClass('btn-info checked');
}

function appendRemoveableEvents(){
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
		'php/loadRemoveableEvents.php',
		{types:h,semesters:s},
		function(data){
			$("#removeEventDiv").append(data);
			$(".removeButton").click(function(){
				removeEventButtonClick($(this).attr('id'));
			});
			toggleLoadScreen();
		}
	);
}

/**
* Removes and event from the database and reloads the list of events after a remove button is clicked on the add/remove event page
*/
function removeEventButtonClick(id){
	var jID = '#'+id;
	var eventName = $(jID).val();
	if(window.confirm("Are you sure that you want to delete "+eventName+"?")){
		$.post(
			'php/doRemoveEvent.php',
			{eventNo:id},
			function(data){
				appendRemoveableEvents();
			}
		);
	}
	else
		return;
}

/***************************************************************************
***************** End of Add/Remove Event Functions ************************
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

/***************************************************************************
********************* Stuff from taylorJS.js *******************************
****************************************************************************/

function checkTime(pat) {
	var reg = /^((1[0-2])|(0?[1-9]))\:[0-5][0-9](am|pm)$/;
	return reg.test(pat);

}

function checkTimeInput() {
	if(!checkTime($(this).val())) {
		$(this).css({'background-color' : 'red'});
	} else {
		$(this).css({'background-color' : 'transparent'});
	}
}

function checkDate(pat) {
	var reg = /^(January|February|March|April|May|June|July|August|September|October|November|December) ([1-2][0-9]|3[0-1]|0?[1-9]), 20[0-9][0-9]$/;
	return reg.test(pat);
}

function checkDateInput() {
	if(!checkDate($(this).val())) {
		$(this).css({'background-color' : 'red'});
	} else {
		$(this).css({'background-color' : ''});
	}
}

/***************************************************************************
****************** End of Stuff from taylorJS.js ***************************
****************************************************************************/

/***************************************************************************
*********************** Matthew's functions ********************************
****************************************************************************/

function showMinutes(loadid)
{
	$.post('php/showMinutes.php', function(data) {
		$('#main').html(data);
		var name = "";
		var view_mode = 1; // 0 = private, 1 = public
		var load_minutes = function(id) {
			$('#minutes' + id).addClass('lighter');
			$.post('php/getMinutes.php', { id : id }, function(data) {
				smoothScrollTo('minutes_main');
				var textPrivate = data;
				var textPublic = "";
				$.post('php/getMinutes.php', { id : id, type : 'name' }, function(name) {
					$('#minutes_main').html("<div id=minutes_view style='clear: both'></div>");
					$('#minutes_main').prepend("<div class=pull-right style=\"padding-left: 10px; padding-right: 10px\" id=\"minutes_tools\"></div>");
					var edit_mode = 0; // 0 = view, 1 = edit
					$.ajax({ url : 'php/hasPermission.php', data : { permission : "edit-minutes" }, async : false, success : function(data) {
						if (data == "1")
						{
							$('#minutes_tools').append(
								"<div class=pull-right style=\"padding-left: 10px; padding-right: 10px\">" +
									"<div class=\"btn-group\">" +
										"<a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\"><i class=\"icon-cog\"></i> <span class=\"caret\"></span></a>" +
										"<ul class=\"dropdown-menu\">" +
											"<li><a href=\"#\" id=\"minutes_send\">Send Email</a></li>" +
											"<li><a href=\"#\" id=\"minutes_delete\">Delete</a></li>" +
										"</ul>" +
									"</div>" +
								"</div>" +
								"<div class=pull-right style=\"padding-left: 10px; padding-right: 10px\">" +
									"<button class=\"btn\"id=minutes_edit>Edit</button>" +
								"</div>"
							)
							$('#minutes_edit').click(function()
							{
								if (edit_mode == 0)
								{
									$('#minutes_edit').html('Done');
									$('#minutes_view').html("<input type=text id=minutes_title><br><div id=private_container><textarea id=minutes_text_private rows=20 style=\"width: 99%\">" + textPrivate + "</textarea></div><div id=public_container><textarea id=minutes_text_public rows=20 style=\"width: 99%\">" + textPublic + "</textarea></div>");
									tinymce.init({ selector: "textarea", height: 500, plugins: "lists textcolor", toolbar: "undo redo | formatselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | numlist bullist indent outdent | removeformat" });
									if (view_mode == 0)
									{
										$('#public_container').css('display', 'none');
										$('#private_container').css('display', 'inline');
									}
									else
									{
										$('#private_container').css('display', 'none');
										$('#public_container').css('display', 'inline');
									}
									$('#minutes_public').off('click');
									$('#minutes_private').off('click');
									$('#minutes_public').click(function() {
										view_mode = 1;
										$('#private_container').css('display', 'none');
										$('#public_container').css('display', 'inline');
									});
									$('#minutes_private').click(function() {
										view_mode = 0;
										$('#public_container').css('display', 'none');
										$('#private_container').css('display', 'inline');
									});
									$('#minutes_title').attr('value', name);
									edit_mode = 1;
								}
								else
								{
									$('#minutes_edit').html('Edit');
									edPublic = tinymce.get("minutes_text_public");
									edPrivate = tinymce.get("minutes_text_private");
									textPublic = edPublic.getContent();
									textPrivate = edPrivate.getContent();
									name = $('#minutes_title').attr('value');
									if (view_mode == 0) $('#minutes_view').html(textPrivate);
									else $('#minutes_view').html(textPublic);
									$('#minutes_public').off('click');
									$('#minutes_private').off('click');
									$('#minutes_public').click(function() {
										view_mode = 1;
										$('#minutes_view').html(textPublic);
									});
									$('#minutes_private').click(function() {
										view_mode = 0;
										$('#minutes_view').html(textPrivate);
									});
									$('td#minutes' + id).html(name);
									$.post('php/doEditMinutes.php', { id : id, newname : name, private : textPrivate, public : textPublic }, function(data) {
										res = data.split('\n');
										if (res[0] != "OK") alert("Error:  " + data);
									});
									edPublic.destroy();
									edPrivate.destroy();
									edit_mode = 0;
								}
								return false;
							});
							$('#minutes_delete').click(function() {
								if (confirm("Delete \"" + name + "\"?")) $.post('php/doEditMinutes.php', { id : id, newname : ".DELETE", private : "", public : "" }, function(data) {
									res = data.split('\n');
									if (res[0] == 'OK')
									{
										$('td#minutes' + id).remove();
										$('#minutes_main').html("Select a meeting to the left.");
									}
									else alert("Error:  " + res[0]);
								});
								return false;
							});
							$('#minutes_send').click(function() {
								$.post('php/doSendMinutes.php', { id : id }, function(data) {
									if (data == "OK") alert("Email successfully sent");
									else alert("Error: " + data);
								});
								return false;
							});
						};
					}});
					$.ajax({ url : 'php/hasPermission.php', data : { permission : "view-complete-minutes" }, async : false, success : function(data) {
						if (data == "1")
						{
							$('#minutes_tools').append(
								"<div class=\"btn-group pull-right\" style=\"padding-left: 10px; padding-right: 10px\" data-toggle=\"buttons-radio\">" +
									"<button class=btn id=minutes_public>Redacted</button>" +
									"<button class=btn id=minutes_private>Complete</button>" +
								"</div>");
							if (view_mode == 0) $('#minutes_private').button('toggle');
							else $('#minutes_public').button('toggle');
							$.ajax({ url : 'php/getMinutes.php', type : "POST", data : { id : id, public : 1 }, async : false, success : function(data) {
								textPublic = data;
							}});
							$('#minutes_public').click(function() {
								view_mode = 1;
								$('#minutes_view').html(textPublic);
							});
							$('#minutes_private').click(function() {
								view_mode = 0;
								$('#minutes_view').html(textPrivate);
								
							});
							if (view_mode == 0) $('#minutes_view').html(textPrivate);
							else $('#minutes_view').html(textPublic);
							$.post('php/todo.php', { form : 'true' }, function(data)
							{
								$('#minutes_main').append("<div class=\"block\">" + data + "</span>");
								$('#newTodoButton').on('click', function() { submitNewTodo(); });
								$('#multiTodo').tokenInput("php/searchMembers.php", { theme:"facebook", preventDuplicates:true });
								$('#multiTodoButton').on('click', submitMultiTodo);
							});
						}
						else $('#minutes_view').html(textPrivate);
					}});
				});
			});
		};
		var minutes_row_click = function() {
			if (name == $(this).html()) return;
			$('.minutes_row').parent().removeClass('lighter');
			var id = $(this).data('id');
			window.location.hash = 'minutes:' + id;
			//history.replaceState({}, document.title, window.location.protocol + '//' + window.location.host + window.location.pathname + '#minutes:' + id);
			//load_minutes(id);
		};
		$('.minutes_row').click(minutes_row_click);
		$('#minutes_add').click(function() {
			var curTime = now();
			$.post('php/doEditMinutes.php', { id : '', newname : "New Minutes", private : "", public : "" }, function(data) { // Create the new minutes in the database
				res = data.split('\n');
				if (res[0] == 'OK')
				{
					$('#minutes_table').prepend("<tr><td class=minutes_row id='minutes" + res[1] + "' data-id='" + res[1] + "'>New Minutes</td></tr>"); // Update page with new minutes
					$('.minutes_row').click(minutes_row_click);
				}
				else alert("Error:  " + res[0]);
			});
		});
		if (typeof loadid != 'undefined') load_minutes(loadid);
		else $("#minutes_main").html("Select a meeting to the left.");
	});
}

function loadSong(songid, canEdit)
{
	$.post('php/getSong.php', { id : songid }, function(data)
	{
		smoothScrollTo('repertoire_main');
		$('#repertoire_main').html(data);
		if (canEdit)
		{
			var edit = 0;
			$('#repertoire_main').prepend("<div class=pull-right style=\"padding-left: 10px; padding-right: 10px\"><img id=\"spinner\" src=\"/images/loading.gif\" style=\"width: 28px; height:28px; margin-right: 10px; display:none;\"><button class=btn id=repertoire_edit>Edit</button></div>");
			$('#repertoire_edit').click(function() {
				if (edit == 0)
				{
					edit = 1;
					$('#repertoire_edit').html("Done");
					$('#repertoire_header').html("<a id=\"edit_song\"><i class=\"icon-pencil\"></i></a> <a id=\"delete_song\"><i class=\"icon-remove\"></i></a> <a id=\"current_song\">" + ($('#repertoire_header').data('current') == '0' ? "Add to this semester" : "Remove from this semester") + "</a>");
					$('.rep_actions').html("<a class=\"rep_add\" style=\"margin-left: 10px;\"><i class=\"icon-plus\"></i></button>");
					var key_dropdown = "<select class=\"keyselect\" style=\"width: 60px\">";
					var keyarr = $('#song_key').data('vals').split(',');
					for (var i = 0; i < keyarr.length; i++) key_dropdown += "<option value=\"" + keyarr[i] + "\">" + keyarr[i] + "</option>";
					key_dropdown += "</select>";
					var song_key = $('#song_key').html();
					$('#song_key').html(key_dropdown);
					$('#song_key').find('option[value="' + song_key + '"]').prop('selected', 'true');
					var song_pitch = $('#song_pitch').html();
					$('#song_pitch').html(key_dropdown);
					$('#song_pitch').find('option[value="' + song_pitch + '"]').prop('selected', 'true');
					$('#song_key').change(function() {
						$('#spinner').css('display', 'inline');
						$.post('php/doEditSong.php', { id : songid, action : "key", note : $('#song_key').find('option:selected').prop('value') }, function(data) {
							$('#spinner').css('display', 'none');
							if (data != "OK") alert(data);
						});
					});
					$('#song_pitch').change(function() {
						$('#spinner').css('display', 'inline');
						$.post('php/doEditSong.php', { id : songid, action : "pitch", note : $('#song_pitch').find('option:selected').prop('value') }, function(data) {
							$('#spinner').css('display', 'none');
							if (data != "OK") alert(data);
						});
					});
					$('#current_song').click(function() {
						$('#repertoire_header').data('current', $('#repertoire_header').data('current') == '0' ? '1' : '0');
						$.post('php/doEditSong.php', { action : 'current', id : songid, current : $('#repertoire_header').data('current') }, function(data) {
							if (data != "OK")
							{
								alert("Error:  " + data);
								return;
							}
						});
						if ($('#repertoire_header').data('current') == 0) $('#current_song').html("Add to this semester");
						else $('#current_song').html("Remove from this semester");
					});
					$('.rep_add').click(function() {
						var section = $(this).parent().attr('id').replace("actions_", "");
						$('#spinner').css('display', 'inline');
						$.post('php/doEditLink.php', { action : "new", type : section, song : songid }, function(data) {
							$('#spinner').css('display', 'none');
							if (! /^OK /.test(data)) alert("Error:  " + data.message);
							else
							{
								data = data.substr(3);
								$('#block_' + section).append("<div id=\"file_" + data + "\"><span class=\"link_actions\"><a class=\"rep_remove\"><i class=\"icon-remove\"></i></a> <a class=\"rep_rename\"><i class=\"icon-pencil\"></i></a></span> <span class=\"link_main\"><a name=\"null\" href=\"#\" target=\"_blank\"></a></span></div>");
								$('#file_' + data + ' .rep_rename').click(function() { link_edit($(this).parent().parent().attr('id').replace("file_", "")); });
								$('#file_' + data + ' .rep_remove').click(function() { rep_remove($(this).parent().parent().attr('id').replace('file_', '')); });
								link_edit(data, true);
							}
						});
					});
					$('.link_actions').html("<a class=\"rep_remove\"><i class=\"icon-remove\"></i></a> <a class=\"rep_rename\"><i class=\"icon-pencil\"></i></a>");
					function rep_remove(linkid)
					{
						$('#spinner').css('display', 'inline');
						var storage = $('#file_' + linkid).parent().find('.data-storage').html();
						if (storage == 'remote')
						{
							$.post('php/doEditLink.php', { id : linkid, action : "delete" }, function(data) {
								$('#spinner').css('display', 'none');
								if (data == "OK") $('#file_' + linkid).remove();
								else alert("Error:  " + data.message);
							});
							return;
						}
						$.post('php/doEditLink.php', { id : linkid, action : "delete" }, function(data) {
							$('#spinner').css('display', 'none');
							if (data == "OK") $('#file_' + linkid).remove();
							else alert("Error:  " + data.message);
						});
					}
					$('.rep_rename').click(function() { link_edit($(this).parent().parent().attr('id').replace("file_", ""), false); });
					function link_edit(id, isnew)
					{
						$('.link_actions').css('display', 'none');
						$('.rep_actions').css('display', 'none');
						var storage = $('#file_' + id).parent().data('storage');
						var typeid = $('#file_' + id).parent().data('typeid');
						var link_main = $('#file_' + id).find('.link_main');
						var oldtarget = link_main.find('a').prop('href');
						var oldname = link_main.find('a').html();
						var empty = link_main.find('a').prop('name') == 'null';
						var nofile = "<span style=\"color: #888\">(No file specified)</span>";
						var uploadfile = "<input type=\"file\" id=\"file_upload\">";
						var delfile = "<button type=\"button\" id=\"file_remove\" class=\"btn\"><i class=\"icon-remove\"></i></button>";
						function perform_upload()
						{
							$('#spinner').css('display', 'inline');
							var form = new FormData();
							form.append('action', 'upload');
							form.append('id', id);
							form.append('file', document.getElementById('file_upload').files[0]);
							$.ajax({ url : 'php/doEditLink.php', type : 'POST', data : form, contentType : false, processData : false, success : function(data) {
								$('#spinner').css('display', 'none');
								if (/^OK /.test(data))
								{
									data = data.substr(3);
									empty = false;
									$('#static_target').html(data);
									$('#file_upload').replaceWith(delfile);
									$('#file_remove').click(perform_remove);
									link_main.find('a').prop('name', '');
								}
								else alert("Error:  " + data.message);
							}, error : function(data) { $('#spinner').css('display', 'none'); alert("Error:  " + data); } });
						}
						function perform_remove()
						{
							$('#spinner').css('display', 'inline');
							$.post('php/doEditLink.php', { action : "rmfile", id : id }, function(data) {
								$('#spinner').css('display', 'none');
								if (data == 'OK')
								{
									$('#file_remove').replaceWith(uploadfile);
									$('#static_target').html(nofile);
									$('#file_upload').change(perform_upload);
									empty = true;
								}
								else alert("Error:  " + data.message);
							});
						}
						link_main.html("<form method=\"post\" action=\"php/doEditLink.php\" id=\"link_form\" style=\"display: none; background: #DDD; border: 4px solid #AAA; border-radius: 4px; padding: 10px; margin: 4px 0px;\">Name:  <input type=\"text\" name=\"link_name\" id=\"link_name\"><br>Target:  </form>");
						$('#link_name').prop('value', oldname);
						if (storage == 'local')
						{
							$('#link_form').append("<span id=\"static_target\" style=\"padding-right: 10px;\">" + (empty ? nofile : oldtarget) + "</span>" + (empty ? uploadfile : delfile));
							$('#static_target').prop('value', oldtarget);
							if (empty) $('#file_upload').change(perform_upload);
						}
						else if (storage == 'remote')
						{
							if (typeid == 'video')
							{
								$('#link_form').append("<b>https://www.youtube.com/watch?v=</b>");
								oldtarget = oldtarget.replace(/^https:\/\/www.youtube.com\/watch\?v=/, '');
							}
							$('#link_form').append("<input type=text name=\"link_target\" id=\"link_target\">");
							$('#link_target').prop('value', oldtarget);
							if (empty) $('#link_target').prop('value', '');
						}
						$('#link_form').append("<br><button type=\"button\" class=\"btn\" id=\"link_edit_cancel\" style=\"margin-right: 10px;\">Cancel</button><button type=\"submit\" class=\"btn btn-default\" id=\"link_edit_done\">Done</button>");
						$('#link_form').slideDown(400);
						$('#file_remove').click(perform_remove);
						$('#link_form').submit(function() {
							$('#spinner').css('display', 'inline');
							var newname = $('#link_name').prop('value');
							var newtarget = '';
							if (newname == '') { alert("Name field cannot be empty."); return false; }
							link_main.find('a').prop('name') == '';
							if (storage == 'remote')
							{
								newtarget = $('#link_target').prop('value');
								if (newtarget == '') { alert("Target field cannot be empty."); return false; }
							}
							else if (storage == 'local')
							{
								if (empty) { alert("Target field cannot be empty."); return false; }
								newtarget = 'https://mensgleeclub.gatech.edu' + $('#static_target').html();
							}
							$.post('php/doEditLink.php', { id : id, action : "update", name : newname, target : newtarget }, function(data) {
								$('#spinner').css('display', 'none');
								if (data == 'OK')
								{
									link_main.html("<a href=\"" + (typeid == 'video' ? "https://www.youtube.com/watch?v=" : '') + newtarget + "\" target=\"_blank\">" + newname + "</a>");
									$('.link_actions').css('display', 'inline');
									$('.rep_actions').css('display', 'inline');
								}
								else alert("Error:  " + data.message);
							});
							return false;
						});
						$('#link_edit_cancel').click(function() {
							if (isnew || empty) rep_remove(id);
							else link_main.html("<a href=\"" + oldtarget + "\" target=\"_blank\">" + oldname + "</a>");
							$('.link_actions').css('display', 'inline');
							$('.rep_actions').css('display', 'inline');
						});
					}
					$('#edit_song').click(function() {
						// Song edit dialog:  name (text) and description (text)
						$.post('php/songInfo.php', { id : songid, item : "name" }, function(data) { $('#song_edit_name').attr('value', data); });
						$.post('php/songInfo.php', { id : songid, item : "desc" }, function(data) { $('#song_edit_desc').attr('value', data); });
						$('#edit_song_accept').unbind('click');
						$('#edit_song_accept').click(function() {
							$('#spinner').css('display', 'inline');
							$.post('php/doEditSong.php', { id : songid, action : "update", name : $('#song_edit_name').attr('value'), desc : $('#song_edit_desc').attr('value') }, function(data) {
								$('#spinner').css('display', 'none');
								if (data == "OK")
								{
									// Update information in main div
									$('#row_' + songid).html($('#song_edit_name').attr('value'));
									$('#song_title').html($('#song_edit_name').attr('value'));
									$('#song_desc').html($('#song_edit_desc').attr('value'));
								}
								else alert("Error:  " + data);
							});
							$('#song_editor').modal('hide');
						});
						$('#song_editor').modal('show');
					});
					$('#delete_song_deny').click(function() { $('#confirm_delete_song').modal('hide'); });
					$('#delete_song').click(function() {
						// Confirm song deletion
						$('#delete_song_confirm').unbind('click');
						$('#delete_song_confirm').click(function() {
							$.post('php/doEditSong.php', { id : songid, action : "delete" }, function(data) {
								if (data == "OK")
								{
									// Update information in main div
									$('#row_' + songid).remove();
									$('#repertoire_main').html("Select a song to the left.");
								}
								else alert("Error:  " + data);
							});
							$('#confirm_delete_song').modal('hide');
						});
						$('#confirm_delete_song').modal('show');
					});
					$('.rep_remove').click(function() { rep_remove($(this).parent().parent().attr('id').replace('file_', '')); });
				}
				else
				{
					edit = 0;
					$('#repertoire_edit').html("Edit");
					$('#repertoire_header').html("");
					$('.rep_actions').html("");
					$('.link_actions').html("");
					$('#song_key').html($('#song_key').find('option:selected').prop('value'));
					$('#song_pitch').html($('#song_pitch').find('option:selected').prop('value'));
				}
			});
		}
	});
}

function showRepertoire(firstid)
{
	$.post('php/repertoireList.php', function(data) {
		$('#main').html(data);
		var name = "";
		var canEdit = false;
		$.ajax({ url : 'php/hasPermission.php', data : { permission : "edit-repertoire" }, async : false, success : function(data) {
			if (data == "1") canEdit = true;
			else canEdit = false;
			if (canEdit)
			{
				$('#repertoire_add').click(function() {
					// Add song
					$('#song_edit_name').attr('value', "");
					$('#song_edit_desc').attr('value', "");
					$('#edit_song_accept').click(function() {
						$.post('php/doEditSong.php', { action : "add", name : $('#song_edit_name').attr('value'), desc : $('#song_edit_desc').attr('value') }, function(data) {
							if (data == parseInt(data, 10))
							{
								$('#repertoire_table').prepend("<tr><td class=\"repertoire_row\" id=\"row_" + data + "\">" + $('#song_edit_name').attr('value') + "</td></tr>");
								$('.repertoire_row').click(repertoire_row_click);
							}
							else alert("Error:  " + data);
						});
						$('#song_editor').modal('hide');
					});
					$('#edit_song_cancel').click(function() { $('#song_editor').modal('hide'); });
					$('#song_editor').modal('show');
				});
			}
			function repertoire_row_click() {
				if (name == $(this).html()) return;
				name = $(this).html();
				var songid = $(this).attr('id').replace("row_", "");
				$('.repertoire_row').parent().removeClass('lighter');
				$(this).parent().addClass('lighter');
				loadSong(songid, canEdit);
			}
			$('.repertoire_row').click(repertoire_row_click);
			if (typeof firstid != 'undefined') loadSong(firstid, canEdit);
		}});
	});
}

function pad(n, width, z)
{
	z = z || '0';
	n = n + '';
	return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

function now()
{
	var date = new Date();
	var ret = "" + date.getFullYear() + "-" + pad((date.getMonth() + 1), 2) + "-" + pad((date.getDay() + 1), 2) + " " + pad(date.getHours(), 2) + ":" + pad(date.getMinutes(), 2) + ":" + pad(date.getSeconds(), 2);
	return ret;
}

/***************************************************************************
********************* End Matthew's functions ******************************
****************************************************************************/

function timeMachine(semester, item)
{
	$.post('php/timeMachine/main.php', { semester : semester, item : item }, function(data) {
		$("#main").html(data);
	});
}

function gigreqs()
{
	$.post("php/gigreqs.php", function(data) {
		$("#main").html(data);
		$(".event-create").on("click", function() {
			var id = $(this).parent().data("id");
			editEvent(-id, "#main", function(eventNo) {
				$.post("php/gigreqs.php", { action : "accept", id : id, event: eventNo }, function(data) {
					if (data != "OK") alert(data);
				});
				window.location.hash = "event:" + eventNo;
			}, null, [{ name : "Go<br>Back", onclick : "gigreqs()" }]);
		});
		$(".event-dismiss").on("click", function() {
			var id = $(this).parent().data("id");
			$.post("php/gigreqs.php", { action : "dismiss", id : id }, function(data) {
				if (data != "OK") alert(data);
				else gigreqs(); // This is so lazy but I'm tired of writing the same JavaScript over and over
			});
		});
		$(".event-restore").on("click", function() {
			var id = $(this).parent().data("id");
			$.post("php/gigreqs.php", { action : "restore", id : id }, function(data) {
				if (data != "OK") alert(data);
				else gigreqs();
			});
		});
	});
}
