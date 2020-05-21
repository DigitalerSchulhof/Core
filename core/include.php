<?php
namespace Core;

include_once(__DIR__."/../core/yaml.php");
use Async\YAML;

if($DSH_URL[0] == "Schulhof") {
	// Schulhof
	if($DSH_URL[1] == "Verwaltung") {
		if(count($DSH_URL) == 2) {
			// Verwaltungsbereich

		} else {
			modulLaden($DSH_URL[2]);
		}
	} else {
		$seiten = \unserialize(\file_get_contents(__DIR__."/../core/"));
	}
}

?>
