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

  public static function istZahl($x, $min = null, $max = null) {
    if (preg_match("/^[0-9]+$/", $x) !== 1) {
      return false;
    }
    $fehler = false;
    if ($min !== null) {
      if ($x < $min) {
        $fehler = true;
      }
    }
    if ($max !== null) {
      if ($x > $max) {
        $fehler = true;
      }
    }
  	return !$fehler;
  }

  public static function fuehrendeNull($x) {
    $check = new Check();
  	if ($check->istZahl($x)) {
  		if (strlen($x) < 2) {
  			return "0".$x;
  		} else {
  			return $x;
  		}
  	} else {
  		return false;
  	}
  }
}

?>
