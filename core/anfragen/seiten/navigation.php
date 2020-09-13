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
  Core\Einbinden::modulLaden("Website");
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

  $sqlStatus = "";
  if(!Kern\Check::angemeldet() || !$DSH_BENUTZER->hatRecht("website.inhalte.versionen.[|alt,neu].[|sehen,aktivieren] || website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) {
    $sqlStatus = " AND status = 'a'";
  }
  $anf = $DBS->anfrage("SELECT ws.id, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))}, {(SELECT COALESCE(wsd.bezeichnung, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND ws.zugehoerig IS NULL$sqlStatus", "s", $bereich);
  while($anf->werte($id, $pfad, $bezeichnung)) {
    $pfad     = Kern\Texttrafo::text2url($pfad);
    $kopf     = new UI\Reiterkopf($bezeichnung);
    $spalte   = new UI\Spalte();
    if ((Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) || Website\Seite::sichtbar($id, $bereich)) {
      $kopf     ->addFunktion("href", "$sprache$pfad");
      $kopf     ->setTag("a");
    }
    // Unterseiten laden
    $anff = $DBS->anfrage("SELECT ws.id, {(SELECT COALESCE(wsd.pfad, COALESCE(wsd.bezeichnung, (SELECT COALESCE(wsds.pfad, wsds.bezeichnung) FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0))))))}, {(SELECT COALESCE(wsd.bezeichnung, (SELECT wsds.bezeichnung FROM website_seitendaten as wsds WHERE wsds.seite = ws.id AND wsds.sprache = (SELECT id FROM website_sprachen as wsp WHERE wsp.a2 = (SELECT wert FROM website_einstellungen WHERE id = 0)))))} FROM website_seiten as ws JOIN website_sprachen as wsp LEFT JOIN website_seitendaten as wsd ON wsd.seite = ws.id AND wsd.sprache = wsp.id WHERE wsp.id = (SELECT id FROM website_sprachen WHERE a2 = [?]) AND ws.zugehoerig = ?$sqlStatus", "si", $bereich, $id);
    while($anff->werte($unterid, $pf, $bez)) {
      if ((Kern\Check::angemeldet() && $DSH_BENUTZER->hatRecht("website.inhalte.elemente.[|anlegen,bearbeiten,löschen]")) || Website\Seite::sichtbar($unterid, $bereich)) {
        $direkt  = new UI\IconKnopf(new UI\Icon("fas fa-globe"), $bez);
        $direkt  ->addFunktion("href", "$sprache$pfad/$pf");
        $spalte[] = "$direkt ";
      }
    }
    if(count($spalte->getElemente()) > 0) {
      $koerper  = new UI\Reiterkoerper($spalte);
    } else {
      $koerper  = new UI\Reiterkoerper();
    }
    if(count($koerper->getSpalten()) > 0 || $kopf->getTag() == "a") {
      // Hat Funktion (Selbst Klick oder Kinder)
      $hauptreiter[] = new UI\Reitersegment($kopf, $koerper);
    }
  }
}

$hauptreiter->addFunktion("onclick", "kern.navigation.ausblenden(true, event)");

Anfrage::setRueck("Navigation", (string) $hauptreiter);

?>