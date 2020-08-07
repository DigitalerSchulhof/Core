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

  // Liefert das mögliche System
  public static function systeminfo() {
    $info = strtolower($_SERVER['HTTP_USER_AGENT']);

    // Browser finden
    if (preg_match("/opera/", $info)) {
      $browser = "Opera";
    } else if (preg_match("/opr/", $info)) {
      $browser = "Opera";
    } else if (preg_match("/chromium/", $info)) {
      $browser = "Chromium";
    } elseif (preg_match("/chrome/", $info)) {
      $browser = "Chrome";
    } elseif (preg_match("/webkit/", $info)) {
      $browser = "Safari";
    } elseif (preg_match("/msie/", $info)) {
      $browser = "Internet Explorer / Edge";
    } elseif (preg_match("/mozilla/", $info) && !preg_match("/compatible/", $info)) {
      $browser = "Firefox";
    } else {
      $browser = "Unbekannter Browser";
    }

    // Browser-Version
    $version = "";
    if ($browser != "Unbekannter Browser") {
      if (preg_match("/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/", $info, $matches)) {
        $version = $matches[1];
      }
    }


    // Betriebssystem
    if (preg_match("/linux/", $info)) {
      $os = "Linux";
    } elseif (preg_match("/macintosh|mac os x/", $info)) {
      $os = "Mac";
    } elseif (preg_match("/windows|win32/", $info)) {
      $os = "Windows";
    } else {
      $os = "Unbekanntes OS";
    }

    return "<span title=\"$info\">$browser $version ($os)</span>";
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

  public static function istLatein($x, $min = 1, $max = null) {
    if (preg_match("/^[-_0-9a-zA-Z]+$/", $x) !== 1) {
      return false;
    }
    $fehler = false;
    if ($min !== null) {
      if (strlen($x) < $min) {
        $fehler = true;
      }
    }
    if ($max !== null) {
      if (strelan($x) > $max) {
        $fehler = true;
      }
    }
  	return !$fehler;
  }

  public static function istText($x, $min = 1, $max = null) {
    if (preg_match("/^[-_ \.@äöüÄÖÜßáéíóúàèìòùÁÉÍÓÚÀÈÌÒÙæÆâêîôûÂÊÎÔÛøØÅÇËÃÏÕãåçëïõÿñ0-9a-zA-Z]*$/", $x) !== 1) {
      return false;
    }
    $fehler = false;
    if ($min !== null) {
      if (strlen($x) < $min) {
        $fehler = true;
      }
    }
    if ($max !== null) {
      if (strelan($x) > $max) {
        $fehler = true;
      }
    }
  	return !$fehler;
  }

  public static function istTitel($x, $min = 0, $max = null) {
    if (preg_match("/^[- \._äöüÄÖÜßáéíóúàèìòùÁÉÍÓÚÀÈÌÒÙæÆâêîôûÂÊÎÔÛøØÅÇËÃÏÕãåçëïõÿñ0-9a-zA-Z]*$/", $x) !== 1) {
      return false;
    }
    $fehler = false;
    if ($min !== null) {
      if (strlen($x) < $min) {
        $fehler = true;
      }
    }
    if ($max !== null) {
      if (strelan($x) > $max) {
        $fehler = true;
      }
    }
  	return !$fehler;
    return true;
  }

  public static function istName($x, $min = 1, $max = null) {
    if (preg_match("/^[- _äöüÄÖÜßáéíóúàèìòùÁÉÍÓÚÀÈÌÒÙæÆâêîôûÂÊÎÔÛøØÅÇËÃÏÕãåçëïõÿñ0-9a-zA-Z]*$/", $x) !== 1) {
      echo "JA1";
      return false;
    }
    $fehler = false;
    if ($min !== null) {
      if (strlen($x) < $min) {
        echo "JA2";
        $fehler = true;
      }
    }
    if ($max !== null) {
      if (strelan($x) > $max) {
        echo "JA3";
        $fehler = true;
      }
    }
    return !$fehler;
  }

  /**
   * Gibt zurück, ob eine Mailadresse gültig ist
   * @param  string $mail :)
   * @return bool
   */
  public static function istMail($mail) : bool {
    if (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]{2,}$/', $mail) != 1) {
  		return false;
  	}
  	return true;
  }

  /**
   * Gibt zurück, ob der übergebene Wert 0 oder 1 ist
   * @param  string $wert :)
   * @return bool
   */
  public static function istToggle($wert) : bool {
    if (preg_match('/^(0|1)$/', $wert) != 1) {
  		return false;
  	}
  	return true;
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

  public static function boese($string) {
  	// onevent
  	if (preg_match("/ [oO][nN][a-zA-Z]* *=[^\\\\]*/", $string)) {return true;}
  	// <script>
  	if (preg_match("/<[sS][cC][rR][iI][pP][tT].*>/", $string)) {return true;}
  	// data:
  	if (preg_match("/=['\"]?data:(application\\/(javascript|octet-stream|zip|x-shockwave-flash)|image\\/(svg\+xml)|text\\/(javascript|x-scriptlet|html)|data\\/(javascript))[;,]/", $string)) {
  		return true;
  	}
  	preg_match_all("/(.[^ ])*[jJ](&.*;)*[aA](&.*;)*[vV](&.*;)*[aA](&.*;)*[sS](&.*;)*[cC](&.*;)*[rR](&.*;)*[iI](&.*;)*[pP](&.*;)*[tT](&.*;)*(:|;[cC][oO][lL][oO][nN])/", $string, $matchjs);
  	preg_match_all("/javascript:cms_download\('([-a-zA-Z0-9]+\/)*[\-\_a-zA-Z0-9]{1,244}\.((tar\.gz)|([a-zA-Z0-9]{2,10}))'\)/", $string, $matchdown);

  	if (count($matchjs[0]) != count($matchdown[0])) {
  		return true;
  	}
  	return false;
  }

  /**
   * Prüft ob die Person im Session-Cookie angemeldet ist
   * @return bool true wenn angemeldet, false sonst
   */
  public static function angemeldet() : bool {
    global $DSH_BENUTZER;
    if(session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $angemeldet = false;
    if (isset($_SESSION["Benutzer"])) {
      $DSH_BENUTZER = $_SESSION["Benutzer"];
      $angemeldet = $DSH_BENUTZER->angemeldet();
    }
    return $angemeldet;
  }

  /**
   * Erstellt aus einem Float-Wert einen Prozentstring
   * @param  float $wert :)
   * @return array ["wert"] enthält den Wert, ["anzeige"] enthält den String, ["style"] enthält den Wert mit %-Zeichen
   */
  public static function prozent($teil, $ganz) : array {
    $rueckgabe = [];
    $rueckgabe["wert"] = ($teil/$ganz)*100;
    $rueckgabe["anzeige"] = str_replace(".", ",", round($rueckgabe["wert"], 2))." %";
    $rueckgabe["style"] = "{$rueckgabe["wert"]}%";
    return $rueckgabe;
  }

  /**
   * Gibt den Speicher in der größtmöglichen Einheit aus
   * @param  int    $bytes :)
   * @return string        :)
   */
  public static function speicher ($bytes) : string {
    if ($bytes/1000 >= 1) {
      $bytes = $bytes/1000;
      if ($bytes/1000 >= 1) {
        $bytes = $bytes/1000;
        if ($bytes/1000 >= 1) {
          $bytes = $bytes/1000;
          if ($bytes/1000 >= 1) {
            $bytes = $bytes/1000;
            if ($bytes/1000 >= 1) {
              $bytes = $bytes/1000;
              if ($bytes/1000 >= 1) {
                $bytes = $bytes/1000;
                $bytes = str_replace('.', ',', round($bytes, 2));
                return $bytes." EB";
              }
              $bytes = str_replace('.', ',', round($bytes, 2));
              return $bytes." PB";
            }
            $bytes = str_replace('.', ',', round($bytes, 2));
            return $bytes." TB";
          }
          $bytes = str_replace('.', ',', round($bytes, 2));
          return $bytes." GB";
        }
        $bytes = str_replace('.', ',', round($bytes, 2));
        return $bytes." MB";
      }
      $bytes = str_replace('.', ',', round($bytes, 2));
      return $bytes." KB";
    }
    return $bytes." B";
  }

  /**
   * Gibt die Zeit in der größtmöglichen Einheit aus
   * @param  int    $bytes :)
   * @return string        :)
   */
  public static function zeit ($sekunden) : string {
    if ($sekunden < 60) {
      return "weniger als eine Minute";
    }
    if ($sekunden / 60 > 1) {
      $minuten = $sekunden / 60;
      if ($minuten / 60 > 1) {
        $stunden = $minuten / 60;
        // Stunden ausgeben
        if (floor($stunden) == 1) {
          return "eine Stunde";
        } else {
          return floor($stunden)." Stunde";
        }
      }
      // MINUTEN AUSGEBEN
      if (floor($minuten) == 1) {
        return "eine Minute";
      } else {
        return floor($minuten)." Minuten";
      }
    }
  }


  /**
   * Prüft den Datenschutzcookie
   * @param  string $typ Typ des Datenschutzcookies
   * @return bool        true, wenn Datenschutz zugestimmt, sonst false
   */
  public static function einwilligung($typ = null) : bool {
    // Datenschutzcookies verwalten
    if (!isset($_COOKIE["EinwilligungDSH"])) {
      setcookie("EinwilligungDSH", "nein", time()+30*24*60*60, "/");
      $_COOKIE["EinwilligungDSH"] = "nein";
    } else {
      if ($_COOKIE["EinwilligungDSH"] == "ja") {
        if(session_status() === PHP_SESSION_NONE) {
          session_start();
        }
      }
    }
    if (!isset($_COOKIE["EinwilligungEXT"])) {
      setcookie("EinwilligungEXT", "nein", time()+30*24*60*60, "/");
      $_COOKIE["EinwilligungEXT"] = "nein";
    }

    $typen = ["DSH", "EXT"];
    if (!in_array($typ, $typen)) {return false;}

    if ($_COOKIE["Einwilligung{$typ}"] == "ja") {
      return true;
    } else {
      return false;
    }
  }
}

?>
