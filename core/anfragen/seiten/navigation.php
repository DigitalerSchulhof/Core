<?php
Anfrage::post("bereich");

class NavigationReiter extends UI\Reiter {
  public function __toString() : string {
    $this->gewaehlt = -1;
    return parent::__toString();
  }

  public function addReitersegment(...$seg) : self {
    foreach($seg as $s) {
      $kopf    = $s->getKopf();
      $koerper = $s->getKoerper();
      // Aktiv deaktivieren
      $kopf    ->setKannAktiv(false);
      $kopf    ->addFunktion("onmouseenter", "kern.navigation.einblenden(this)");
      $kopf    ->addFunktion("onmouseleave", "kern.navigation.ausblenden(this)");
      $koerper ->addFunktion("onmouseenter", "kern.navigation.einblenden(this)");
      $koerper ->addFunktion("onmouseleave", "kern.navigation.ausblenden(this)");
    }
    return parent::addReitersegment(...$seg);
  }
}

$hauptreiter = new NavigationReiter("dshHauptnavigationReiter");

if($bereich === "Schulhof") {
  if(Kern\Check::angemeldet()) {
    include_once "$DSH_MODULE/Kern/klassen/verwaltungselemente.php";
    new Kern\Wurmloch("funktionen/navigation.php", array(),
    /**
     * @param UI\Reitersegment $r
     */
    function($r) use (&$hauptreiter) {
      if($r === null) {
        return;
      }
      $hauptreiter->addReitersegment(...$r);
    });

    // Verwaltung
    new Kern\Wurmloch("funktionen/verwaltung/elemente.php");

    $reiter = new UI\Reiter("dshHauptnavigationVerwaltung");
    foreach(Kern\Verwaltung\Liste::getKategorien() as $kat) {
      if(!($kat instanceof Kern\Verwaltung\Kategorie)) {
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
      $kopf    = new UI\Reiterkopf("Verwaltung", new UI\Icon(UI\Konstanten::VERWALTUNGSBEREICH));
      $direkt  = new UI\IconKnopf(new UI\Icon(UI\Konstanten::VERWALTUNGSBEREICH), "Verwaltungsübersicht");
      $direkt  ->addFunktion("href", "Schulhof/Verwaltung");
      $koerper = new UI\Reiterkoerper(new UI\Spalte("A1", $reiter, $direkt));
      $kopf    ->addFunktion("href",         "Schulhof/Verwaltung");
      $kopf    ->setTag("a");
      $hauptreiter[] = new UI\Reitersegment($kopf, $koerper);
    }
  }
} else {
  if(!$DBS->existiert("website_sprachen", "a2 = [?]", "s", $bereich)) {
    $bereich = "DE";
  }
  $DBS->anfrage("SELECT {wert} FROM website_einstellungen WHERE id = 0")
        ->werte($standard);
  $sprache = "";
  if($bereich != $standard) {
    $sprache = "$bereich/";
  }
  // Bereich ist Sprache
  $anf = $DBS->anfrage("SELECT ws.id, {(SELECT IF(wsd.pfad IS NULL, (SELECT wsds.pfad FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.pfad))}, {(SELECT IF(wsd.bezeichnung IS NULL, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))), wsd.bezeichnung))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND ws.zugehoerig IS NULL", "s", $bereich);
  while($anf->werte($id, $pfad, $bezeichnung)) {
    $kopf     = new UI\Reiterkopf($bezeichnung);
    $koerper  = new UI\Reiterkoerper();
    $kopf     ->addFunktion("href", "$sprache$pfad");
    $kopf     ->setTag("a");
    $hauptreiter[] = new UI\Reitersegment($kopf, $koerper);
  }
}

$hauptreiter->addFunktion("onclick", "kern.navigation.ausblenden(true, event)");

Anfrage::setRueck("Navigation", (string) $hauptreiter);

?>