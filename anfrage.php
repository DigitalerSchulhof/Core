<?php

class Anfrage {
  /**
   * Anfrage beenden und Fehlercode ausgeben
   * @param  integer $num Fehlercode
   *
   *
   * Reservierte Fehlercodes:
   * 0: Anfragewert nicht übergeben
   */
  public static function fehler($num) {
    global $MODUL;
    echo "{$MODUL}FEHLER".dechex($num);
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
        Anfrage::fehler(0);
      } else {
        global $$var;
        $$var = $_POST[$var];
      }
    }
  }
}

$MODUL = "Core";
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";
$DSH_DATENBANKEN = array();

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
  Anfrage::fehler(1);
}

$fehler 		= $fehler || !isset($_POST["ziel"]);
$fehler 		= $fehler || (!is_numeric($_POST["ziel"]) || intval($_POST["ziel"]) < 0);

if($fehler) {
  Anfrage::fehler(2);
}

$ZIELE = array();

if(!file_exists("$moduldir/anfragen/ziele.php")) {
  Anfrage::fehler(3);
}
if($_POST["modul"] !== "Core") {
  Core\modulLaden($_POST["modul"], true, false);
}
include("$moduldir/anfragen/ziele.php");
if(!isset($ZIELE[$_POST["ziel"]])) {
  Anfrage::fehler(4);
}

if(!file_exists("$moduldir/{$ZIELE[$_POST["ziel"]]}")) {
  Anfrage::fehler(5);
}

$MODUL = $_POST["modul"];
$DIR   = $moduldir;
$ZIEL  = $_POST["ziel"];
include("$moduldir/{$ZIELE[$_POST["ziel"]]}");
?>