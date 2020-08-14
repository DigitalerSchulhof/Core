<?php

  class Seite {
    /**
     * Prüft, ob der aktuelle Benutzer angemeldet ist, und gibt eine Fehlermeldung aus wenn nicht
     */
    public static function checkAngemeldet() {
      if(!\Kern\Check::angemeldet()) {
        self::seiteAus("Schulhof/Anmeldung");
      }
    }

    /**
     * Gibt eine Seite aus und beendet das Skript
     * @param string $seite :)
     */
    public static function seiteAus($seite) {
      global $DSH_TITEL, $CODE;
      einbinden($seite);
      Anfrage::setRueck("Titel",  $DSH_TITEL);
      Anfrage::setRueck("Code",   $CODE);
      Anfrage::ausgeben();
      die;
    }

    /**
     * Gibt eine 404-Fehlermeldung aus und beendet das Skript
     */
    public static function nichtGefunden() {
      self::seiteAus("Fehler/404");
    }
  }

  function einbinden($seite) {
    global $CODE, $DSH_TITEL, $DSH_URL, $DSH_URLGANZ;

    $urls = Core\Einbinden::seiteBestimmen($seite);
    $DSH_URL = $urls["url"];
    $DSH_URLGANZ = $urls["urlganz"];

    $DSH_TITEL = "$seite";
    $SEITE = Core\Einbinden::seiteEinbinden(explode("/", $seite));
    if($SEITE === false) {
      einbinden("Fehler/404");
      return;
    }
    $DSH_TITEL = $SEITE->getTitel();
    $CODE = (string) $SEITE;
  }

  Anfrage::post("seite");

  Core\Einbinden::modulLaden("UI", true, false);
  Core\Einbinden::modulLaden("Kern", true, false);

  $rueck = [];

  $CODE;
  einbinden($seite);

  Anfrage::setRueck("Titel",  $DSH_TITEL);
  Anfrage::setRueck("Code",   $CODE);
?>
