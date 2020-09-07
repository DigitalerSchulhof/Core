<?php

$DSH_VERSION = "1.0";

$DSH_URLGANZ = $_GET["URL"] ?? "";
if(substr($DSH_URLGANZ, -1) === "/") {
  $DSH_URLGANZ = substr($DSH_URLGANZ, 0, -1);
}
$DSH_URL = explode("/", $DSH_URLGANZ);
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";

$DSH_DATENBANKEN = [];

include __DIR__."/core/config.php";
include __DIR__."/core/funktionen.php";
include __DIR__."/core/angebote.php";
include __DIR__."/core/include.php";

use Core\Einbinden;

Einbinden::modulLaden("UI", true, false);
Einbinden::modulLaden("Kern", true, false);

Kern\DB::log();

// Datenschutzcookies verwalten
Kern\Check::einwilligung();


echo "<!DOCTYPE html>";
echo "<html lang=\"de\">";
	echo "<head>";
    echo "<base href=\"{$EINSTELLUNGEN["Base"]}\">";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
    echo "<meta charset=\"UTF-8\">";
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
    echo "<title>Digitaler Schulhof - Seite wird geladen...</title>";
	echo "</head>";
	echo "<body>";
    echo "<div id=\"dshSeiteladenO\"><div id=\"dshSeiteladenI\"></div></div>";
		echo "<div id=\"dshKopfzeileO\">";
			echo "<div id=\"dshKopfzeileI\">";
				echo "<img id=\"dshLogoBild\" src=\"dateien/schulspezifisch/logo.png\">";
				echo "<span id=\"dshLogoSchrift\">";
          $SCHULDATEN = Kern\Einstellungen::ladenAlle("Kern");
					echo "<span id=\"dshLogoO\">{$SCHULDATEN["Schulname"]}</span>";
					echo "<span id=\"dshLogoU\">{$SCHULDATEN["Schulort"]}</span>";
				echo "</span>";
				echo "<ul class=\"dshKopfnavigation\">";
					echo "<li>";
						echo "<div class=\"dshSuche\">";
              $suche = new UI\Textfeld("dshSuchePcSuchbegriff");
              $suche->addFunktion("oninput", "dshSucheSuchen('dshSuche_pc_suchbegriff', 'dshSuchePcErgebnisse')");
              echo $suche->setPlatzhalter("Suchen...")->self();
							echo "<div id=\"dshSuchePcErgebnisse\">";
                $schliessen = new UI\MiniIconKnopf(new UI\Icon(Ui\Konstanten::SCHLIESSEN), "Schließen", "Fehler", "UL");
                $schliessen->addFunktion("onclick", "dhsWebsucheSchliessen('dshSuchePcSuchbegriff', 'dshSuchePcErgebnisse')");
                $schliessen->setID("dshSuchePcSchliessen");
                echo $schliessen;
								echo "<div id=\"dshSuchePcErgebnisseInhalt\">";
                  echo new UI\Notiz("Bitte warten...");
								echo "</div>";
							echo "</div>";
						echo "</div>";
					echo "</li>";
					echo "<li>";
            $optWebsite = new UI\Toggleoption("dshKopfnaviWebsite");
            $optWebsite->setText("Website");
            $optWebsite->setWert("website");
            $optWebsite->addFunktion("href", "");
            $optWebsite->addFunktion("onhref", "this.blur()");

            $optSchulhof = new UI\Toggleoption("dshKopfnaviSchulhof");
            $optSchulhof->setText("Schulhof");
            $optSchulhof->setWert("schulhof");
            $optSchulhof->addFunktion("href", "Schulhof");
            $optSchulhof->addFunktion("onhref", "this.blur()");

            $kopfnavi = new UI\Togglegruppe("dshKopfnavi");
            $kopfnavi->addOption($optWebsite);
            $kopfnavi->addOption($optSchulhof);
            $bereich = $DSH_URL[0] ?? "Website";
            if($bereich == "") {
              $bereich = "Website";
            }
            $kopfnavi->setWert(strtolower($bereich));
            echo $kopfnavi;
					echo "</li>";
				echo "</ul>";
        echo "<div id=\"dshHauptnavigation\">";
        class NavigationReiter extends UI\Reiter {
          public function __toString() : string {
            $this->gewaehlt = -1;
            return parent::__toString();
          }
        }
        echo (new NavigationReiter("dshHauptnavigationReiter"))->addReitersegment(new UI\Reitersegment(new UI\Reiterkopf("Navigation wird geladen", new UI\Icon("fas fa-clock"))));
        echo "</div>";
				echo "<div class=\"dshClear\"></div>";
			echo "</div>";
		echo "</div>";
    echo "<div id=\"dshFenstersammler\"></div>";
		echo "<div id=\"dshHauptteilO\">";
			echo "<div id=\"dshHauptteilI\">";
        $hier = new UI\Link("hier", "https://www.enable-javascript.com/de/", true);
        echo UI\Zeile::standard((new UI\Meldung("Inkompatibel", "JavaScript ist deaktiviert! Um den Digitalen Schulof zu nutzen, muss JavaScript aktiv sein.<br>Wie Sie dieses aktivieren, erfahren Sie $hier.", "Fehler")))->setTag("noscript");
        echo UI\Zeile::standard((new UI\Meldung("Fehler", "Bei der Anfrage ist ein unbekannter Fehler aufgetreten: <pre></pre", "Fehler")))->setID("dshFehlerbox")->setStyle("display", "none");
        echo "<div id=\"dshSeite\">";
          echo (new Kern\Aktionszeile(true, false))->setBrotkrumenPfad(array($DSH_URLGANZ => "Digitaler Schuhlhof"));
          echo "<i></i>"; // Hack, sodass p:last-child nicht greift, und mb fälschlicherweise auf 0 setzt
        echo "</div>";
        echo UI\Zeile::standard((new UI\Meldung("Bitte warten", "Der Digitale Schulhof wird geladen...", "Arbeit")))->setID("dshMeldungInitial")->setStyle("display", "none");
      echo "</div>";
		echo "</div>";
		echo "<div id=\"dshFusszeileO\">";
			echo "<div id=\"dshFusszeileI\">";
			echo "</div>";
		echo "</div>";
    echo "<div id=\"dshNetzcheck\">";
      echo "<a style=\"font-family: inherit; color: inherit; font-size: inherit;\" target=\"_blank\" tabindex=\"0\" class=\"dshExtern\" href=\"https://github.com/DigitalerSchulhof\" rel=\"noopener\">Digitaler Schulhof – Version $DSH_VERSION – Website – Schulhof"./**" – Lehrerzimmer"*/"</a>";
      echo "<p>Offline!<br>Der Digitale Schulhof benötigt eine Internetverbindung.</p>";
    echo "</div>";

    echo (new UI\Meldung("Fenster wird geladen...", null, "Laden"))->setID("dshUiFensterLadesymbol");

    echo "<div id=\"dshBlende\">";
      echo "<div id=\"dshBlendeI\">";
        $laden = new UI\Fenster("dshLaden", "WIRD ÜBERSCHRIEBEN", "BLA", false);
        $laden->setSchliessen(false);
        $laden->setMinimieren(false);
        echo $laden;
      echo "</div>";
    echo "</div>";

		echo "<script>";
			echo "window.onload=()=>{core.seiteLaden('$DSH_URLGANZ', false)};document.querySelector('#dshMeldungInitial').style.display='block';";
		echo "</script>";
	echo "</body>";
echo "</html>";
?>
<!-- Digitaler Schulhof - Version <?php echo $DSH_VERSION?> -->
<!-- 🍪 -->
