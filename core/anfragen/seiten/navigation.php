<?php
Anfrage::post("bereich");

include_once "$DSH_MODULE/Kern/klassen/verwaltungselemente.php";

use Kern\Verwaltung\Liste;
use Kern\Verwaltung\Kategorie;

class NavigationReiter extends UI\Reiter {
  public function __toString() : string {
    $this->gewaehlt = -1;
    return parent::__toString();
  }
}

$hauptreiter = new NavigationReiter("dshHauptnavigationReiter");

if($bereich === "Schulhof") {
  if(!Kern\Check::angemeldet()) {
    Anfrage::addFehler(-4, true);
  }

  // Verwaltung
  new Kern\Wurmloch("funktionen/verwaltung/elemente.php");

  $reiter = new UI\Reiter("dshHauptnavigationVerwaltung");
  foreach(Liste::getKategorien() as $kat) {
    if(!($kat instanceof \Kern\Verwaltung\Kategorie)) {
      throw new Exception("Die Kategorie ist ungültig");
    } else {
      if(count($kat->getElemente()) > 0) {
        $kopf     = new UI\Reiterkopf($kat->getTitel());
        $spalte   = new UI\Spalte("A1");
        $spalte   ->addKlasse("dshUiOhnePadding");
        foreach($kat->getElemente() as $elm) {
          $art = null;
          if($elm->istFortgeschritten()) {
            $art = "Warnung";
          }
          $knopf = new UI\IconKnopf($elm->getIcon(), $elm->getName(), $art);
          $knopf ->addFunktion("href", $elm->getZiel());
          $spalte[] = "$knopf ";
        }
        $koerper  = new UI\Reiterkoerper($spalte);
        $reiter[] = new UI\Reitersegment($kopf, $koerper);
      }
    }
  }
  if(count($reiter->getReitersegmente()) > 0) {
    $kopf    = new UI\Reiterkopf("Verwaltung");
    $koerper = new UI\Reiterkoerper(new UI\Spalte("A1", $reiter));
    $kopf    ->addFunktion("onmouseenter", "kern.navigation.einblenden(this)");
    $kopf    ->addFunktion("onmouseleave", "kern.navigation.ausblenden(this)");
    $koerper ->addFunktion("onmouseenter", "kern.navigation.einblenden(this)");
    $koerper ->addFunktion("onmouseleave", "kern.navigation.ausblenden(this)");
    $hauptreiter[] = new UI\Reitersegment($kopf, $koerper);
  }
}

Anfrage::setRueck("Navigation", (string) $hauptreiter);

?>