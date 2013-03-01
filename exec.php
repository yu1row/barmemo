<?php
	require_once("JSON.php");

	$GLOBALS['CUSTOMMER_COUNT'] = count(getNamesArray());
	$GLOBALS['DRINK_COUNT']     = count(getDrinksArray());
	$GLOBALS['DATA_ARRAY']      = NULL; // array[stage][cid][did]['point', 'flag']
	$GLOBALS['DATA_PERMISSION'] = 0666;
	$GLOBALS['DATA_DIR_PERMISSION'] = 0777;

	// If args is valid
	if (isset($_GET['q'])) {
		$q  = $_GET['q'];
		switch ($q) {
			case 'names':
				// args: { }
				names();
				break;
			case 'drinks':
				// args: { }
				drinks();
				break;
			case 'stage':
				// args: { }
				stage();
				break;
			case 'stages':
				// args: { }
				stages();
				break;
			case 'summary':
				// args: { stage, cid }
				summary();
				break;
			case 'detail':
				// args: { stage, cid }
				detail();
				break;
			case 'setval':
				// args: { stage, cid, did, point }
				setval();
				break;
			case 'setflag':
				// args: { stage, cid, flag }
				setflag();
				break;
			case 'nextstage':
				// args: { }
				nextstage();
				break;
			case 'prevstage':
				// args: { }
				prevstage();
				break;
			case 'debug':
				createInitialArray(0);
				$arr = getDrinksArray();
				
//				$stages = getStagesArray();
//				$arr = array('Result' => count($arr[0]));
				echoJson($arr);
				break;
		}
	}

	function nextstage() {
		$arr = array('Result' => 0);
		$stages = getStagesArray();
		if ( ($stages['Current'] + 2) < count($stages) ) {
			$stages['Current'] = $stages['Current'] + 1;
			saveStage($stages);
			$arr = array('Result' => 1);
		}
		echoJson($arr);
	}

	function prevstage() {
		$arr = array('Result' => 0);
		$stages = getStagesArray();
		if (0 < $stages['Current']) {
			$stages['Current'] = $stages['Current'] - 1;
			saveStage($stages);
			$arr = array('Result' => 1);
		}
		echoJson($arr);
	}

	function saveStage($stages) {

		$ret = false;

		// create directory
		createDir();

		// backup before save
		backupStage();

		// open file
		$fname = './stage';
		$fh = fopen($fname, 'w');
		$stageCount = count($stages)-1;
		if ($fh) {
			if (flock($fh, LOCK_EX)) {
			
				// save values
				fwrite ($fh, $stageCount.','.$stages['Current']);
				for ($i=0; $i<count($stages)-1; $i++) {
					fwrite ($fh, ','.$stages[$i]['Stage']);
					fwrite ($fh, ','.$stages[$i]['Name']);
				}
				fwrite ($fh, "\n");
				flock($fh, LOCK_UN);
				$ret = true;
			}
			fclose($fh);
		}
		return $ret;
	}

	function names() {
		echoJson(getNamesArray());
	}

	function getNamesArray() {
		// check file existance
		$fname = './names';
		if (!file_exists($fname)) {
			return;
		}

		$arr = array();
		// open file
		$fh = fopen($fname, 'r');
		if ($fh) {
			if (flock($fh, LOCK_EX)) {
				// load values
				$cid = 0;
				for ($i=0; $i<2; $i++) {
					$line = fgets($fh);
					$vals = explode(",", $line);
					$cnt  = count($vals)-1;
					for ($j=0; $j<$cnt; $j++) {
						$arr[$cid]["ID"]    = $cid;
						$arr[$cid]["Name"]  = $vals[$j+1];
						$arr[$cid]["Entry"] = $vals[0];
						$cid++;
					}
				}
				flock($fh, LOCK_UN);
			}
			fclose($fh);
		}

		return $arr;
	}

	function drinks() {
		echoJson(getDrinksArray());
	}

	function getDrinksArray() {
		// check file existance
		$fname = './drinks';
		if (!file_exists($fname)) {
			return;
		}

		$arr = array();
		// open file
		$fh = fopen($fname, 'r');
		if ($fh) {
			if (flock($fh, LOCK_EX)) {
				// load values
				$line = fgets($fh);
				$vals = explode(",", $line);
				$cnt  = count($vals);
				for ($did=0; $did<$cnt; $did++) {
					$arr[$did]["ID"]   = $did;
					$arr[$did]["Name"] = $vals[$did];
				}

				flock($fh, LOCK_UN);
			}
			fclose($fh);
		}

		return $arr;
	}

	function getStagesArray() {
		$arr = array();
		$arr = array('Current' => 0);

		// open file
		$fname = "stage";
		$fh = fopen($fname, 'r');
		if ($fh) {
			if (flock($fh, LOCK_EX)) {
				do {
					$line  = trim(fgets($fh));
					$iscmt = (strpos($line, '#') === 0);
					if (!$iscmt) {
						$vals = explode(',', $line);
						$arr['Current'] = intVal($vals[1]);
						for ($i=0; $i<$vals[0]; $i++) {
							$arr[$i]['Stage'] = intVal($vals[$i*2+2]);
							$arr[$i]['Name']  = $vals[$i*2+3];
						}
					}
				} while ($iscmt);
				flock($fh, LOCK_UN);
			}
			fclose($fh);
		}

		return $arr;
	}

	function stages() {
		echoJson(getStagesArray());
	}

	function stage() {
		$stages = getStagesArray();
		$stage = $stages['Current'];
		$name  = $stages[$stage]['Name'];
		echoJson(array ('Stage' => $stage, 'Name' => $name));
	}

	function setval() {
		if (!isset($_GET['stage']) ||
		    !isset($_GET['cid']) ||
		    !isset($_GET['did']) ||
		    !isset($_GET['point'])) {
		    return;
		}
		$stage = intval(trim($_GET['stage']));
		$cid   = intval(trim($_GET['cid']));
		$did   = intval(trim($_GET['did']));
		$point = intval(trim($_GET['point']));

		load ($stage);
		$gArr = $GLOBALS['DATA_ARRAY'];
		$gArr[$stage][$cid][$did]['point'] = $point;
		$GLOBALS['DATA_ARRAY'] = $gArr;
		save ($stage);
		$arr = array('Result' => 1);
		echoJson($arr);
	}

	function setflag() {
		if (!isset($_GET['stage']) ||
		    !isset($_GET['cid']) ||
		    !isset($_GET['flag'])) {
		    return;
		}
		$stage = intval(trim($_GET['stage']));
		$cid   = intval(trim($_GET['cid']));
		$flag  = intval(trim($_GET['flag']));

		load ($stage);

		$gArr = $GLOBALS['DATA_ARRAY'];
		$max = getMaxPoint ($stage, $cid);
		$flg = getFlag ($stage, $cid);
		if ($flag == 0) {
			if ($flg['flag'] == 0) { return; }
			for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
				$gArr[$stage][$cid][$did]['flag'] = 0;
			}
		} else {
			if ($flg['flag'] == 1) {
				if ($max['did'] == $flg['did']) { return; }
			}
			for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
				$gArr[$stage][$cid][$did]['flag'] = 0;
			}
			$gArr[$stage][$cid][$max['did']]['flag'] = 1;
		}

		$GLOBALS['DATA_ARRAY'] = $gArr;
		save ($stage);
		$arr = array('Result' => 1);
		echoJson($arr);
	}

	function getFlag($stage, $cid) {
		$val = 0;
		$num = -1;
		$arr = $GLOBALS['DATA_ARRAY'];
		for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
			$tmp = $arr[$stage][$cid][$did]['flag'];
			if ($tmp == 1) {
				$val = $tmp;
				$num = $did;
				break;
			}
		}
		return array( 'did' => $num, 'flag' => $val );
	}

	function summary() {
		if (!isset($_GET['stage']) ||
		    !isset($_GET['cid'])) {
		    return;
		}
		$stage = $_GET['stage'];
		$cid   = $_GET['cid'];

		load ($stage);
		$max = getMaxPoint ($stage, $cid);
		$flg = getFlag ($stage, $cid);
		$arr = array (
			'ID'   => $max['did'],
			'Max'  => $max['point'],
			'Flag' => $flg['flag']
		);
		echoJson($arr);
	}

	function detail() {
		if (!isset($_GET['stage']) ||
		    !isset($_GET['cid'])) {
		    return;
		}
		$stage = $_GET['stage'];
		$cid   = $_GET['cid'];

		load ($stage);
		$names  = getNamesArray();
		$drinks = getDrinksArray();
		$flg = getFlag($stage, $cid);
		$arr = array();
		$arr["Name"] = $names[$cid]["Name"];
		$arr["Flag"] = $flg['flag'];
		$arrData = $GLOBALS['DATA_ARRAY'];
		$arr["Drink"] = array();
		$n = 0;
		for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
			//if ($arrData[$stage][$cid][$did]['point'] == 0) { continue; }
			$arr["Drink"][$n]["ID"]   = $drinks[$did]['ID'];
			$arr["Drink"][$n]["Name"] = $drinks[$did]['Name'];
			$arr["Drink"][$n]["Point"]= $arrData[$stage][$cid][$did]['point'];
			$arr["Drink"][$n]["Flag"] = $arrData[$stage][$cid][$did]['flag'];
			$n++;
		}
		echoJson($arr);
	}

	function echoJson($arr) {
		$json = new Services_JSON;
		header("Content-Type: application/json; charset=utf-8");
		$encode = $json->encode($arr);
		echo $encode;
	}

	function createInitialArray($stage) {
		$arr = array();
		for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
			for ($cid=0; $cid<$GLOBALS['CUSTOMMER_COUNT']; $cid++) {
				$arr[$stage][$cid][$did]['point'] = 0;
				$arr[$stage][$cid][$did]['flag']  = 0;
			}
		}
		$GLOBALS['DATA_ARRAY'] = $arr;
	}

	function getMaxPoint($stage, $cid) {
		$val = 0;
		$num = -1;
		$arr = $GLOBALS['DATA_ARRAY'];
		for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
			$tmp = $arr[$stage][$cid][$did]['point'];
			if ($val<$tmp) {
				$val = $tmp;
				$num = $did;
			}
		}
		return array( 'did' => $num, 'point' => $val );
	}

	function save($stage) {

		$ret = false;

		// backup before save
		backup($stage);

		// initialize array
		if (is_null($GLOBALS['DATA_ARRAY'])) {
			createInitialArray($stage);
		}
		$arr = $GLOBALS['DATA_ARRAY'];

		// create directory
		createDir();

		// open file
		$fname = './data/'.$stage.'.dat';
		$fh = fopen($fname, 'w');
		if ($fh) {
			if (flock($fh, LOCK_EX)) {
			
				// save values
				for ($cid=0; $cid<$GLOBALS['CUSTOMMER_COUNT']; $cid++) {
					fwrite ($fh, $cid);
					for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
						fwrite ($fh, ",{$arr[$stage][$cid][$did]['point']}");
						fwrite ($fh, ",{$arr[$stage][$cid][$did]['flag']}");
					}
					fwrite ($fh, "\n");
				}
				flock($fh, LOCK_UN);
				$ret = true;
			}
			fclose($fh);
			chmod($fname, $GLOBALS['DATA_PERMISSION']);
		}
		return $ret;
	}

	function backup($stage) {
		$fsrc = "./data/{$stage}.dat";
		$fdst = "{$fsrc}.".date('Ymd_His');
		if (file_exists($fsrc)) {
			copy ($fsrc, $fdst);
			chmod($fdst, $GLOBALS['DATA_PERMISSION']);
		}
	}

	function backupStage() {
		$fsrc = "./stage";
		$fdst = "./data/stage.".date('Ymd_His');
		if (file_exists($fsrc)) {
			copy ($fsrc, $fdst);
			chmod($fdst, $GLOBALS['DATA_PERMISSION']);
		}
	}

	function load($stage) {
		// initialize array
		if (is_null($GLOBALS['DATA_ARRAY'])) {
			createInitialArray($stage);
		}
		$arr = $GLOBALS['DATA_ARRAY'];

		// check file existance
		$fname = './data/'.$stage.'.dat';
		if (!file_exists($fname)) {
			return;
		}

		// open file
		$fh = fopen($fname, 'r');
		if ($fh) {
			if (flock($fh, LOCK_EX)) {
			
				// load values
				for ($cid=0; $cid<$GLOBALS['CUSTOMMER_COUNT']; $cid++) {
					$line = trim(fgets($fh));
					$vals = explode(",", $line);
					for ($did=0; $did<$GLOBALS['DRINK_COUNT']; $did++) {
						$arr[$stage][$vals[0]][$did]['point'] = intVal($vals[$did*2+1]);
						$arr[$stage][$vals[0]][$did]['flag']  = intVal($vals[$did*2+2]);
					}
				}

				flock($fh, LOCK_UN);
			}
			fclose($fh);
		}
		$GLOBALS['DATA_ARRAY'] = $arr;
	}

	function createDir() {
		$dir = './data';
		if (!is_dir($dir)) {
			mkdir ($dir);
			chmod($dir, $GLOBALS['DATA_DIR_PERMISSION']);
		}
	}
?>
