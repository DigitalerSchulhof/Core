<?php

$DSH_VERSION = "1.0";

$DSH_URLGANZ = $_GET["URL"] ?? "";
$DSH_URL = explode("/", $DSH_URLGANZ);
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";

$DSH_DATENBANKEN = [];

include __DIR__."/core/config.php";
include __DIR__."/core/funktionen.php";
include __DIR__."/core/angebote.php";
include __DIR__."/core/check.php";
include __DIR__."/core/include.php";

use Core\Einbinden;

Einbinden::modulLaden("UI", true, false);
Einbinden::modulLaden("Kern", true, false);

echo "<!DOCTYPE html>";
echo "<html>";
	echo "<head>";
    echo "<base href=\"{$EINSTELLUNGEN["Base"]}\">";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
		echo style("css/layout.css");
		if(isset($_COOKIE["dunkelmodus"])) {
			if($_COOKIE["dunkelmodus"] == "ja") {
				echo style("css/dunkelroh.css");
			} else {
				echo style("css/hell.css");
			}
		} else {
			echo style("css/hell.css");
			// echo style("css/dunkel.css");
		}
		if(/** drucken */ false) {
			echo style("css/drucken.css");
		}
    echo js("js/eQuery.js");
		echo js("js/core.js");
		echo js("js/ajax.js");
		echo js("js/laden.js");
		echo modulJs("Kern");
		echo modulJs("UI");
    echo "<title>Seite wird geladen...</title>";
	echo "</head>";
	echo "<body class=\"dshSeiteP\">";
    echo "<div id=\"dshSeiteladenO\"><div id=\"dshSeiteladenI\"></div></div>";
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
              $suche = new UI\Textfeld("dshSuchePcSuchbegriff");
              $suche->getAktionen()->addFunktion("onkeyup", "dshSucheSuchen('dshSuche_pc_suchbegriff', 'dshSuchePcErgebnisse')");
              echo $suche->setPlatzhalter("Suchen...")->self();
							echo "<div id=\"dshSuchePcErgebnisse\">";
                $schliessen = new UI\MiniIconKnopf(new UI\Icon(Ui\Konstanten::SCHLIESSEN), "Schließen", "Fehler", "UL");
                $schliessen->getAktionen()->addFunktion("onclick", "dhsWebsucheSchliessen('dshSuchePcSuchbegriff', 'dshSuchePcErgebnisse')");
                $schliessen->setID("dshSuchePcSchliessen");
                echo $schliessen;
								echo "<div id=\"dshSuchePcErgebnisseInhalt\">";
									echo "<p class=\"dshNotiz\">Bitte warten...</p>";
								echo "</div>";
							echo "</div>";
						echo "</div>";
					echo "</li>";
					echo "<li>";
            $optWebsite = new UI\Toggleoption("dshKopfnaviWebsite");
            $optWebsite->setText("Website");
            $optWebsite->setWert("website");
            $optWebsite->getAktionen()->addFunktion("href", "Website");
            $optWebsite->getAktionen()->addFunktion("onhref", "core.navigationAnpassen('website')");
            $optWebsite->getAktionen()->addFunktion("onhref", "this.blur()");

            $optSchulhof = new UI\Toggleoption("dshKopfnaviSchulhof");
            $optSchulhof->setText("Schulhof");
            $optSchulhof->setWert("schulhof");
            $optSchulhof->getAktionen()->addFunktion("href", "Schulhof");
            $optSchulhof->getAktionen()->addFunktion("onhref", "core.navigationAnpassen('schulhof')");
            $optSchulhof->getAktionen()->addFunktion("onhref", "this.blur()");

            $kopfnavi = new UI\Togglegruppe("dshKopfnavi");
            $kopfnavi->addOption($optWebsite);
            $kopfnavi->addOption($optSchulhof);

            $kopfnavi->setWert(strtolower($DSH_URL[0]));
            echo $kopfnavi;
					echo "</li>";
				echo "</ul>";
				echo "<div class=\"dshClear\"></div>";
			echo "</div>";
		echo "</div>";
		echo "<div id=\"dshPlatzhalter\"></div>";
		echo "<div id=\"dshHauptteilO\">";
			echo "<div id=\"dshHauptteilI\">";
        $hier = new UI\Link("hier", "https://www.enable-javascript.com/de/", true);
        echo UI\Zeile::standard((new UI\Meldung("Inkompatibel", "JavaScript ist deaktiviert! Um den Digitalen Schulof zu nutzen, muss JavaScript aktiv sein.<br>Wie Sie dieses aktivieren, erfahren Sie $hier.", "Fehler")))->setTag("noscript");
        echo UI\Zeile::standard((new UI\Meldung("Bitte warten", "Der Digitale Schulhof wird geladen...", "Arbeit")))->setID("dshMeldungInitial")->setStyle("display", "none");
        echo UI\Zeile::standard((new UI\Meldung("Fehler", "Bei der Anfrage ist ein unbekannter Fehler aufgetreten: <pre></pre", "Fehler")))->setID("dshFehlerbox")->setStyle("display", "none");
        echo "<div id=\"dshSeite\"></div>";
      echo "</div>";
		echo "</div>";
		echo "<div id=\"dshFusszeileO\">";
			echo "<div id=\"dshFusszeileI\">";
			echo "</div>";
		echo "</div>";
    echo "<div id=\"dshNetzcheck\">";
      echo "<a style=\"font-family: inherit; color: inherit; font-size: inherit;\" target=\"_blank\" tabindex=\"0\" class=\"dshExtern\" href=\"https://github.com/DigitalerSchulhof\">Digitaler Schulhof – Version $DSH_VERSION – Website – Schulhof"./**" – Lehrerzimmer"*/"</a>";
      echo "<p>Offline!<br>Der Digitale Schulhof benötigt eine Internetverbindung.</p>";
    echo "</div>";

    echo "<div id=\"dshBlende\">";
      echo "<div id=\"dshBlendeI\">";
        $laden = new UI\Fenster("dshLaden", "WIRD ÜBERSCHRIEBEN", "BLA");
        $laden->setSchliessen(false);
        echo $laden;
      echo "</div>";
    echo "</div>";
		echo "<script>";
			echo "window.onload=()=>{core.seiteLaden('$DSH_URLGANZ', false);$('#dshMeldungInitial').einblenden();}";
		echo "</script>";
	echo "</body>";
echo "</html>";
?>
<!-- Digitaler Schulhof - Version <?php echo $DSH_VERSION?> -->
<!-- 🍪 -->
