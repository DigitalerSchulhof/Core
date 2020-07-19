<?php

class Check {
  /**
   * Gibt zurück, ob ein Datum gültig ist
   * @param  string $datum :)
   * @return bool
   */
  public static function istDatum($datum) : bool {
    return self::macheDatum($datum) !== false;
  }

  /**
   * Gibt mktime eines Datums zurück
   * @param  string $datum :)
   * @return int|false
   */
  public static function macheDatum($datum) {
    $d = explode(".", $datum);
    if (count($d) !== 3) {return false;}
    $tag   = $d[0];
    $monat = $d[1];
    $jahr  = $d[2];
    return mktime(0, 0, 0, $monat, $tag, $jahr);
  }
}

?>