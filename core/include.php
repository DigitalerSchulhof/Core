<?php
namespace Core;

$gefunden = false;
if(count($DSH_URL) >= 2 && $DSH_URL[0] == "Schulhof" && $DSH_URL[1] == "Verwaltung") {
	if(count($DSH_URL) == 2) {
		// Verwaltungsbereich
		$gefunden = true;
		include __DIR__."/seiten/verwaltungsbereich.php";
	} else {
		$gefunden = modulLaden($DSH_URL[2]);
		if($gefunden !== false) {
			$seitenliste = $gefunden["seiten"];
			$seiten = \unserialize(\file_get_contents("$DSH_MODULE/{$DSH_URL[2]}/$seitenliste"));
			seiteFinden($seiten);
			$gefunden = true;
		}
	}
}

if(!$gefunden) {
	$seiten = \unserialize(\file_get_contents(__DIR__."/seitenliste"));
	seiteFinden($seiten);
}

/**
* Bindet die passende PHP-Datei zu einer Seite ein
* @param array $seiten Array an Seiten
* @param bool $return Soll Pfad als Rückgabewert behandelt werden
* @return bool|string Bei $return = true den Pfad, sonst die Rückgabe von include_once
*/
function seiteFinden($seiten, $return = false) {
	global $DSH_URL, $DSH_URLGANZ;
	$gefunden = false;
	foreach($seiten as $seite => $datei) {
		if(\substr($seite, 0, 1) == "/") {
			// RegEx
			if(\preg_match(\str_replace("{linkmuster}", $DSH_LINKMUSTER, $seite), $DSH_URLGANZ) === 1) {
				$gefunden = $datei;
				break;
			}
		} else {
			// URL
			if($DSH_URLGANZ == $seite) {
				$gefunden = $datei;
				break;
			}
		}
	}
	if($gefunden === false) {
		$DSH_URLGANZ = "Fehler/404";
		$DSH_URL = \explode("/", $DSH_URLGANZ);
		$gefunden = "../core/seiten/fehler/404.php";
	}
	$modul = \substr($gefunden, 0, \strpos($gefunden, "/"));

	if(substr($gefunden, 0, 8) != "../core/" && modulLaden($modul) === false) {
		$DSH_URLGANZ = "Fehler/404";
		$DSH_URL = \explode("/", $DSH_URLGANZ);
		$gefunden = "../core/seiten/fehler/404.php";
	}
	if($return) {
		return $gefunden;
	}
	return include_once "$DSH_MODULE/$gefunden";
}

/** @var array Geladene Module */
$geladeneModule = array();

/**
* Lädt das Modul und dessen Abhängigkeiten
*	@param string $modul Das zu ladende Modul
* @param bool $configrueck Ob die Modulkonfiguration zurückgegeben werden soll
* @return bool|array false wenn Modul nicht gefunden, sonst Modulkonfiguration
*/
function modulLaden($modul, $configrueck = true) {
	global $geladeneModule;
	if(!file_exists("$DSH_MODULE/$modul/modul.yml")) {
		// Modul gibt's nicht
		return false;
	}
	$config = \unserialize("$DSH_MODULE/$modul/modul.yml");
	$benötigt = $config["benötigt"];
	foreach($benötigt as $b) {
		if(!\in_array($b, $geladeneModule)) {
			if(modulLaden($b, ) === false) {
				return false;
			}
			$geladeneModule[] = $b;
		}
	}

	if(	$config["name"] 				?? "" 	== "" 		||
			$config["beschreibung"] ?? "" 	== "" 		||
			$config["lehrernetz"] 	?? null == null 	||
			$config["version"] 			?? null == null
		) {
		return false;
	}

	$geladen = "$DSH_MODULE/$modul/".( $config["geladen"] ?? "funktionen/geladen.php" );
	if(\file_exists($geladen)) {
		include $geladen;
	}

	if(!$configrueck) {
		return true;
	}

	$standard = array(
		"geladen"					=> "funktionen/geladen.php",
		"ziele"						=> "funktionen/ziele",
		"rechte"					=> "funktionen/rechte",
		"seiten"					=> "seiten/seitenliste",
		"speicher"				=> "dateien/$modul",
		"einstellungen"		=> "funktionen/einstellungen",
		"datenbanken"			=> array("schulhof", "personen"),
		"benötigt"				=> array(),
		"erweitert"				=> array()
	);

	$config = array_merge($standard, $config);

	return $config;
}

?>
