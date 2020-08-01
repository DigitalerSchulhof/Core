<?php
include_once(__DIR__."/yaml.php");
use Async\YAML;

class Fehler {
  /** @var int id */
  private $id;

  /** @var string modul */
  private $modul;

  /**
   * Erstellt einen neuen Fehler
   * @param int $id    :)
   * @param string $modul :)
   */
  public function __construct($id, $modul = null) {
    if ($modul === null) {
      global $MODUL;
      $modul = $MODUL;
    }
    $this->id = $id;
    $this->modul = $modul;
  }

  /**
   * Gibt den Fehler aus
   * @return string :)
   */
  public function __toString() : string {
    return "<span class=\"dshUiFehlermeldung\"><span class=\"dshUiModul\">{$this->modul}</span><span class=\"dshUiFehlercode\">{$this->id}</span></span>";
  }

  /**
   * Gibt die ID des Fehlers zurück
   * @return int :)
   */
  public function getId() : int {
    return $this->id;
  }

  /**
   * Gibt das Modul des Fehlers zurück
   * @return string :)
   */
  public function getModul() : string {
    return $this->modul;
  }

}

class Anfrage {
  /**
   * Anfrage beenden und Fehlercode ausgeben
   * @param integer $num Fehlercode
   * @param bool $die Auswertung, ob Fehler vorliegen und ggf. Abbruch
   *
   * Reservierte Fehlercodes:
   * 0: Anfragewert nicht übergeben
   */
  public static function addFehler($nr, $die = false) {
    global $FEHLER, $MODUL;
    $neu = new Fehler($nr, $MODUL);
    if (!in_array($neu, $FEHLER)) {
      $FEHLER[] = $neu;
    }
    if ($die) {
      Anfrage::hatFehler();
    }
  }

  /**
   * Prüft ob Fehler vorliegen und
   * gibt diese gegebenenfalls aus und
   * bricht das Skript ab
   */
  public static function hatFehler() {
    global $MODUL, $FEHLER;
    if (count($FEHLER) > 0) {
      $fehlerdateien = [];

      if (file_exists(__DIR__."/core/fehlercodes.yml")) {
        $fehlerdateien["Core"] = YAML::loader(file_get_contents(__DIR__."/core/fehlercodes.yml"));
      } else {
        Anfrage::antwort("Fehler", "Fehler", new UI\Meldung("Unbekannter Fehler", "Bei der Bearbeitung der Anfrage ist ein unbekannter Fehler aufgetreten. Fehlercode: ".(new Fehler(-1, "Core")), "Fehler"));
      }

      $inhalt = "";
      foreach ($FEHLER as $f) {
        if ($f->getId() == 0) {
          $fmodul = "Core";
        } else {
          $fmodul = $f->getModul();
        }
        if (!isset($fehlerdateien[$fmodul])) {
          if (file_exists(__DIR__."/module/$fmodul/fehlercodes.yml")) {
            $fehlerdateien[$fmodul] = YAML::loader(file_get_contents(__DIR__."/module/$fmodul/fehlercodes.yml"));
          } else {
            $FEHLER[] = new Fehler(6, "Core");
          }
        }

        $inhalt .= new UI\Absatz($fehlerdateien[$fmodul][$f->getId()]["beschreibung"]." ".(new Fehler($f->getId(), $f->getModul())));
      }

      $meldung = new UI\Meldung("Es sind Fehler aufgetreten", $inhalt, "Fehler");
      $abbrechen = new UI\Knopf("OK");
      $abbrechen->addFunktion("onclick", "ui.laden.aus()");

      Anfrage::antwort("Meldung", null, (string) $meldung, (string) $abbrechen);
    }
  }

  public static function antwort($typ, $titel, $inhalt, $aktionen = []) {
    $aktionscode = "";
    if (is_array($aktionen)) {
      foreach ($aktionen as $a) {
        $aktionscode .= $a;
      }
    } else {
      $aktionscode = $aktionen;
    }
    echo json_encode(array("typ" => $typ, "titel" => $titel, "inhalt" => $inhalt, "aktionen" => $aktionscode));
    die();
  }

  /**
   * Lädt die gegebenen Werte von $_POST in die entsprechenden Variablen
   * @param  boolean|string $fehler Wenn <code>true</code> oder <code>false</code>: Ob ein Fehler auftritt, wenn der Wert nicht übergeben wurde. Ansonsten: Erster Wert von ...$vars
   * @param  string ...$var Werte
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
        Anfrage::addFehler(0);
      } else if (isset($_POST[$var])) {
        global $$var;
        $$var = $_POST[$var];
      } else {
        global $$var;
        $$var = null;
      }
    }
  }
}

$FEHLER = [];

$MODUL = "Core";
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";
$DSH_DATENBANKEN = [];

include_once(__DIR__."/core/config.php");
include_once(__DIR__."/core/check.php");
include_once(__DIR__."/core/angebote.php");
include_once(__DIR__."/core/funktionen.php");
include_once(__DIR__."/core/include.php");

$fehler = false;

$fehler 		= $fehler || !isset($_POST);
$fehler 		= $fehler || !isset($_POST["modul"]);
$fehler 		= $fehler || !preg_match("/^[A-Za-z0-9]{1,16}$/", $_POST["modul"]);
$moduldir 	= __DIR__."/module/{$_POST["modul"]}";
if($_POST["modul"] === "Core") {
  $moduldir = __DIR__."/core";
}
$fehler 		= $fehler || !is_dir($moduldir);

if($fehler) {
  Anfrage::addFehler(1, true);
}

$fehler 		= $fehler || !isset($_POST["ziel"]);
$fehler 		= $fehler || (!is_numeric($_POST["ziel"]) || intval($_POST["ziel"]) < 0);

if($fehler) {
  Anfrage::addFehler(2, true);
}

$ZIELE = [];

if(!file_exists("$moduldir/anfragen/ziele.php")) {
  Anfrage::addFehler(3, true);
}
Core\Einbinden::modulLaden("UI", true, false);
Core\Einbinden::modulLaden("Kern", true, false);
if($_POST["modul"] !== "Core") {
  Core\Einbinden::modulLaden($_POST["modul"], true, false);
}
include("$moduldir/anfragen/ziele.php");
if(!isset($ZIELE[$_POST["ziel"]])) {
  Anfrage::addFehler(4, true);
}

if(!file_exists("$moduldir/{$ZIELE[$_POST["ziel"]]}")) {
  Anfrage::addFehler(5, true);
}

$MODUL = $_POST["modul"];
$DIR   = $moduldir;
$ZIEL  = $_POST["ziel"];
include("$moduldir/{$ZIELE[$_POST["ziel"]]}");
?>
