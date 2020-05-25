<?php
namespace DB\Anfrage;

/**
* Eine Datenbankanfrage
*/
class Anfrage implements \Iterator {
  /** @var array Enthält ob die Anfrage erfolgreich war */
  private $erfolg;
  /** @var int Enthält die Anzahl an beeinflussten Zeilen */
  private $anzahl;
  /** @var array Enthält das Ergebnis */
  private $ergebnis;
  /** @var int Position des Iterators */
  private $position;

	/**
	* @param boolean $erfolg Ob die Anfrage erfolgreich war
	* @param int $anzahl Anzahl an Ergebnissen (affected_rows)
	* @param array $ergebnis Die Ergebnisse
	*/
  public function __construct($erfolg = true, $anzahl = 0, $ergebnis = array()) {
    $this->erfolg = $erfolg;
  	$this->anzahl = $anzahl;
    $this->ergebnis = $ergebnis;
    $this->position = 0;
  }

  /**
	* @return bool gibt zurück, ob die Anfrage erfolgreich war
	*/
  public function getErfolg() : bool {
    return $this->erfolg;
  }

  /**
	* @return int gibt zurück, wie viele Datenreihen verändert wurden
	*/
  public function getAnzahl() : int {
    return $this->anzahl;
  }

  /**
	* @return array gibt die Ergebnisse der Anfrage zurück
	*/
  public function getErgebnis() : array {
    return $this->ergebnis;
  }

  /**
  * Iterable-Interface
  */
  public function rewind() {
    $this->position = 0;
  }

  public function current() {
    return $this->ergebnis[$this->position];
  }

  public function key() : int {
    return $this->position;
  }

  public function next() {
    $this->position++;
  }

  public function valid() : bool {
    return isset($this->ergebnis[$this->position]);
  }

}
?>