<?php
include __DIR__."/yaml.php";
use Async\YAML;

$DSH_CORE = __DIR__."/core";
$DSH_MODULE = __DIR__."/module";

/** @var array Priorität => Seitenliste */
$globseitenliste = array();

/**
* Erzeugt die serialized-Version der YAML-Modulkonfiguration
* @param string $modul Das Modul
*/
function modulKeimen($modul) {
	global $globseitenliste, $DSH_MODULE;
	echo "Modul »{$modul}« keimen lassen<br>\n";
	$config = YAML::loader(file_get_contents("$DSH_MODULE/$modul/modul.yml"));
	$config = $config["modul"];

	$standard = array(
		"seitenPrio"			=> 0,
		"speicher"				=> "dateien/$modul",
		"datenbanken"			=> array("schulhof"),
		"benötigt"				=> array(),
		"erweitert"				=> array()
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
		array_push($globseitenliste[$seitenprio], ...$modulSeiten);
	}

	// Rechte keimen lassen
	$rechteliste = "$DSH_MODULE/$modul/funktionen/rechte.yml";
	if(file_exists($seitenliste)) {
		$modulRechte = YAML::loader($rechteliste);
		$modulRechte = $modulRechte["rechte"];
		file_put_contents("$DSH_MODULE/$modul/funktionen/rechte.core", serialize($modulRechte));
	}

	// Einstelungen keimen lassen
	$einstellungenliste = "$DSH_MODULE/$modul/funktionen/einstellungen.yml";
	if(file_exists($seitenliste)) {
		$modulEinstellungen = YAML::loader($einstellungenliste);
		$modulEinstellungen = $modulEinstellungen["einstellungen"];
		file_put_contents("$DSH_MODULE/$modul/funktionen/einstellungen.core", serialize($modulEinstellungen));
	}

	file_put_contents("$DSH_MODULE/$modul/modul.core", serialize($config));

	// Styles keimen lassen
	$styledir = "$DSH_MODULE/$modul/styles";
	if(is_dir($styledir)) {
		foreach(array_diff(scandir($styledir), array(".", "..")) as $style) {
				// TODO: StYlEs MaChEn
		}
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
		array_push($seiten, ...$s);
	}

	file_put_contents("$DSH_CORE/seitenliste.core", serialize($seiten));

	// Styles keimen lassen
} else {
	echo "<a href=\"?keimen=ja\">Keimen</a>";
}
?>