<?php

function style($css) {
	$cb = substr(sha1(filemtime(__DIR__."/../$css")),0, 7);
	return "<link rel=\"stylesheet\" href=\"$css?$cb\">";
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

?>
