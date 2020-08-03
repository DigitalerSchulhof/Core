<?php
session_start();
if(!Check::angemeldet()) {
  einbinden("Schulhof/Anmeldung");
  return;
}

$DSH_TITEL = "Nutzerkonto";
$CODE .= new Kern\Aktionszeile();

$spalte = new UI\Spalte();
$spalte->add(new UI\SeitenUeberschrift("Willkommen $DSH_BENUTZER!"));

$CODE .= new UI\Zeile($spalte);
?>
