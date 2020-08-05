<?php

  function einbinden($seite) {
    global $CODE, $DSH_TITEL, $DSH_URL, $DSH_URLGANZ, $EINSTELLUNGEN;

    $urls = Core\Einbinden::seiteBestimmen($seite);
    $DSH_URL = $urls["url"];
    $DSH_URLGANZ = $urls["urlganz"];

    $DSH_TITEL = "$seite";

    // Anonyme Klasse, sodass <code>$CODE[] = $element</code> zu string castet
    $CODE = new class implements \ArrayAccess {
      /** @var string Der HTML-Code */
      private $code = "";

      public function __toString() : string {
        return $this->code;
      }

      /*
       * ArrayAccess Methoden
       */

      public function offsetSet($o, $v) {
        if(!is_null($o)) {
          throw new \Exception("Nicht implementiert!");
        }
        $this->code .= (string) $v;
      }

      public function offsetExists($o) {
        throw new \Exception("Nicht implementiert!");
      }

      public function offsetUnset($o) {
        throw new \Exception("Nicht implementiert!");
      }

      public function offsetGet($o) {
        throw new \Exception("Nicht implementiert!");
      }
    };

    Core\Einbinden::seiteEinbinden(explode("/", $seite));
  }

  Anfrage::post("seite");

  Core\Einbinden::modulLaden("UI", true, false);
  Core\Einbinden::modulLaden("Kern", true, false);

  $rueck = [];

  $CODE;
  einbinden($seite);

  if(Anfrage::getTyp() === null) {
    Anfrage::setTyp("Seite");
    Anfrage::setRueck("Titel",  $DSH_TITEL);
    Anfrage::setRueck("Code",   $CODE);
  }
?>
