<?php

/*
* Verfügbare Variablen:
* $ROOT           - Hauptverzeichnis (/)
* $DIR            - Verzeichnis, des aktuellen Moduls (/module/AKTUELLESMODUL)
* $DSH_MODULE     - Absoluter Pfad zum Verzeichnis, in welchem die Module liegen (/module)
* $DSH_ALLEMODULE - Assoziatives Array aller Module und deren Pfade: [Modul => Absoluter Pfad zum Modul (/module/MODUL)]
*                   <code>array_keys($DSH_ALLEMODULE)</code> für nur die Module nutzen
* $DSH_BENUTZER   - Aktueller Benutzer
 */

include_once(__DIR__."/yaml.php");
use Async\YAML;

/*
 * Eine Anfrage gibt (außer im Falle eines Fehlers lol) immer JSON-Code zurück. Kommt ungültiges JSON zurück, wird, sofern dies nicht explizit verlangt wird, eine Fehlermeldung direkt an DSH übermittelt.
 *
 * JSON-Rückgaben enthalten das Feld {Erfolg: }, welches angibt, ob die Anfrage erfolgreich gewesen ist, oder nicht.
 * Ist die Anfrage nicht erfolgreich gewesen ({Erfolg: false}), ist das Feld {Fehler: } enthalten, welches ein Array an Fehlercodes und deren zugehörigen Modul entfält. Beispiel: {Erfolg: false, Fehler: [["Core", -3], ["Kern", 4], ["Kern", 6]]}
 * Ist die Anfrage erfolgreich gewesen ({Erfolg: true}), sind, je nach Anfrage, weitere Rückgabefelder gegeben, welche Frontend entsprechend erwartet und ausgewertet werden. Fehlt eines dieser Felder, eine Fehlermeldung direkt an DSH übermittelt.
 * @TODO: Übermitteln des Fehlers an DSH
 */

/**
 * Klasse mit statischen Funtionen, die eine Anfrage benötigt
 *
 * Die Klasse hält für die gesamte Anfrage den Typ der Rückgabe als String und Rückgabewerte als Array.
 */
class Anfrage {
  private function __construct() {}

  /** @var array Liste an Fehler-IDs und deren Modul, die aufgetreten sind
   * [[Modul : string, Fehlerid : int], [Modul : string, Fehlerid : int], [Modul : string, Fehlerid : int]]
   */
  private static $FEHLER = [];

  /** @var bool Ob die Anfrage erfolgreich ist */
  private static $ERFOLG = true;

  /** @var [Rückgabefeld : string] => [Rückgabewert : mixed] Rückgabefelder mit deren Inhalt */
  private static $RUECK = [];

  /**
   * Setzt, ob die Anfrage erfolgreich ist
   * @param bool $typ :)
   */
  public static function setErfolg($erfolg) {
    self::$ERFOLG = $erfolg;
  }

  /**
   * Gibt zurück, ob die Anfrage erfolgreich ist
   * @return bool
   */
  public static function getErfolg() : bool {
    return self::$ERFOLG;
  }

  /**
   * Setzt den Wert eines Rückgabefeldes
   * @param string $feld :)
   * @param mixed $wert :)
   */
  public static function setRueck($feld, $wert) {
    self::$RUECK[$feld] = $wert;
  }

  /**
   * Hängt einen Rückgabewert an ein Rückgabefeld an, welches dann - obviously - ein Array ist
   * @param string $feld  :)
   * @param mixed ...$werte :)
   */
  public static function addRueck($feld, ...$werte) {
    if(!isset(self::$RUECK[$feld])) {
      self::$RUECK[$feld] = [];
    }
    self::$RUECK[$feld] = array_merge(self::$RUECK[$feld], $werte);
  }

  /**
   * Gibt den Wert eines Rückgabefeldes zurück. <code>null</code> wenn es nicht gesetzt ist.
   * @param string $feld :)
   * @return mixed
   */
  public static function getRueck($feld) {
    return self::$RUECK[$feld] ?? null;
  }

  /**
   * Leert die Rückgabeparameter
   * @return array Gesetzte Rückgabeparameter
   */
  public static function leereRueck() : array {
    $r = self::$RUECK;
    self::$RUECK = [];
    return $r;
  }

  /**
   * Fehlercode zur Liste hinzufügen
   * @param int $fehler Fehlercode, wenn < 1, wird Modul, sofern nicht explizit mit <code>$modul</code> übergeben, auf "Core" gesetzt
   *  Fehlercodes von Core:
   *  `0`: Es fehlen Informationen, um die Anfrage zu verarbeiten.
   *  `1`: Die Fehlerdatei des Moduls »Core« konnte nicht gefunden werden.
   *  `2`: Aktuell ist kein Benutzer angemeldet.
   *  `3`: Du kannst hier schon 'nen anderen Wert eingeben, aber dann ist es halt kacke...
   *  `4`: Für diese Aktion besteht keine Berechtigung!
   * @param string $modul
   * Wenn <code>null</code>: Das aktuelle Modul
   * Wenn <code>true</code>: Der Wert von $die und $modul = null
   * Sonst: das Modul des Fehlers
   * @param bool $die Check, ob Fehler vorliegen und ggf. Abbruch
   *
   * Reservierte Fehlercodes:
   * 0: Anfragewert nicht übergeben
   */
  public static function addFehler($fehler, $modul = null, $die = false) {
    global $MODUL;
    if($modul === true) {
      $die = $modul;
      $modul = null;
    }
    if($fehler < 1) {
      $modul = $modul ?? "Core";
    }
    $fehler = [$modul ?? $MODUL, $fehler];
    $d = false;
    foreach(self::$FEHLER as $f) {
      if($f == $fehler) {
        $d = true;
      }
    }
    if(!$d) {
      self::$FEHLER[] = $fehler;
    }
    if($die) {
      Anfrage::checkFehler();
    }
  }

  /**
   * Prüft ob Fehler vorliegen und gibt diese zusammen mit {Erfolg: false} aus. Bricht das Skript ab, wenn Fehler vorhanden sind.
   */
  public static function checkFehler() {
    if(count(self::$FEHLER) > 0) {
      Anfrage::leereRueck();
      Anfrage::setErfolg(false);
      Anfrage::setRueck("Fehler", self::$FEHLER);
      Anfrage::ausgeben();
    }
  }

  /**
   * Gibt das Resultat der Anfrage aus und beendet das Skript.
   */
  public static function ausgeben() {
    echo json_encode(array_merge(["Erfolg" => self::$ERFOLG], self::$RUECK));
    die();
  }

  // Prüft die üblichen Sortiervariablen und gibt einen Fehler aus, falls sie falsch oder nicht gesetzt sind
  public static function postSort() {
    Anfrage::post("sortSeite", "sortDatenproseite", "sortRichtung", "sortSpalte");
    global $sortSeite, $sortDatenproseite, $sortRichtung, $sortSpalte;
    if (!UI\Check::istZahl($sortSeite) || (!UI\Check::istZahl($sortDatenproseite) && $sortDatenproseite != 'alle')  ||
        !in_array($sortRichtung, ["ASC", "DESC"]) || !UI\Check::istZahl($sortSpalte)) {
      Anfrage::addFehler(-3, true);
    }
  }

  /**
   * Lädt die gegebenen Werte von $_POST in die entsprechenden Variablen
   * @param  boolean|string $fehler Wenn <code>true</code> oder <code>false</code>: Ob ein Fehler auftritt, wenn der Wert nicht übergeben wurde. Ansonsten: Erster Wert von ...$vars
   * @param  string ...$vars Werte
   */
  public static function post($fehler = true, ...$vars) {
    if(($fehler === true || $fehler === false) && count($vars) === 0) {
      return;
    }

    // Wenn werder true noch false
    if($fehler !== true && $fehler !== false) {
      array_unshift($vars, $fehler);
      $fehler = true;
    }

    foreach($vars as $var) {
      if(!isset($_POST[$var]) && $fehler) {
        Anfrage::addFehler(0, "Core");
      } else if (isset($_POST[$var])) {
        global $$var;
        $$var = $_POST[$var];
      } else {
        global $$var;
        $$var = null;
      }
    }
    if($fehler === true) {
      Anfrage::checkFehler();
    }
  }

  /**
   * Startet die PHP-Session, sofern dies noch nicht geschehen ist
   */
  public static function session_start() {
    if(session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }
}

$FEHLER = [];

$MODUL = "";
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";

include_once(__DIR__."/core/config.php");
include_once(__DIR__."/core/angebote.php");
include_once(__DIR__."/core/funktionen.php");
include_once(__DIR__."/core/include.php");

Core\Einbinden::modulLaden("UI", true, false);
Core\Einbinden::modulLaden("Kern", true, false);

$DSH_ALLEMODULE = Core\Einbinden::alleModuleBestimmen();

$fehler = false;

$fehler 		= $fehler || !isset($_POST);
$fehler 		= $fehler || !isset($_POST["modul"]);
$fehler 		= $fehler || !Kern\Check::istModul($_POST["modul"]);
if(!$fehler) {
  $moduldir 	= __DIR__."/module/{$_POST["modul"]}";
  if($_POST["modul"] === "Core") {
    $moduldir = __DIR__."/core";
  }
  $fehler 		= $fehler || !is_dir($moduldir);
}

if($fehler) {
  Anfrage::addFehler(1, "Core", true);
}

$fehler 		= $fehler || !isset($_POST["ziel"]);
$fehler 		= $fehler || (!is_numeric($_POST["ziel"]) || intval($_POST["ziel"]) < 0);

if($fehler) {
  Anfrage::addFehler(2, "Core", true);
}

$ZIELE = [];

if(!file_exists("$moduldir/anfragen/ziele.php")) {
  Anfrage::addFehler(3, "Core", true);
}

if($_POST["modul"] !== "Core") {
  Core\Einbinden::modulLaden($_POST["modul"], true);
}

include("$moduldir/anfragen/ziele.php");
if(!isset($ZIELE[$_POST["ziel"]])) {
  Anfrage::addFehler(4, "Core", true);
}

if(!file_exists("$moduldir/anfragen/{$ZIELE[$_POST["ziel"]]}")) {
  Anfrage::addFehler(5, "Core", true);
}

$ROOT  = __DIR__;
$MODUL = $_POST["modul"];
$DIR   = $moduldir;
$ZIEL  = $_POST["ziel"];
include("$moduldir/anfragen/{$ZIELE[$_POST["ziel"]]}");
Anfrage::ausgeben();
?>
