<?php

  Anfrage::post("seite");

  use Core\Einbinden;

  Einbinden::modulLaden("UI", true, false);
  Einbinden::modulLaden("Kern", true, false);

  $rueck = array();

  $urls = Einbinden::seiteBestimmen($seite);
  $DSH_URL = $urls["url"];
  $DSH_URLGANZ = $urls["urlganz"];

  $DSH_TITEL = "$seite";
  $CODE = "";

  Einbinden::seiteEinbinden(explode("/", $seite));

  $rueck["seite"] = (string) $CODE;
  $rueck["daten"] = array(
    "seitentitel" => $DSH_TITEL
  );

  echo json_encode($rueck);
?>