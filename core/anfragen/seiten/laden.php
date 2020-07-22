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

  ob_start();
  Einbinden::seiteEinbinden(explode("/", $seite));

  $CODE = ob_get_contents().$CODE;
  ob_end_clean();

  $rueck["seite"] = (string) $CODE;
  $rueck["daten"] = array(
    "seitentitel" => $DSH_TITEL
  );
  $r = json_encode($rueck);
  $ln = strlen($r);
  header("Content-length: $ln");
  echo $r;
?>