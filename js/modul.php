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


	$scan = function($dir) use (&$scan) {
    $dirs = [];
    if(file_exists("$dir/objekt.js")) {
      $c = file_get_contents("$dir/objekt.js");
      foreach(explode("\n", $c) as $l) {
        if(preg_match("/^[\\s\\t]*\/\//", $l) !== 1) {
          echo $l;
        }
      }
    }
		foreach(array_diff(scandir($dir), array(".", "..", "objekt.js")) as $js) {
			if(is_dir("$dir/$js")) {
				$dirs[] = "$dir/$js";
			} else if(substr($js, -3) === ".js") {
				$c = file_get_contents("$dir/$js");
        foreach(explode("\n", $c) as $l) {
          if(preg_match("/^[\\s\\t]*\/\//", $l) !== 1) {
            echo $l;
          }
        }
			}
		}
    foreach($dirs as $dir) {
      $scan($dir);
    }
	};

  ob_start();
	$scan($jsdir);
  $js = ob_get_contents();
  ob_end_clean();
  $kurz = array(
    "\\s*=\\s*" => "=",
    "\\s*{\\s*" => "{",
    "\\s*}\\s*" => "}",
    "\\s*>\\s*" => ">",
    "\\s*<\\s*" => "<",
    ";}"        => "}",
    ",}"        => "}",
    "\\s*,"     => ",",
    "\\s\\s+"   => "",
    ";\\n"      => ";",
    "\\s*:"     => ":",
  );

  foreach($kurz as $rx => $r) {
    $js = preg_replace("/$rx/", "$r", $js);
  }
  echo $js;
}
?>