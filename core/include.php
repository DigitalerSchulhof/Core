<?php
namespace Core;
use UI;

class Einbinden {
  /**
  * Bindet die zu $url passende Seite ein
  * @param array url Die URL der zu ladenenden Seite
  */
  static function seiteEinbinden($url) {
  	global $DSH_MODULE;

		Einbinden::seiteFinden();
  }

  /** @var array Daten über das aktuelle Modul */
  static $aktuellesModul = null;

  /**
  * Bestimmt und lädt das aktulle Modul, gibt die neue URL zurück
  * @param string $url Die URL
  * @return string[]
  */
  static function seiteBestimmen($urlganz) {
  	global $DSH_MODULE, $DSH_LINKMUSTER;
  	$seiten = unserialize(file_get_contents(__DIR__."/seitenliste.core"));
  	$gefunden = false;
  	foreach($seiten as $seite => $datei) {
  		if(substr($seite, 0, 1) == "/") {
  			// RegEx
  			if(preg_match(str_replace("{linkmuster}", $DSH_LINKMUSTER, $seite), $urlganz) === 1) {
  				$gefunden = $datei;
  				break;
  			}
  		} else {
  			// URL
  			if($urlganz == $seite) {
  				$gefunden = $datei;
  				break;
  			}
  		}
  	}

  	if($gefunden === false) {
  		$urlganz = "Fehler/404";
  		$gefunden = "Kern/seiten/fehler/404.php";
  	}
  	$modul = substr($gefunden, 0, strpos($gefunden, "/"));

  	if($modul != "Kern" && Einbinden::modulLaden($modul) === false) {
  		$urlganz = "Fehler/404";
  		$gefunden = "Kern/seiten/fehler/404.php";
  	}
  	Einbinden::$aktuellesModul["gefunden"] = $gefunden;
  	Einbinden::$aktuellesModul["modul"] = $modul;

    return array("url" => explode("/", $urlganz), "urlganz" => $urlganz);
  }

  /**
  * Bindet die passende PHP-Datei zu einer Seite ein
  * @param bool $return Soll Pfad als Rückgabewert behandelt werden
  * @return bool|string Bei $return = true den Pfad, sonst die Rückgabe von include_once
  */
  static function seiteFinden($return = false) {
  	global $DSH_MODULE, $aktuellesModul, $DSH_TITEL, $CODE, $DSH_BENUTZER, $DSH_URL, $DSH_URLGANZ;
  	if($return) {
  		return Einbinden::$aktuellesModul["gefunden"];
  	}
    if (is_file("$DSH_MODULE/".Einbinden::$aktuellesModul['gefunden'])) {
  	  return include_once "$DSH_MODULE/".Einbinden::$aktuellesModul['gefunden'];
    } else {
      // Gibt den Eindrück, als würde die Seite gesucht werden :)
      sleep(1);
      echo UI\Zeile::standard(new UI\Meldung("Datei fehlt", "Die einzubindende Datei wurde nicht gefunden. Bitte den Administrator informieren!", "Fehler"));
    }
    return false;
  }

  /** @var array Geladene Module */
  static $geladeneModule = [];

  /**
  * Lädt das Modul und dessen Abhängigkeiten
  *	@param string $modul Das zu ladende Modul
  * @param bool $laden Ob die geladen-Funktion des Moduls ausgeführt werden soll
  * @param bool $configrueck Ob die Modulkonfiguration zurückgegeben werden soll
  * @return bool|array false wenn Modul nicht gefunden, sonst Modulkonfiguration
  */
  static function modulLaden($modul, $laden = true, $configrueck = true) {
  	global $DSH_MODULE, $DSH_DATENBANKEN, $MODUL, $EINSTELLUNGEN;
  	if(!file_exists("$DSH_MODULE/$modul/modul.core")) {
  		// Modul gibt's nicht
  		return false;
  	}

  	$config = unserialize(file_get_contents("$DSH_MODULE/$modul/modul.core"));

  	// Nicht sich selbst laden
  	Einbinden::$geladeneModule[] = $modul;

  	foreach($config["benötigt"] as $b) {
  		if(!in_array($b, Einbinden::$geladeneModule)) {
  			Einbinden::$geladeneModule[] = $b;		// Vor modulLaden, um Endlosschleife zu verhindern!
  			if(Einbinden::modulLaden($b, true, false) === false) {
  				return false;
  			}
  		}
  	}

  	foreach($config["erweitert"] as $b) {
  		if(!in_array($b, Einbinden::$geladeneModule)) {
  			Einbinden::$geladeneModule[] = $b;		// Vor modulLaden, um Endlosschleife zu verhindern!
  			Einbinden::modulLaden($b, true, false);
  		}
  	}

    $MODUL      = "$DSH_MODULE/$modul";
  	if($laden) {
  		$KLASSEN    = "$DSH_MODULE/$modul/klassen";
  		$geladen    = "$DSH_MODULE/$modul/funktionen/geladen.php";
  		$check      = "$DSH_MODULE/$modul/funktionen/check.php";
      if(file_exists($geladen)) {
  			include_once $geladen;
  		}
      if(file_exists($check)) {
  			include_once $check;
  		}
  	}

  	// Nötige Datenbankverbindungen bestimmen
  	$DSH_DATENBANKEN = array_merge($DSH_DATENBANKEN, $config["datenbanken"]);

  	if(!$configrueck) {
  		return true;
  	}

  	return $config;
  }
}
?>
