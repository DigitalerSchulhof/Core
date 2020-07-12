<?php

function style($css) {
	$cb = substr(sha1(filemtime(__DIR__."/../$css")),0, 7);
	return "<link rel=\"stylesheet\" href=\"$css?$cb\">";
}

function js($js) {
	$cb = substr(sha1(filemtime(__DIR__."/../$js")),0, 7);
	return "<script src=\"$js?$cb\"></script>";
}

function istZahl($x) {
	if (preg_match("/^[0-9]+$/", $text)) {
		return false;
	}	else {
		return true;
	}
}

function fuehrendeNull($x) {
	if (istZahl($x)) {
		if (strlen($x) < 2) {
			return "0".$x;
		} else {
			return $x;
		}
	} else {
		return false;
	}
}

/**
 * Rekursiv alle .php einbinden
 * @param  string $dir Ordner
 */
function allesEinbinden($dir) {
	foreach(array_diff(scandir($dir), array(".", "..")) as $f) {
		$f = "$dir/$f";
		if(is_dir($f)) {
			allesEinbinden($f);
		} else {
			if(substr($f, -4) === ".php") {
				include_once($f);
			}
		}
	}
}
?>
