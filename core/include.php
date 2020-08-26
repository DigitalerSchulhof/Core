<?php
namespace Core;
use UI;
use Kern;

class Einbinden {
  /**
  * Bindet die zu $url passende Seite ein
  * @param array url Die URL der zu ladenenden Seite
  */
  static function seiteEinbinden() {
		return Einbinden::seiteFinden();
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
  	$seitenmodule = unserialize(file_get_contents(__DIR__."/seitenliste.core"));
  	$gefunden = false;
    $modul;
    foreach($seitenmodule as $modul => $seiten) {
    	foreach($seiten as $seite => $datei) {
    		if(substr($seite, 0, 1) == "/") {
    			// RegEx
    			if(preg_match(str_replace("{linkmuster}", $DSH_LINKMUSTER, $seite), $urlganz) === 1) {
    				$gefunden = $datei;
    				break 2;
    			}
    		} else {
    			// URL
    			if($urlganz == $seite) {
    				$gefunden = $datei;
    				break 2;
    			}
    		}
      }
  	}

  	if($gefunden === false) {
  		$urlganz = "Fehler/404";
  		$gefunden = "../../Kern/seiten/fehler/404.php";
  	}
    
  	if(!in_array($modul, array("Kern", "UI")) && Einbinden::modulLaden($modul, true, true, true) === false) {
  		$urlganz = "Fehler/404";
  		$gefunden = "../../Kern/seiten/fehler/404.php";
  	}

    if(substr($gefunden, 0, 1) === ">") {
      return self::seiteBestimmen(substr($gefunden, 1));
    }

  	Einbinden::$aktuellesModul["gefunden"] = $gefunden;
  	Einbinden::$aktuellesModul["modul"] = $modul;

    return array("url" => explode("/", $urlganz), "urlganz" => $urlganz);
  }

  /**
  * Bindet die passende PHP-Datei zu einer Seite ein
  * @param bool $return Soll Pfad als Rückgabewert behandelt werden
  * @return bool|string Bei $return = true den Pfad, sonst die Rückgabe von include_once
  *
  * Verfügbare Variablen:
  * $ROOT           - Hauptverzeichnis (/)
  * $DIR            - Verzeichnis, des aktuellen Moduls (/module/AKTUELLESMODUL)
  * $DSH_MODULE     - Absoluter Pfad zum Verzeichnis, in welchem die Module liegen (/module)
  * $DSH_ALLEMODULE - Assoziatives Array aller Module und deren Pfade: [Modul => Absoluter Pfad zum Modul (/module/MODUL)]
  * $DSH_BENUTZER   - Aktueller Benutzer
  * $DSH_URL        - URL
  * $DSH_URLGANZ    - Mit "/" verbundene URL
  */
  static function seiteFinden($return = false) {
  	global $DSH_MODULE, $DSH_ALLEMODULE, $aktuellesModul, $DSH_BENUTZER, $DSH_URL, $DSH_URLGANZ, $ROOT, $DIR, $EINSTELLUNGEN, $DBS;

    Kern\Check::einwilligung();
    Kern\DB::log();

    $DIR = "$DSH_MODULE/".Einbinden::$aktuellesModul["modul"];

  	if($return) {
  		return Einbinden::$aktuellesModul["gefunden"];
  	}

    if (is_file("$DIR/seiten/".Einbinden::$aktuellesModul['gefunden'])) {
  	  include_once "$DIR/seiten/".Einbinden::$aktuellesModul['gefunden'];
      return $SEITE;
    } else {
      $CODE[] = UI\Zeile::standard(new UI\Meldung("Datei fehlt", "Die einzubindende Datei wurde nicht gefunden. Bitte den Administrator informieren!", "Fehler"));
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
  * @param bool $script Ob die benötigten Scripts zurückgegeben werden sollen
  * @return bool|array false wenn Modul nicht gefunden, sonst Modulkonfiguration
  */
  static function modulLaden($modul, $laden = true, $configrueck = false, $scripts = false) {
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
  			if(Einbinden::modulLaden($b, true, false, $scripts) === false) {
  				return false;
  			}
  		}
  	}

  	foreach($config["erweitert"] as $b) {
  		if(!in_array($b, Einbinden::$geladeneModule)) {
  			Einbinden::$geladeneModule[] = $b;		// Vor modulLaden, um Endlosschleife zu verhindern!
  			Einbinden::modulLaden($b, true, false, $scripts);
  		}
  	}

    $MODUL        = "$modul";
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

    if($scripts) {
      \Anfrage::addRueck("Scripts", $modul);
    }

  	if(!$configrueck) {
  		return true;
  	}

  	return $config;
  }

  /**
   * Gibt ein Array aller Modul-Ordner zurück zurück
   * @return array
   */
  public static function alleModuleBestimmen() : array {
    global $DSH_MODULE;
    $r = array();
    foreach(array_diff(scandir($DSH_MODULE), [".", "..", ".htaccess"]) as $modul) {
      $r[$modul] = "$DSH_MODULE/$modul";
    }
    return $r;
  }
}
?>
