<?php
namespace Core;

$DSH_URLGANZ = $_GET["URL"] ?? "";
$DSH_URL = explode("/", $DSH_URLGANZ);
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";

$DSH_DATENBANKEN = array();

include __DIR__."/core/include.php";
aktuellesModulBestimmen();
$DSH_DATENBANKEN = array("schulhof");
coreEinbinden();

echo "<!DOCTYPE html>";
echo "<html>";
	echo "<head>";
		echo "<link rel=\"stylesheet\" href=\"css/hell.css\">";
		// TODO: Darkmode Einstellung
		echo "<link rel=\"stylesheet\" href=\"css/dunkel.css\">";
	echo "</head>";
	echo "<body>";
		seiteEinbinden($DSH_URL);
	echo "</body>";
echo "</body>";
?>

<!-- Digitaler Schulhof - Version 1.2..4.5.6.87.8.. -->
<!-- 🍪 -->