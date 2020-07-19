<?php
namespace Core;

/**
* Bindet die zu $url passende Seite ein
* @param array url Die URL der zu ladenenden Seite
*/
function seiteEinbinden(array $url) {
	global $DSH_MODULE;

	$gefunden = false;
	if(count($url) == 2 && $url[0] == "Schulhof" && $url[1] == "Verwaltung") {
		// Verwaltungsbereich
		$gefunden = true;
		include __DIR__."/seiten/verwaltungsbereich.php";
	}

 	if(!$gefunden) {
		seiteFinden();
	}
}

/** @var array Daten über das aktuelle Modul */
$aktuellesModul = null;

/**
* Bestimmt und lädt das aktulle Modul
*/
function aktuellesModulBestimmen() {
	global $DSH_URL, $DSH_URLGANZ, $DSH_MODULE, $aktuellesModul;
	$seiten = unserialize(file_get_contents(__DIR__."/seitenliste.core"));
	$gefunden = false;
	foreach($seiten as $seite => $datei) {
		if(substr($seite, 0, 1) == "/") {
			// RegEx
			if(preg_match(str_replace("{linkmuster}", $DSH_LINKMUSTER, $seite), $DSH_URLGANZ) === 1) {
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
		$DSH_URL = explode("/", $DSH_URLGANZ);
		$gefunden = "Kern/seiten/fehler/404.php";
	}
	$modul = substr($gefunden, 0, strpos($gefunden, "/"));

	if($modul != "Kern" && modulLaden($modul) === false) {
		$DSH_URLGANZ = "Fehler/404";
		$DSH_URL = explode("/", $DSH_URLGANZ);
		$gefunden = "Kern/seiten/fehler/404.php";
	}
	$aktuellesModul["gefunden"] = $gefunden;
	$aktuellesModul["modul"] = $modul;
}

// /**
// * Bindet die passende PHP-Datei zu einer Seite ein
// * @param bool $return Soll Pfad als Rückgabewert behandelt werden
// * @return bool|string Bei $return = true den Pfad, sonst die Rückgabe von include_once
// */
// function seiteFinden($return = false) {
// 	global $DSH_MODULE, $aktuellesModul;
// 	if($return) {
// 		return $aktuellesModul["gefunden"];
// 	}
//
// 	return include_once "$DSH_MODULE/{$aktuellesModul['gefunden']}";
// }

/** @var array Geladene Module */
$geladeneModule = array();

/**
* Lädt das Modul und dessen Abhängigkeiten
*	@param string $modul Das zu ladende Modul
* @param bool $laden Ob die geladen-Funktion des Moduls ausgeführt werden soll
* @param bool $configrueck Ob die Modulkonfiguration zurückgegeben werden soll
* @return bool|array false wenn Modul nicht gefunden, sonst Modulkonfiguration
*/
function modulLaden($modul, $laden = true, $configrueck = true) {
	global $geladeneModule, $DSH_MODULE, $DSH_DATENBANKEN, $MODUL;
	if(!file_exists("$DSH_MODULE/$modul/modul.core")) {
		// Modul gibt's nicht
		return false;
	}

	$config = unserialize(file_get_contents("$DSH_MODULE/$modul/modul.core"));

	// Nicht sich selbst laden
	$geladeneModule[] = $modul;

	foreach($config["benötigt"] as $b) {
		if(!in_array($b, $geladeneModule)) {
			$geladeneModule[] = $b;		// Vor modulLaden, um Endlosschleife zu verhindern
			if(modulLaden($b, true, false) === false) {
				return false;
			}
		}
	}

	foreach($config["erweitert"] as $b) {
		if(!in_array($b, $geladeneModule)) {
			$geladeneModule[] = $b;		// Vor modulLaden, um Endlosschleife zu verhindern
			modulLaden($b, true, false);
		}
	}

  $MODUL      = "$DSH_MODULE/$modul";
	if($laden) {
		$KLASSEN    = "$DSH_MODULE/$modul/klassen";
		$geladen    = "$DSH_MODULE/$modul/funktionen/geladen.php";
		$check      = "$DSH_MODULE/$modul/funktionen/check.php";
		$generieren = "$DSH_MODULE/$modul/funktionen/generieren.php";
    if(file_exists($geladen)) {
			include $geladen;
		}
    if(file_exists($check)) {
			include $check;
		}
    if(file_exists($generieren)) {
			include $generieren;
		}
	}

	// Nötige Datenbankverbindungen bestimmen
	$DSH_DATENBANKEN = array_merge($DSH_DATENBANKEN, $config["datenbanken"]);

	if(!$configrueck) {
		return true;
	}

	return $config;
}
?>