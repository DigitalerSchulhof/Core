<?php

  function einbinden($seite) {
    global $CODE, $DSH_TITEL, $DSH_URL, $DSH_URLGANZ, $EINSTELLUNGEN;

    $urls = Core\Einbinden::seiteBestimmen($seite);
    $DSH_URL = $urls["url"];
    $DSH_URLGANZ = $urls["urlganz"];

    $DSH_TITEL = "$seite";
    $CODE = "";

    Core\Einbinden::seiteEinbinden(explode("/", $seite));
  }

  Anfrage::post("seite");

  Core\Einbinden::modulLaden("UI", true, false);
  Core\Einbinden::modulLaden("Kern", true, false);

  $rueck = [];

  $urls = Core\Einbinden::seiteBestimmen($seite);
  $DSH_URL = $urls["url"];
  $DSH_URLGANZ = $urls["urlganz"];

  $DSH_TITEL = "$seite";
  $CODE = "";

  ob_start();
  Core\Einbinden::seiteEinbinden(explode("/", $seite));

  $CODE = ob_get_contents().$CODE;
  ob_end_clean();

  Anfrage::setTyp("Seite");
  Anfrage::setRueck("Titel", $DSH_TITEL);
  Anfrage::setRueck("Code", (string) $CODE);
?>
