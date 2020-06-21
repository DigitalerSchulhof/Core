<?php
namespace Core;

$DSH_URLGANZ = $_GET["URL"] ?? "";
$DSH_URL = explode("/", $DSH_URLGANZ);
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";

$DSH_DATENBANKEN = array();

include __DIR__."/core/funktionen.php";
include __DIR__."/core/include.php";
aktuellesModulBestimmen();
$DSH_DATENBANKEN = array("schulhof");
modulLaden("Kern", true, false);

echo "<!DOCTYPE html>";
echo "<html>";
	echo "<head>";
		echo style("css/layout.css");
		if(/* app */ false) {
			echo style("css/app.css");
		}
		if(isset($_COOKIE["dunkelmodus"])) {
			if($_COOKIE["dunkelmodus"] == "ja") {
				echo style("css/dunkelroh.css");
			} else {
				echo style("css/hell.css");
			}
		} else {
			echo style("css/hell.css");
			echo style("css/dunkel.css");
		}
	echo "</head>";
	echo "<body>";
		echo "<div id=\"dsh_kopfzeile_o\">";
			echo "<div id=\"dsh_kopfzeile_i\">";
				echo "<img id=\"dsh_logo_bild\" src=\"dateien/schulspezifisch/logo.png\">";
				echo "<span id=\"dsh_logo_schrift\">";
					echo "<span id=\"dsh_logo_o\">Schulname</span>";
					echo "<span id=\"dsh_logo_u\">Schule Ort</span>";	// TODO: Schuldaten
				echo "</span>";
				echo "<div class=\"dsh_clear\"></div>";
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dsh_platzhalter\"></div>";
		echo "<div id=\"dsh_hauptteil_o\">";
			echo "<div id=\"dsh_hauptteil_i\">";
				seiteEinbinden($DSH_URL);
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dsh_fusszeile_o\">";
			echo "<div id=\"dsh_fusszeile_i\">";
			echo "</div>";
		echo "</div>";
	echo "</body>";
echo "</body>";
?>
<!-- Digitaler Schulhof - Version 1.0 --><!-- 🍪 -->
