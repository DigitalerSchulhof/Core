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
modulLaden("Kern", true, false);

echo "<!DOCTYPE html>";
echo "<html>";
	echo "<head>";
		echo "<link rel=\"stylesheet\" href=\"css/hell.css\">";
		if(!isset($_COOKIE["dunkelmodus"])) {
			echo "<link rel=\"stylesheet\" href=\"css/dunkel.css\">";
		}
		if($_COOKIE["dunkelmodus"] ?? "nein" == "ja") {
			echo "<link rel=\"stylesheet\" href=\"css/dunkelroh.css\">";
		}
	echo "</head>";
	echo "<body>";
		echo "<div id=\"dsh_kopfzeile\">";
			echo "<img id=\"dsh_logo_bild\" src=\"dateien/schulspezifisch/logo.png\">";
			echo "<span id=\"dsh_logo_schrift\">";
				echo "<span id=\"dsh_logo_o\">Schulname</span>";
				echo "<span id=\"dsh_logo_u\">Schule Ort</span>";	// TODO: Schuldaten
			echo "</span>";
			echo "<div class=\"dsh_clear\"></div>";
		echo "</div>";
		echo "<div id=\"dsh_hauptteil\">";
			seiteEinbinden($DSH_URL);
		echo "</div>";
		echo "<div id=\"dsh_fusszeile\">";
		echo "</div>";
	echo "</body>";
echo "</body>";
?>
<!-- Digitaler Schulhof - Version 1.2..4.5.6.87.8.. --><!-- 🍪 -->
