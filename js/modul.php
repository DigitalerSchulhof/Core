<?php

/*
 * Alle JavaScript Dateien des per GET["modul"] übergebenen Moduls als application/javascript ausgeben
 */

$fehler = false;

$fehler 		= $fehler || !isset($_GET);
$fehler 		= $fehler || !isset($_GET["modul"]);
$fehler 		= $fehler || !preg_match("/^[A-Za-z0-9]{1,16}$/", $_GET["modul"]);
$moduldir 	= __DIR__."/../module/{$_GET["modul"]}";
$fehler 		= $fehler || !is_dir($moduldir);

if($fehler) {
	include __DIR__."/../index.php";
	die();
} else {
	header("Content-Type: application/javascript");
	$jsdir = "$moduldir/js";

  if(file_exists("$jsdir/objekt.js")) {
    echo file_get_contents("$jsdir/objekt.js")."\n";
  }

	$scan = function($dir) use (&$scan) {
		foreach(array_diff(scandir($dir), array(".", "..", "objekt.js")) as $js) {
			if(is_dir("$dir/$js")) {
				$scan("$dir/$js");
			} else if(substr($js, -3) === ".js") {
				echo file_get_contents("$dir/$js")."\n";
			}
		}
	};

	$scan($jsdir);
}
?>