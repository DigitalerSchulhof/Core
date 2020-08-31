<?php
include __DIR__."/yaml.php";
include __DIR__."/module/Kern/klassen/db/db.php";
include __DIR__."/module/Kern/klassen/db/anfrage.php";
use \Kern\DB;

$dbs = new DB("localhost", 3306, "root", "", "dsh_schulhof", "MeinPasswortIstSicher");

use Async\YAML;

$DSH_CORE = __DIR__."/core";
$DSH_MODULE = __DIR__."/module";

/** @var array Priorität => Seitenliste */
$globseitenliste = [];

/** @var array Angbote als [Modul][Platz][Angebot]*/
$globangebote = [];

/** @var array Alle Styles, die es gibt */
$allestyles = array("layout" => "", "hell" => "", "dunkel" => "", "dunkelroh" => "", "drucken" => "");

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
    $anfrage = $dbs->anfrage("SELECT s.bezeichnung, IFNULL(s.wert_h, ah.wert_h), IFNULL(s.wert_d, ad.wert_d) FROM kern_styles as s JOIN dsh_module as m ON m.id = s.modul LEFT JOIN kern_styles as ah ON ah.id = s.alias_h LEFT JOIN kern_styles as ad ON ad.id = s.alias_d WHERE m.name = ? OR m.id = 0 ORDER BY s.modul ASC", "s", $modul);
    $styles = [];
    while($anfrage->werte($bezeichnung, $wert_h, $wert_d)) {
      $styles[$bezeichnung] = array($wert_h, $wert_d);
    }
    $wert = function($wert, $styleindex) use ($styles) {
      if(isset($styles[$wert][$styleindex])) {
        return $styles[$wert][$styleindex];
      }
      if(isset($styles[$wert][0])) {
        return $styles[$wert][0];
      }
      global $$wert;
      if(isset($$wert)) {
        return $$wert;
      }
      trigger_error("Der Wert »{$wert}« [$styleindex] ist nicht definiert!", E_USER_WARNING);
      return "FEHLENDER_WERT";
    };

    $mlayout    = "";
    $mhell      = "";
    $mdunkel    = "";
    $mdunkelroh = "";
    $mdrucken   = "";

    foreach(array_diff(scandir($styledir), array(".", "..")) as $style) {
      echo "Style: module/$modul/styles/$style\n";
      ob_flush();
      flush();

      ob_start();
      echo "// LAYOUT;\n";
      include "$styledir/$style";
      $ob = ob_get_contents();
      ob_end_clean();

      $layout = "";
      $farben = "";
      $drucken = "";
      $modus = &$layout;
      foreach(explode("\n", $ob) as $zeile) {
        if(substr($zeile, 0, strlen("// LAYOUT;")) === "// LAYOUT;") {
          $modus = &$layout;
          continue;
        }
        if(substr($zeile, 0, strlen("// FARBEN;")) === "// FARBEN;") {
          $modus = &$farben;
          continue;
        }
        if(substr($zeile, 0, strlen("// DRUCKEN;")) === "// DRUCKEN;") {
          $modus = &$drucken;
          continue;
        }
        if(preg_match("/^[\\s\\t]*\/\//", $zeile) !== 1) {  // Kommentare weglassen
          $zeile = preg_replace("/\\t*/", "", $zeile);
          $zeile = preg_replace("/\\n*/", "", $zeile);
          $zeile = preg_replace("/\\r*/", "", $zeile);
          $zeile = preg_replace("/\\s\\s+/", "", $zeile);
          $modus .= $zeile;
        }
      }

      $hell   = $farben;
      $dunkel = $farben;

      $layout   = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes|import)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($wert) {return $wert($match[1], 0);}, $layout);
      $hell     = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes|import)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($wert) {return $wert($match[1], 0);}, $hell);
      $dunkel   = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes|import)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($wert) {return $wert($match[1], 1);}, $dunkel);
      $drucken  = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes|import)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($wert) {return $wert($match[1], 0);}, $drucken);

      $kurz = array(
        "\\s*{\\s*" => "{",
        "\\s*}\\s*" => "}",
        "\\s*:\\s*" => ":",
        ";}"        => "}",
        "\\s+!"     => "!",
        "}\\s+\\."  => "}.",
        "}\\s+#"    => "}#",
        "\\s*,\\s*" => ",",
        ":0px"      => ":0",
      );

      foreach($kurz as $rx => $r) {
        $layout   = preg_replace("/$rx/", "$r",           $layout);
        $hell     = preg_replace("/$rx/", "$r",           $hell);
        $dunkel   = preg_replace("/$rx/", "$r",           $dunkel);
        $drucken  = preg_replace("/$rx/", "$r",           $drucken);
      }

      $mlayout    .= $layout;
      $mhell      .= $hell;
      $mdunkel    .= $dunkel;
      $mdrucken   .= $drucken;
    }

    $allestyles["layout"]     .= $mlayout;
    $allestyles["hell"]       .= $mhell;
    $allestyles["dunkel"]     .= $mdunkel;
    $allestyles["dunkelroh"]  .= $mdunkelroh;
    $allestyles["drucken"]    .= $mdrucken;
  }
  echo "Modul »{$modul}« ausgewachsen\n\n";
  ob_flush();
  flush();
}

// Module scannen
foreach(array_diff(scandir($DSH_MODULE), array(".", "..", ".htaccess")) as $modul) {
  $MODULE[] = $modul;
}
echo "<pre>";
if($_GET["keimen"] ?? "nein" == "ja") {
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
  extract($allestyles);

  $layout       = "$layout";
  $hell         = "$hell";
  $dunkelroh    = "$dunkel";
  $dunkel       = "@media (prefers-color-scheme: dark) { $dunkel }";
  $drucken      = "@media print { $drucken }";

  file_put_contents(__DIR__."/css/layout.css",    $layout);
  file_put_contents(__DIR__."/css/hell.css",      $hell);
  file_put_contents(__DIR__."/css/dunkel.css",    $dunkel);
  file_put_contents(__DIR__."/css/dunkelroh.css", $dunkelroh);
  file_put_contents(__DIR__."/css/drucken.css",   $drucken);
  echo "Styles gespeichert.\n";

  file_put_contents(__DIR__."/core/rechte.core",   serialize($allerechte));
  echo "Rechte gespeichert.\n";
} else {
  echo "<a href=\"?keimen=ja\">Keimen</a>";
}
echo "</pre>";
?>