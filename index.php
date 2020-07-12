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
modulLaden("UI", true, false);

use UI;

seiteEinbinden($DSH_URL);

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
		echo js("js/core.js");
		echo js("js/ajax.js");
		echo js("js/laden.js");
	echo "</head>";
	echo "<body>";
		echo "<div id=\"dshKopfzeileO\">";
			echo "<div id=\"dshKopfzeileI\">";
				echo "<img id=\"dshLogoBild\" src=\"dateien/schulspezifisch/logo.png\">";
				echo "<span id=\"dshLogoSchrift\">";
					echo "<span id=\"dshLogoO\">Schulname</span>";
					echo "<span id=\"dshLogoU\">Schule Ort</span>";	// TODO: Schuldaten
				echo "</span>";
				echo "<ul class=\"dshKopfnavigation\">";
					echo "<li>";
						echo "<div class=\"dshSuche\">";

							echo (new UI\Textfeld("dshSuchePcSuchbegriff", "", "", "Suchen...", new UI\Aktion("onkeyup", "cms_suche_suchen('dshSuche_pc_suchbegriff', 'dshSuche_pc_ergebnisse')")))->ausgabe();
							echo "<div id=\"dshSuche_pc_ergebnisse\">";
								echo "<span class=\"cms_button_nein dshSuche_schliessen\" onclick=\"cms_websuche_schliessen('dshSuche_pc_suchbegriff', 'dshSuche_pc_ergebnisse')\">×</span>";
								echo "<div id=\"dshSuche_pc_ergebnisse_inhalt\">";
									echo "<p class=\"cms_notiz\">Bitte warten...</p>";
								echo "</div>";
							echo "</div>";
						echo "</div>";
					echo "</li>";
					echo "<li>";
						echo (new UI\Schaltflaeche("Website", new UI\Aktion("href", "dshLink('Website')")))->iconknopf("", UI\Konstanten::PERSON);
					echo "</li>";
					echo "<li>";
						echo "<a class=\"cms_button\" href=\"Schulhof\">Schulhof</a>";
					echo "</li>";
				echo "</ul>";
				echo "<div class=\"dsh_clear\"></div>";
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dsh_platzhalter\"></div>";
		echo "<div id=\"dsh_hauptteil_o\">";
			echo "<div id=\"dsh_hauptteil_i\">";
				echo "<p>Seite wird geladen :)</p>";
				echo (new UI\Meldung("Laden ...", "", "laden"))->ausgabe();
				echo (new UI\Meldung("Erfolg ...", "<p>Juhu!</p>", "schutz"))->ausgabe();
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dsh_fusszeile_o\">";
			echo "<div id=\"dsh_fusszeile_i\">";
			echo "</div>";
		echo "</div>";
		?>
		<script>
			window.onload = () => {
				core.seiteLaden('<?php echo $DSH_URLGANZ; ?>');
			}
		</script>
		<?php
	echo "</body>";
echo "</html>";
?>
<!-- Digitaler Schulhof - Version 1.0 -->
