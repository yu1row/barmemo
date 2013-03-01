<!DOCTYPE html>
<!------------------------------------------------------------------------------
 Title:
 	Customer Description
 Author:
 	yu1row
 History:
 	2012/10/17 (Created)
------------------------------------------------------------------------------->
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0" />
<head>
<title>来店者詳細</title>

<!------------------------------------------------------------------------------
 # styles
------------------------------------------------------------------------------->
<link media="only screen and (min-device-width:481px)" href="css/desc.css" type="text/css" rel="stylesheet" />
<link media="only screen and (max-device-width:480px)" href="css/idesc.css" type="text/css" rel="stylesheet" />
<link href="css/desc.css" type="text/css" rel="stylesheet" />
<link href="css/ui.css" type="text/css" rel="stylesheet" />

<!------------------------------------------------------------------------------
 # scripts
------------------------------------------------------------------------------->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script type="text/javascript">
<!--
	// global values
	var stage = <? echo $_GET['stage']; ?>;
	var cid   = <? echo $_GET['cid']; ?>;
// -->
</script>
<script type="text/javascript" src="js/desc.js"></script>

</head>
<body>

<!------------------------------------------------------------------------------
 # page body
------------------------------------------------------------------------------->
<body>

	<div id="container">
		<div id="header">
			
			<ul>
				<li><a href="./" class="buttonBack">Back</a></li>
				<li><p id="title"></p></li>
			</ul>
		</div>
		<div id="contents">
			<ul>
				<li class="flag_check">
					<div>
						<span class="field switch">
							<label class="text-left"><span>大満足：</span></label>
							<label class="cb-enable"><span>はい</span></label>
							<label class="cb-disable selected"><span>いいえ</span></label>
							<input type="checkbox" id="checkbox" class="checkbox" name="flag" />
						</span>
					</div>
				</li>
			</ul>
			<ul>
				<li class="cells"><div><span id="cell0"></span></div></li>
				<li class="cells"><div><span id="cell1"></span></div></li>
				<li class="cells"><div><span id="cell2"></span></div></li>
				<li class="cells"><div><span id="cell3"></span></div></li>
				<li class="cells"><div><span id="cell4"></span></div></li>
				<li class="cells"><div><span id="cell5"></span></div></li>
				<li class="cells"><div><span id="cell6"></span></div></li>
				<li class="cells"><div><span id="cell7"></span></div></li>
				<li class="cells"><div><span id="cell8"></span></div></li>
			</ul>
		</div>
	</div>
</body>
</html>
