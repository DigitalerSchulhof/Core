<?php
include __DIR__."/yaml.php";
include __DIR__."/module/Kern/klassen/db/DB.php";
include __DIR__."/module/Kern/klassen/db/Anfrage.php";
use \DB\DB;

$dbs = new DB("localhost", "root", "", "dsh_schulhof", "MeinPasswortIstSicher");

use Async\YAML;

$DSH_CORE = __DIR__."/core";
$DSH_MODULE = __DIR__."/module";

/** @var array Priorität => Seitenliste */
$globseitenliste = array();

/** @var array Alle Styles, die es gibt */
$allestyles = array("layout" => "", "mobil" => "", "hell" => "", "dunkel" => "", "dunkelroh" => "", "drucken" => "");

/**
* Erzeugt die serialized-Version der YAML-Modulkonfiguration
* @param string $modul Das Modul
*/
function modulKeimen($modul) {
  global $globseitenliste, $DSH_MODULE, $dbs, $allestyles;
  echo "Modul »{$modul}« keimen lassen<br>\n";
  $config = YAML::loader(file_get_contents("$DSH_MODULE/$modul/modul.yml"));
  $config = $config["modul"];

  $standard = array(
    "seitenPrio"      => 0,
    "speicher"        => "dateien/$modul",
    "datenbanken"     => array("schulhof"),
    "benötigt"        => array(),
    "erweitert"       => array()
  );


  if(!isset($config["name"])) {
    echo "Eintrag »name« fehlt in der Modulkonfiguration<br>\n";
  }
  if(!isset($config["beschreibung"])) {
    echo "Eintrag »beschreibung« fehlt in der Modulkonfiguration<br>\n";
  }
  if(!isset($config["lehrernetz"])) {
    echo "Eintrag »lehrernetz« fehlt in der Modulkonfiguration<br>\n";
  }
  if(!isset($config["autor"])) {
    echo "Eintrag »autor« fehlt in der Modulkonfiguration<br>\n";
  }
  if(!isset($config["version"])) {
    echo "Eintrag »version« fehlt in der Modulkonfiguration<br>\n";
  }

  $config = array_merge($standard, $config);

  // Seiten keimen lassen
  $seitenliste = "$DSH_MODULE/$modul/seiten/seitenliste.yml";
  $seitenprio = $config["seitenPrio"];
  if(file_exists($seitenliste)) {
    $modulSeiten = YAML::loader($seitenliste);
    $modulSeiten = $modulSeiten["seiten"];
    $globseitenliste[$seitenprio] = array_merge(($globseitenliste[$seitenprio] ?? array()), $modulSeiten);
  }

  // Rechte keimen lassen
  $rechteliste = "$DSH_MODULE/$modul/funktionen/rechte.yml";
  if(file_exists($rechteliste)) {
    $modulRechte = YAML::loader($rechteliste);
    $modulRechte = $modulRechte["rechte"];
    file_put_contents("$DSH_MODULE/$modul/funktionen/rechte.core", serialize($modulRechte));
  }

  // Einstelungen keimen lassen
  $einstellungenliste = "$DSH_MODULE/$modul/funktionen/einstellungen.yml";
  if(file_exists($einstellungenliste)) {
    $modulEinstellungen = YAML::loader($einstellungenliste);
    $modulEinstellungen = $modulEinstellungen["einstellungen"];
    file_put_contents("$DSH_MODULE/$modul/funktionen/einstellungen.core", serialize($modulEinstellungen));
  }

  file_put_contents("$DSH_MODULE/$modul/modul.core", serialize($config));

  // Styles keimen lassen
  $styledir = "$DSH_MODULE/$modul/styles";
  if(is_dir($styledir)) {
    $anfrage = $dbs->anfrage("SELECT s.bezeichnung, IFNULL(s.wert_h, ah.wert_h), IFNULL(s.wert_d, ad.wert_d) FROM kern_styles as s LEFT JOIN dsh_module as m ON m.id = s.modul OR s.modul = 0 LEFT JOIN kern_styles as ah ON ah.id = s.alias_h LEFT JOIN kern_styles as ad ON ad.id = s.alias_d WHERE m.name = ? ORDER BY s.modul ASC", "s", $modul);
    $styles = array();
    while($anfrage->werte($bezeichnung, $wert_h, $wert_d)) {
      $styles[$bezeichnung] = array($wert_h, $wert_d);
    }

    ob_start();
    foreach(array_diff(scandir($styledir), array(".", "..")) as $style) {
      include "$DSH_MODULE/$modul/styles/$style";
      echo "\n";
    }
    $ob = ob_get_contents();
    ob_end_clean();

    $layout = "";
    $mobil = "";
    $farben = "";
    $drucken = "";
    $modus = &$layout;
    foreach(explode("\n", $ob) as $zeile) {
      if(substr($zeile, 0, strlen("// LAYOUT;")) === "// LAYOUT;") {
        $modus = &$layout;
        continue;
      }
      if(substr($zeile, 0, strlen("// MOBIL;")) === "// MOBIL;") {
        $modus = &$mobil;
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
      if(substr($zeile, 0, 2) !== "//") {  // Kommentare weglassen
        $zeile = preg_replace("/\\t*/", "", $zeile);
        $modus .= $zeile;
      }
    }

    $hell   = $farben;
    $dunkel = $farben;

    $layout   = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($styles) {return $styles[$match[1]][0];}, $layout);
    $mobil    = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($styles) {return $styles[$match[1]][0];}, $mobil);
    $hell     = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($styles) {return $styles[$match[1]][0];}, $hell);
    $dunkel   = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($styles) {return $styles[$match[1]][1] ?? $styles[$match[1]][0];}, $dunkel);
    $drucken  = preg_replace_callback("/@((?!media|font|page|-moz-document|keyframes|-webkit-keyframes)[\\w_\\-ÄÖÜäöüß]+)/", function($match) use ($styles) {return $styles[$match[1]][0];}, $drucken);

    $layout   = preg_replace("/;}/", "}",   $layout);
    $mobil    = preg_replace("/;}/", "}",   $mobil);
    $hell     = preg_replace("/;}/", "}",   $hell);
    $dunkel   = preg_replace("/;}/", "}",   $dunkel);
    $drucken  = preg_replace("/;}/", "}",   $drucken);

    $layout       = "$layout";
    $mobil        = "$mobil";
    $hell         = "$hell";
    $dunkelroh    = "$dunkel";
    $dunkel       = "@media (prefers-color-scheme: dark) { $dunkel }";
    $drucken      = "@media print { $drucken }";

    $allestyles["layout"]     .= $layout;
    $allestyles["mobil"]      .= $mobil;
    $allestyles["hell"]       .= $hell;
    $allestyles["dunkel"]     .= $dunkel;
    $allestyles["dunkelroh"]  .= $dunkelroh;
    $allestyles["drucken"]    .= $drucken;
  }
  echo "Modul »{$modul}« ausgewachsen<br>\n<br>\n";
}

// Module scannen
foreach(array_diff(scandir($DSH_MODULE), array(".", "..", ".htaccess")) as $modul) {
  $MODULE[] = $modul;
}

if($_GET["keimen"] ?? "nein" == "ja") {
  foreach($MODULE as $modul) {
    modulKeimen($modul);
  }

  krsort($globseitenliste);

  $seiten = array();
  foreach($globseitenliste as $s) {
    $seiten = array_merge($seiten, $s);
  }

  file_put_contents("$DSH_CORE/seitenliste.core", serialize($seiten));

  // Styles keimen lassen
  file_put_contents(__DIR__."/css/layout.css",      $allestyles["layout"]);
  file_put_contents(__DIR__."/css/mobil.css",       $allestyles["mobil"]);
  file_put_contents(__DIR__."/css/hell.css",        $allestyles["hell"]);
  file_put_contents(__DIR__."/css/dunkel.css",      $allestyles["dunkel"]);
  file_put_contents(__DIR__."/css/dunkelroh.css",   $allestyles["dunkelroh"]);
  file_put_contents(__DIR__."/css/drucken.css",     $allestyles["drucken"]);

} else {
  echo "<a href=\"?keimen=ja\">Keimen</a>";
}
?>