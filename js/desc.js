$(window).unload(function(){});

// global values
var points = new Array();
var names  = new Array();

// process for on load
$(document).ready(function() {
	cells = $("#contents span");
	updateTable();
	$(".cb-enable").click(checkEnable);
	$(".cb-disable").click(checkDisable);
	$(".cells").click(setValue);
})

function checkEnable() {
	setCheck(true);
	setFlag(true);
	updateTable();
}

function checkDisable() {
	setCheck(false);
	setFlag(false);
	updateTable();
}

function setFlag(flag) {
	var f = (flag ? 1 : 0);
	$.ajaxSetup({async: false});
	$.getJSON("./exec.php", { 'q': 'setflag', 'stage': stage, 'cid': cid, 'flag': f, 'd': new Date().getTime() }, function(json) {
	});
	$.ajaxSetup({async: true});
}

function setCheck(flag) {
	var cbx    = flag ? $('.cb-enable') : $('.cb-disable');
	var parent = cbx.parents('.switch');
	if (flag == true) {
		$('.cb-disable',parent).removeClass('selected');
		cbx.addClass('selected');
		$('.checkbox',parent).attr('checked', true);
	} else {
		$('.cb-enable',parent).removeClass('selected');
		cbx.addClass('selected');
		$('.checkbox',parent).attr('checked', false);
	}
}

function updateTable() {

	$.ajaxSetup({async: false});

	$.getJSON("./exec.php", { 'q': 'detail', 'stage': stage, 'cid': cid, 'd': new Date().getTime() }, function(json) {
		$("p#title").html(json.Name);

		// Set points
		var flag = 0;
		for (i=0; i<json.Drink.length; i++) {
			var cell = cells.filter("#cell"+i);
			var num  = json.Drink[i].ID + 1;
			var name = json.Drink[i].Name;
			var pt   = json.Drink[i].Point;
			var flg  = json.Drink[i].Flag;

			cell.html(num + ': ' + name);
			cell.append('<br />&nbsp;&nbsp;&nbsp;');
			cell.append((pt==0 ? '?' : pt) + ' point');
			names[i]  = name;
			points[i] = pt;
			
			if (flg == 1) { flag = 1; }
			cell.parent().css( 'background-color', ((flg == 1) ? 'lightgreen' : 'white' ) );
		}

		// Switch flag
		setCheck((flag==1));
	});

	$.ajaxSetup({async: true});
}

function setValue() {
	var cell = $(this);
	// index
	var index = Number($(cell).children("div").children("span").attr("id").replace('cell', ''));
	// name
	var name  = names[index];
	// point
	var curPt = points[index];
	// input point
	var newPt = window.prompt(name+" のポイントを入力してください", curPt);
	
	if (newPt == null) { return; }

	// check params
	if (!$.isNumeric(newPt)) {
		alert("ポイントには数値を入力して下さい");
		return;
	}
	// set
	$.ajaxSetup({async: false});
	var did  = index;
	$.getJSON("./exec.php?", {
			'q': 'setval',
			'stage': stage,
			'cid': cid,
			'did': did,
			'point': newPt,
			'd': new Date().getTime()
		}, function(json) {
		});
	$.ajaxSetup({async: true});
	
	// reload
	updateTable();
}
