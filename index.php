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

							echo (new UI\Textfeld("dshSuchePcSuchbegriff", "", "", "Suchen...", new UI\Aktion("onkeyup", "dshSucheSuchen('dshSuche_pc_suchbegriff', 'dshSuchePcErgebnisse')")))->ausgabe();
							echo "<div id=\"dshSuchePcErgebnisse\">";
								echo (new UI\Knopf("Schließen", new UI\Aktion("onclick", "dhsWebsucheSchliessen('dshSuchePcSuchbegriff', 'dshSuchePcErgebnisse')"), new UI\Icon(Ui\Konstanten::SCHLIESSEN)))->ausgabe("m", "fehler", "UL");
								echo "<div id=\"dshSuchePcErgebnisseInhalt\">";
									echo "<p class=\"dshNotiz\">Bitte warten...</p>";
								echo "</div>";
							echo "</div>";
						echo "</div>";
					echo "</li>";
					echo "<li>";
						echo (new UI\Knopf("Website", new UI\Aktion("href", "Website")))->ausgabe();
					echo "</li>";
					echo "<li>";
						echo (new UI\Knopf("Schulhof", new UI\Aktion("href", "Schulhof")))->ausgabe();
					echo "</li>";
				echo "</ul>";
				echo "<div class=\"dshClear\"></div>";
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dshPlatzhalter\"></div>";
		echo "<div id=\"dshHauptteilO\">";
			echo "<div id=\"dshHauptteilI\">";
				$meldungladen = (new UI\Meldung("Laden ...", "<p>Die Seite wird geladen :)</p>", "laden"))->ausgabe();
				$meldungtest = (new UI\Meldung("Erfolg ...", "<p>Juhu!</p>", "erfolg"))->ausgabe();
				$toggle = new UI\Togglegruppe("wahl", "TEST", "test", "bla");
				$toggle->dazu("Quatsch", "bla");
				$test = "<p>".($toggle)->ausgabe()."</p>";
				$spalte1 = new UI\Spalte($meldungladen);
				$spalte1->dazu($meldungtest);
				$spalte1->dazu($test);
				echo $spalte1->ausgabe();
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dshFusszeileO\">";
			echo "<div id=\"dshFusszeileI\">";
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
