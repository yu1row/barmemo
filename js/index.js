// global values
var cells;
var stage = 0;

$(document).ready(function() {
	cells = $("#contents span");
	initBoard();
	$("#contents #cells li").click(cellClicked);
	$("#contents #cells2 li").click(cellClicked);
	$("#shuffle_button").click(shuffle);
	$("#prev_button").click(prevstage);
	$(window).unload(function(){});
})

// initialize board
function initBoard() {
	$.ajaxSetup({async: false});

	var isShowVip = false;

	$.getJSON("./exec.php", { 'q': 'stage', 'd': new Date().getTime() } , function(json) {
		stage = json.Stage;
		$("p#title").html(json.Name);
	});

	$.getJSON("./exec.php", { 'q': 'names', 'd': new Date().getTime() }, function(json) {
		for (i=0; i<9; i++) {
			var cell = cells.filter("#cell"+i);
			cell.html("["+json[i].Name+"]");
		}
		if (stage<json[i].Entry) {
			$("ul#cells2").hide();
		} else {
			$("ul#cells2").show();
			for (i=0; i<3; i++) {
				var cell = cells.filter("#cell"+(i+9));
				cell.html("["+json[i+9].Name+"]");
			}
			isShowVip = true;
		}
	});

	$.getJSON("./exec.php", { 'q': 'stages', 'd': new Date().getTime() }, function(json) {
		if (typeof json[1] === "undefined") {
			$("ul#shuffle_buttons").hide();
		} else {
			$("ul#shuffle_buttons").show();
		}
	});

	var cids = new Array('①','②','③','④','⑤','⑥','⑦','⑧','⑨');
	for (var i=0; i<(isShowVip ? 12 : 9); i++) {
		$.getJSON("./exec.php", { 'q': 'summary', 'stage': stage, 'cid': i, 'd': new Date().getTime() }, function(json) {
			var p  = json.Max;
			var id = Number(json.ID);
			var pt = "&nbsp;&nbsp;最高点: " + p;
			if (0<p) {
				pt += '(' + cids[id] + ')';
			}
			var flg  = "大満足: " + (json.Flag == 0 ? "×" : "○");
			var cell = cells.filter("#cell"+i);
			cell.append("<br />");
			cell.append(pt);
			cell.append(", ");
			cell.append(flg);
		});
	}
	$.ajaxSetup({async: true});
}

// cell clicked event
function cellClicked() {
	var cell = this;
	var index = Number($(cell).children("div").children("span").attr("id").replace('cell', ''));
	document.location = 'desc.php?stage='+stage+'&cid='+index;
}

function shuffle() {
	if (confirm("[!注意!]\nこのボタンはシャッフルされた場合に押します\n"
			+ "今までの入力はリセットされます\n"
			+ "本当にシャッフルをしますか？")
	) {
		$.ajaxSetup({async: false});

		$.getJSON("./exec.php", { 'q': 'nextstage', 'd': new Date().getTime() } , function(json) {
			if (json.Result == 1) {
				initBoard();
			} else {
				alert ('これ以上のシャッフルはありません');
			}
		});
		
		$.ajaxSetup({async: true});
	}
}

function prevstage() {
	if (confirm("[!注意!]\nこのボタンは間違ってシャッフルした場合に押します\n"
			+ "シャッフル前の状態に戻ります\n"
			+ "本当に戻しますか？")
	) {
		$.ajaxSetup({async: false});

		$.getJSON("./exec.php", { 'q': 'prevstage', 'd': new Date().getTime() } , function(json) {
			if (json.Result == 1) {
				initBoard();
			} else {
				alert ('これ以上戻せません');
			}
		});
		
		$.ajaxSetup({async: true});
	}
}
