<?php
include_once(__DIR__."/yaml.php");
use Async\YAML;

/*
 * Eine Anfrage gibt (fast) immer JSON-Code zurück. Kommt ungültiges JSON zurück, wird, sofern dies nicht explizit überschrieben wird, eine Fehlermeldung direkt an DSH übermittelt
 * JSON-Rückgaben enthalten den Boolean {Fehler: }, welcher angibt, ob die Anfrage erfolgreich gewesen ist, oder nicht. Außerdem wird ein String {Typ: } zurückgegeben, welcher Angibt, was die Rückgabe enthält (Siehe: Anfrage::$TYP)
 * @TODO: Übermitteln des Fehlers
 */

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
    $hexid = strtoupper(dechex($this->id));
    $hexid = str_pad($hexid, 3, '0', STR_PAD_LEFT);
    return "<span class=\"dshUiFehlercode\">$hexid</span></span>";
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

/**
 * Klasse mit statischen Funtionen, die eine Anfrage benötigt
 *
 * Die Klasse hält für die gesamte Anfrage den Typ der Rückgabe als String und Rückgabewerte als Array.
 * Der eigentliche Inhalt des Rückgabearrays variiert je nach Rückgabetyp. Es sind etwa bei einer bloßen »Code«-Rückgabe keine Knöpfe notwendig.
 */
class Anfrage {
  /** @var Fehler Liste an Fehler-Objekten, die aufgetreten und auszugeben sind */
  public static $FEHLER = [];

  /** @var String Typ der Rückgabe - Ausschlaggebend für die fernere Verarbeitung der Rückgabe auf Client-Side
   *  Mögliche Werte:
   *  - Fehler:   Bei der Anfrage ist einer oder mehrere Fehler aufgetreten.        <b>Wird bei der JSON-Ausgabe auf »Meldung« gesetzt.</b>
   *  - Meldung:  Blendet "Blende" ein und setzt dessen Inhalt auf eine Meldung
   *  - Code:     Die Rückgabe enthält HTML-Code, der Client-Side benötigt wird
   *  - Seite:    Tritt beim Laden einer Seite auf
   */
  public static $TYP = null;

  /** @var [Rückgabefeld : string] => [Rückgabewert : mixed]
   *  Benötigte Felder nach Rückgabetyp: Siehe Anfrage::RUECKGABEFELDER
   *  Der Typ von Rückgabewert kann je nach Rückgabefeld variieren und wird in Anfrage::ausgeben() entsprechend verarbeitet
   */
  public static $RUECK = [];

  /** @var [Rückgabetyp : string] => [Rückgabefelder... : string]
   *  Gibt an, welche Rückgabefelder je Rückgabetyp gesetzt sein müssen
   */
  const RUECKGABEFELDER = array(
    //  »Fehler«  Array an Arrays ["Fehlercode", "Modul des Fehlers", "Beschreibung des Fehlers"], welches die gesammelten Fehler enthält.
    //    »Fehlercode«                String mit dem Fehlercode (Siehe: Fehler::getFehlercodeString())
    //    »Modul des Fehlers«         String, der den Modulnamen des zugehörigen Fehlercodes enthält. I.d.R. stammen alle Fehlercodes aus dem gleichen Modul.
    //    »Beschreibung des Fehlers«  String, welcher die Beschreibung des Fehlercodes (Siehe: fehlercodes.yml eines Moduls), und eventuell den Fehlercode, enthält.
    "Fehler"  => ["Fehler"],

    // »Titel«    entweder String oder <code>null</code>. Bei String: Der Titel der offnen Blende wird auf Titel gesetzt. Bei null: Der Titel der Blende bleibt unverändert.
    // »Meldung«  gültiger HTML-Code einer Meldung (Siehe: UI\Meldung::__toString()), welcher in den Körper der offnenen Blende geladen wird.
    // »Knöpfe«   Array [Knopfcode : string] an HTML-Code der Knöpfe (Siehe: UI\Knopf::__toString()) für die offene Blende. Ist das Array leer, wird automatisch ein dshUiKnopfStandard mit dem Inhalt »OK« und der onclick-Aktion »ui.laden.aus()« übergeben. Ist der Wert <code>null</code>, so wird nichts zurückgegeben.
    //
    // In JSON:
    // »Knöpfe«   HTML-Code der Knöpfe. <b>Nicht</b> mit Leerzeichen getrennt.
    "Meldung" => ["Titel", "Meldung", "Knöpfe"],

    // »Code«     HTML-Code, welcher Client-Side für eine Ausgabe benötigt wird, und zuvor mit entsprechenden __toString() - Methoden generiert wurde.
    "Code"    => ["Code"],

    //  »Titel«   String, der im Browser als Titel angezeigt wird
    //  »Code«    HTML-Code der anzuzeigenden Seite
    "Seite"   => ["Titel", "Code"],

    //  »Ziel«    Interne URL auf die weitergeleitet werden soll
    "Weiterleitung" => ["Ziel"],

    //  »Funktion« JS-Funktion die nach der Bearbeitung der Anfrage ausgeführt werden soll
    "Fortsetzen" => ["Funktion"]
  );

  /**
   * Setzt den Typ der Rückgabe
   * @param  string $typ :)
   */
  public static function setTyp($typ) {
    self::$TYP = $typ;
  }

  /**
   * Gibt den Typ der Rückgabe zurück
   * @return string
   */
  public static function getTyp() : ?string {
    return self::$TYP;
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
   * Fehlercode zur Liste hinzufügen
   * @param int|Fehler $fehler Wenn <code>int</code>: Fehlercode und Modul ist das aktuelle Modul, wenn <code>Fehler</code>: Fehlerobjekt
   * @param bool $die Auswertung, ob Fehler vorliegen und ggf. Abbruch
   *
   * Reservierte Fehlercodes:
   * 0: Anfragewert nicht übergeben
   */
  public static function addFehler($fehler, $die = false) {
    if(!($fehler instanceof Fehler)) {
      global $MODUL;
      $fehler = new Fehler($fehler, $MODUL);
    }
    $doppelt = false;
    foreach(self::$FEHLER as $f) {
      if($f->getId() === $fehler->getId()) {
        $doppelt = true;
      }
    }
    if(!$doppelt) {
      self::$FEHLER[] = $fehler;
    }
    if ($die) {
      Anfrage::checkFehler();
    }
  }

  /**
   * Prüft ob Fehler vorliegen und gibt diese gegebenenfalls aus. Bricht das Skript ab, wenn Fehler vorhanden sind.
   */
  public static function checkFehler() {
    if (count(self::$FEHLER) > 0) {
      $fehlerdateien = [];

      if (file_exists(__DIR__."/core/fehlercodes.yml")) {
        $fehlerdateien["Core"] = YAML::loader(file_get_contents(__DIR__."/core/fehlercodes.yml"));
      } else {
        Anfrage::setTyp("Fehler");
        Anfrage::setRueck("Fehler", [[-1, "Core", "Unbekannter Fehler. <b>Bitte melden!</b>"]]);
        Anfrage::ausgeben();
        die();
      }

      $fehlerListe = [];
      foreach (self::$FEHLER as $f) {
        $fmodul = $f->getModul();
        if (!isset($fehlerdateien[$fmodul])) {
          if (file_exists(__DIR__."/module/$fmodul/fehlercodes.yml")) {
            $fehlerdateien[$fmodul] = YAML::loader(file_get_contents(__DIR__."/module/$fmodul/fehlercodes.yml"));
          } else {
            // Stört nicht dass in foreach, weil Modul »Core« schon geladen worden ist
            self::addFehler(new Fehler(6, "Core"));
          }
        }
        $fehlerListe[] = [$f->getId(), $f->getModul(), $fehlerdateien[$fmodul][$f->getId()]["beschreibung"]];
      }


      $knopfOk = new UI\Knopf("OK");
      $knopfOk->addFunktion("onclick", "ui.laden.aus()");
      Anfrage::setTyp("Fehler");
      Anfrage::setRueck("Fehler", $fehlerListe);
      Anfrage::ausgeben();
      die();
    }
  }

  /**
   * Gibt das Resultat der Anfrage aus. Beendet das Skript <b>nicht</b>.
   */
  public static function ausgeben() {
    $ausgabe  = array();
    $typ      = self::getTyp();
    $ausgabe["Fehler"]  = $typ === "Fehler";
    $ausgabe["Typ"]     = $typ;

    $rueck = self::$RUECK;

    // Benötigte Felder
    $ben = self::RUECKGABEFELDER[$typ];
    foreach($ben as $b) {
      if(!isset($rueck[$b])) {
        trigger_error("Das Rückgabefeld »{$b}« für den Typ »{$typ}« ist nicht gesetzt worden.", E_USER_ERROR);
      }
    }

    switch($typ) {
      case "Fehler":
        $fehlercodes = $rueck["Fehler"];
        $fehlerCode = "";
        foreach($fehlercodes as $fc) {
          $code         = $fc[0];
          $code = strtoupper(dechex($code));
          $code = str_pad($code, 3, '0', STR_PAD_LEFT);

          $modul        = $fc[1];
          $beschreibung = $fc[2];
          $fehlerCode  .= new UI\Absatz("$beschreibung <span class=\"dshFehlercode\" title=\"$modul\">$code</span>");
        }
        if(count($fehlercodes) > 1) {
          $titel = "Es sind folgende Fehler aufgetreten:";
        } else {
          $titel = "Es ist folgender Fehler aufgetreten:";
        }
        $knopfOk = new UI\Knopf("OK");
        $knopfOk->addFunktion("onclick", "ui.laden.aus()");

        $ausgabe["Typ"]     = "Meldung";
        $ausgabe["Titel"]   = null;
        $ausgabe["Meldung"] = (string) new UI\Meldung($titel, $fehlerCode, "Fehler");
        $ausgabe["Knöpfe"]  = (string) $knopfOk;
        break;
      case "Meldung":
        $knoepfe = $rueck["Knöpfe"];
        if($knoepfe === null) {
          $knoepfe = [];
        } else if(count($knoepfe) === 0) {
          $knopfOk = new UI\Knopf("OK");
          $knopfOk->addFunktion("onclick", "ui.laden.aus()");

          $knoepfe = [$knopfOk];
        }

        $ausgabe["Titel"]   = (string) $rueck["Titel"];
        $ausgabe["Meldung"] = (string) $rueck["Meldung"];
        $ausgabe["Knöpfe"]  = join("", $knoepfe);
        break;
      case "Code":
        $ausgabe["Code"]    = (string) $rueck["Code"];
        break;
      case "Seite":
        $ausgabe["Titel"]   = (string) $rueck["Titel"];
        $ausgabe["Code"]    = (string) $rueck["Code"];
        break;
      case "Weiterleitung":
        $ausgabe["Ziel"]   = (string) $rueck["Ziel"];
        break;
      case "Fortsetzen":
        $ausgabe["Funktion"]   = (string) $rueck["Funktion"];
        break;
      default:
        trigger_error("Unbekannter Rückgabetyp: $typ", E_USER_ERROR);
    }
    echo json_encode($ausgabe);
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
        Anfrage::addFehler(new Fehler(0, "Core"));
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
}

$FEHLER = [];

$MODUL = "";
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
  Anfrage::addFehler(new Fehler(1, "Core"), true);
}

$fehler 		= $fehler || !isset($_POST["ziel"]);
$fehler 		= $fehler || (!is_numeric($_POST["ziel"]) || intval($_POST["ziel"]) < 0);

if($fehler) {
  Anfrage::addFehler(new Fehler(2, "Core"), true);
}

$ZIELE = [];

if(!file_exists("$moduldir/anfragen/ziele.php")) {
  Anfrage::addFehler(new Fehler(3, "Core"), true);
}
Core\Einbinden::modulLaden("UI", true, false);
Core\Einbinden::modulLaden("Kern", true, false);
if($_POST["modul"] !== "Core") {
  Core\Einbinden::modulLaden($_POST["modul"], true, false);
}
include("$moduldir/anfragen/ziele.php");
if(!isset($ZIELE[$_POST["ziel"]])) {
  Anfrage::addFehler(new Fehler(4, "Core"), true);
}

if(!file_exists("$moduldir/{$ZIELE[$_POST["ziel"]]}")) {
  Anfrage::addFehler(new Fehler(5, "Core"), true);
}

$ROOT  = __DIR__;
$MODUL = $_POST["modul"];
$DIR   = $moduldir;
$ZIEL  = $_POST["ziel"];
include("$moduldir/{$ZIELE[$_POST["ziel"]]}");
Anfrage::ausgeben();
?>
