<?php

function style($css) {
	$cb = substr(sha1(filemtime(__DIR__."/../$css")),0, 7);
	return "<link rel=\"stylesheet\" href=\"$css?$cb\">";
}

function js($js) {
	$cb = substr(sha1(filemtime(__DIR__."/../$js")),0, 7);
	return "<script src=\"$js?$cb\"></script>";
}

function modulJs($modul) {
	$jsdir = __DIR__."/../module/$modul/js";
	$total = 0;

  if(is_dir($jsdir)) {
    $scan = function($dir) use (&$scan, &$total) {
      foreach(array_diff(scandir($dir), array(".", "..")) as $js) {
        if(is_dir("$dir/$js")) {
          $scan("$dir/$js");
        } else if(substr($js, -3) === ".js") {
          $total += filemtime("$dir/$js");
        }
      }
    };
    $scan($jsdir);
    $cb = substr(sha1($total),0, 7);
    return "<script src=\"js/modul.php?modul=$modul&$cb\"></script>";
	}
  return "";
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
