<?php
namespace Core;

class Angebote {
  /**
   * Sucht und gibt alle gefundenen Angebote zurück
   * @param string $platz Die Kennung des Platzes
   * @return mixed[] Alle gefundenen Angebote
   */
  static function angeboteFinden($platz) {
    $angebote = unserialize(file_get_contents(__DIR__."/angebote.core"));

    if(!isset($angebote[$platz])) {
      return array();
    }
    $geboten = array();
    $angebote = $angebote[$platz];
    $ANGEBOT = null;
    foreach($angebote as $modul => $angebot) {
      include __DIR__."/../module/$modul/$angebot";
      if($ANGEBOT !== null) {
        if(is_array($ANGEBOT)) {
          $geboten = array_merge($geboten, $ANGEBOT);
        }
        $geboten[] = $ANGEBOT;
      }
    }

    return $geboten;
  }
}

?>