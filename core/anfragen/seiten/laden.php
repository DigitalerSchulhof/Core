<?php

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

  if(Anfrage::getTyp() === null) {
    Anfrage::setTyp("Seite");
    Anfrage::setRueck("Titel",  $DSH_TITEL);
    Anfrage::setRueck("Code",   $CODE);
  }
?>
