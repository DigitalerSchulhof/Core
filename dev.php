<?php
include __DIR__."/core/config.php";
include __DIR__."/core/yaml.php";
require __DIR__."/vendor/autoload.php";
include __DIR__."/module/Kern/klassen/db/db.php";
include __DIR__."/module/Kern/klassen/db/anfrage.php";
use \Kern\DB;

$dbs = new DB($EINSTELLUNGEN["Datenbanken"]["Schulhof"]["Host"],
              $EINSTELLUNGEN["Datenbanken"]["Schulhof"]["Port"],
              $EINSTELLUNGEN["Datenbanken"]["Schulhof"]["Benutzer"],
              $EINSTELLUNGEN["Datenbanken"]["Schulhof"]["Passwort"],
              $EINSTELLUNGEN["Datenbanken"]["Schulhof"]["DB"],
              $EINSTELLUNGEN["Datenbanken"]["Schulhof"]["Schluessel"]
            );
$cli = php_sapi_name() == "cli";

if($cli) {
  // Single-line ProblemMatcher
  set_exception_handler (function($ex) {
    /** @var Exception $ex */
    $msg = explode("\n", $ex->getMessage())[0];
    echo "Error: $msg in {$ex->getFile()} on line {$ex->getLine()}\n";
    echo $ex->getMessage();
  });
}

use Async\YAML;

$DSH_CORE = __DIR__."/core";
$DSH_MODULE = __DIR__."/module";

/** @var array Priorität => Seitenliste */
$globseitenliste = [];

/** @var array Angbote als [Modul][Platz][Angebot]*/
$globangebote = [];

/** @var array Alle Styles, die es gibt */
$allestyles = array("layout" => "", "farben" => "", "drucken" => "");

/** @var array Alle Rechte */
$allerechte = [];

/**
* Erzeugt die serialized-Version der YAML-Modulkonfiguration
* @param string $modul Das Modul
*/
function modulKeimen($modul) {
  global $globseitenliste, $globangebote, $DSH_MODULE, $dbs, $allestyles, $allerechte;
  echo "Modul »{$modul}« keimen lassen\n";
  $config = YAML::loader(file_get_contents("$DSH_MODULE/$modul/modul.yml"));

  $standard = array(
    "seitenPrio"      => 0,
    "speicher"        => "dateien/$modul",
    "benötigt"        => [],
    "erweitert"       => []
  );


  if(!isset($config["name"])) {
    echo "Eintrag »name« fehlt in der Modulkonfiguration\n";
  }
  if(!isset($config["beschreibung"])) {
    echo "Eintrag »beschreibung« fehlt in der Modulkonfiguration\n";
  }
  if(!isset($config["lehrernetz"])) {
    echo "Eintrag »lehrernetz« fehlt in der Modulkonfiguration\n";
  }
  if(!isset($config["autor"])) {
    echo "Eintrag »autor« fehlt in der Modulkonfiguration\n";
  }
  if(!isset($config["version"])) {
    echo "Eintrag »version« fehlt in der Modulkonfiguration\n";
  }

  $config = array_merge($standard, $config);

  // Seiten keimen lassen
  $seitenliste = "$DSH_MODULE/$modul/seiten/seitenliste.yml";
  $seitenprio = $config["seitenPrio"];
  if(file_exists($seitenliste)) {
    $modulSeiten = YAML::loader($seitenliste);
    $modulSeiten = array($modul => $modulSeiten["seiten"]);
    $globseitenliste[$seitenprio] = array_merge(($globseitenliste[$seitenprio] ?? []), $modulSeiten);
  }

  // Rechte keimen lassen
  $rechteliste = "$DSH_MODULE/$modul/funktionen/rechte.yml";
  if(file_exists($rechteliste)) {
    $modulRechte = YAML::loader($rechteliste);
    foreach($modulRechte as $root => $rechte) {
      $allerechte[$root] = array_merge_recursive($allerechte[$root] ?? array(), $rechte);
    }
  }

  // Einstelungen keimen lassen
  $einstellungenliste = "$DSH_MODULE/$modul/funktionen/einstellungen.yml";
  if(file_exists($einstellungenliste)) {
    $modulEinstellungen = YAML::loader($einstellungenliste);
    $modulEinstellungen = $modulEinstellungen["einstellungen"];
    file_put_contents("$DSH_MODULE/$modul/funktionen/einstellungen.core", serialize($modulEinstellungen));
  }

  // Angebote keimen lassen
  $angeboteliste = "$DSH_MODULE/$modul/angebote/angebote.yml";
  if(file_exists($angeboteliste)) {
    $modulAngebote = YAML::loader($angeboteliste);
    $modulAngebote = $modulAngebote["angebote"];
    $globangebote[$modul] = $modulAngebote;
  }

  file_put_contents("$DSH_MODULE/$modul/modul.core", serialize($config));

  // Styles keimen lassen
  $styledir = "$DSH_MODULE/$modul/styles";
  if(is_dir($styledir)) {
    foreach(array_diff(scandir($styledir), array(".", "..")) as $style) {
      echo "Style: module/$modul/styles/$style\n";
      flush();

      ob_start();
      echo "// LAYOUT;\n";
      include "$styledir/$style";
      $ob = ob_get_contents();
      ob_end_clean();

      $modus = &$allestyles["layout"];
      foreach(explode("\n", $ob) as $zeile) {
        if(substr($zeile, 0, strlen("// LAYOUT;")) === "// LAYOUT;") {
          $modus = &$allestyles["layout"];
          continue;
        }
        if(substr($zeile, 0, strlen("// FARBEN;")) === "// FARBEN;") {
          $modus = &$allestyles["farben"];
          continue;
        }
        if(substr($zeile, 0, strlen("// DRUCKEN;")) === "// DRUCKEN;") {
          $modus = &$allestyles["drucken"];
          continue;
        }
        $modus .= "$zeile\n";
      }
    }
  }
  echo "Modul »{$modul}« ausgewachsen\n\n";
  flush();
}

if(!$cli) {
  echo "<pre>";
}

// Module scannen
foreach(array_diff(scandir($DSH_MODULE), array(".", "..", ".htaccess")) as $modul) {
  $MODULE[] = $modul;
}
foreach($MODULE as $modul) {
  modulKeimen($modul);
}

krsort($globseitenliste);

$seiten = [];
foreach($globseitenliste as $s) {
  $seiten = array_merge($seiten, $s);
}

file_put_contents("$DSH_CORE/seitenliste.core", serialize($seiten));
echo "Seitenliste gespeichert.\n";

$platzangebote = [];
foreach($globangebote as $modul => $plaetze) {
  foreach($plaetze as $platz => $angebot) {
    $platzangebote[$platz] = $platzangebote[$platz] ?? [];
    $platzangebote[$platz][$modul] = $angebot;
  }
}

file_put_contents("$DSH_CORE/angebote.core", serialize($platzangebote));
echo "Angebote gespeichert.\n";

// Styles keimen lassen

$anfrage = $dbs->anfrage("SELECT s.bezeichnung, s.wert_h, s.wert_d, s.alias_h, s.alias_d FROM kern_styles as s");
$stylesHell = [];
$stylesDunkel = [];

while($anfrage->werte($bezeichnung, $wertHell, $wertDunkel, $aliasHell, $aliasDunkel)) {
  while($wertHell === null) {
    $anfrageHell = $dbs->anfrage("SELECT a.wert_h, a.alias_h FROM kern_styles as a WHERE a.id = ?", "i", $aliasHell);
    $anfrageHell->werte($wertHell, $aliasHell);
  }
  while($wertDunkel === null) {
    $anfrageDunkel = $dbs->anfrage("SELECT a.wert_d, a.alias_d FROM kern_styles as a WHERE a.id = ?", "i", $aliasDunkel);
    $anfrageDunkel->werte($wertDunkel, $aliasDunkel);
    if($wertDunkel === null && $aliasDunkel === null) {
      $wertDunkel = $wertHell;
    }
  }
  $stylesHell[$bezeichnung] = $wertHell;
  $stylesDunkel[$bezeichnung] = $wertDunkel;
}

while ($anfrage->werte($bezeichnung, $wert_h, $wert_d)) {
  $stylesHell[$bezeichnung] = $wert_h;
  $stylesDunkel[$bezeichnung] = $wert_d ?? $wert_h;
}

$layout       = "{$allestyles["layout"]}";
$hell         = "{$allestyles["farben"]}";
$dunkelroh    = "{$allestyles["farben"]}";
$dunkel       = "@media (prefers-color-scheme: dark) { $dunkelroh }";
$drucken      = "@media print { {$allestyles["drucken"]} }";

$options = ["compress" => true];
// $options = [];

// Layout
$less = new Less_Parser($options);
$less->ModifyVars($stylesHell);
$layout     = $less->parse($layout)->getCss();
$less->Reset();

// Hell
$less->ModifyVars($stylesHell);
$hell       = $less->parse($hell)->getCss();
$less->Reset();

// Dunkel
$less->ModifyVars($stylesDunkel);
$dunkel     = $less->parse($dunkel)->getCss();
$less->Reset();

$less->ModifyVars($stylesDunkel);
$dunkelroh  = $less->parse($dunkelroh)->getCss();
$less->Reset();

// Drucken
$drucken    = $less->parse($drucken)->getCss();
$less->Reset();

if (!file_exists(__DIR__."/css")) {
  mkdir(__DIR__."/css");
}

file_put_contents(__DIR__."/css/layout.css",    $layout);
file_put_contents(__DIR__."/css/hell.css",      $hell);
file_put_contents(__DIR__."/css/dunkel.css",    $dunkel);
file_put_contents(__DIR__."/css/dunkelroh.css", $dunkelroh);
file_put_contents(__DIR__."/css/drucken.css",   $drucken);
echo "Styles gespeichert.\n";

file_put_contents(__DIR__."/core/rechte.core",   serialize($allerechte));
echo "Rechte gespeichert.\n";

if(!$cli) {
  echo "</pre>";
}
?>