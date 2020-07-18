## Modul<span id="Modul"></span>
Der Name eines Moduls wird mit `[A-Za-z0-9]{2,16}` beschrieben. Die Ordnerstruktur sowie die Modulkonfiguration ist im Modul [Proto](https://github.com/DigitalerSchulhof/Proto) festgelegt. Module sind in `/module/` gespeichert. Werden Änderungen an der **Konfiguration**, den **Stylesheets**, der **Seitenliste**, den **Einstellungsmöglichkeiten** und den **Rechten** vorgenommen, muss mithilfe `/dev.php` das Modul neu keimen.
### Nutzung<span id="ModulNutzung"></span>
#### Installation<span id="ModulNutzungInstallation"></span>
Bei der Installation eines Moduls über den Digitalen Schulhof wird zunächst automatisch der Speicherort des Moduls (siehe: [Modulkonfiguration](#)) angelegt. Das Modul wird in der Datenbank registriert. Daraufhin wird `/module/MODUL/funktionen/installation.php` ausgeführt. In dieser Datei sind sämtliche neue Strukturen (Styles in der Datenbank, eigene Tabellen in der Datenbank, ...) zu hinterlegen.
#### Deinstallation<span id="ModulNutzungInstallation"></span>
Bei der Deinstallation eines Moduls wird zunächst `/module/MODUL/funktionen/deinstallation.php` ausgeführt. Danach wird automatisch der Speicherort (Siehe: Modulkonfiguration) aufgeräumt, zugehörige Variablen in der Styles-Tabelle in der Datenbank entfernt. Zuletzt werden alle Moduldateien in `/module/MODUL/` unwiederruflich gelöscht.
### Technik<span id="ModulTechnik"></span>
#### Konfiguration<span id="ModulTechnikKonfiguration"></span>
Die Modulkonfiguration ist in `/module/MODUL/modul.yml` in YAML und in `/module/MODUL/modul.core` als *serialized*-String gespeichert. Änderungen müssen lediglich in `modul.yml` vorgenommen werden, und über `/dev.php` in `.core`-Art umgewandelt werden.
#### Stylesheets<span id="ModulTechnikStylesheets"></span>
Alle Dateien in `/module/MODUL/styles/` werden als Stylesheet interpretiert.<!---In diesen Dateien kann PHP-Code ausgeführt werden, um so das Layout dynamisch nach Nutzereingaben zu gestalten. --> Diese Dateien müssen gültigen CSS-Code enthalten. Die Stylevariablen werden über `@VARIABLE` erreicht und sind in der Datenbank in der Tabelle `kern_styles` gespeichert. Für passende Syntaxhervorhebung kann die Datei als `.less` gespeichert werden. **Sie muss dennoch gültigen CSS-Code enthalten!** Bei der Verfassung des CSS-Codes ist darauf zu achten, dass eine strikte Trennung zwischen der farblichen Gestaltung und der des Layouts vorliegt.
##### // LAYOUT;<span id="ModulTechnikStylesheetsLayout"></span>
Mit `// LAYOUT;` wird der Layout-Sektor begonnen. Sämtlicher CSS-Code, der bis zum nächsten Sektor oder Dateiende folgt, wird in die globale Datei `/css/layout.css` geschrieben und immer eingebunden. Inhalte der Variablen werden vom hellen Anstrich genommen.
##### // MOBIL;<span id="ModulTechnikStylesheetsMobil"></span>
Mit `// MOBIL;` wird der Mobil-Sektor begonnen. Sämtlicher CSS-Code, der bis zum nächsten Sektor oder Dateiende folgt, wird in die globale Datei `/css/mobil.css` geschrieben und immer eingebunden. Der Code ist jedoch vom `@media`-Selektoren für schmale Geräte umgeben, sodass diese nur auf Handys etc. wirkt. Inhalte der Variablen werden vom hellen Anstrich genommen.
##### // FARBEN;<span id="ModulTechnikStylesheetsFarben"></span>
Mit `// FARBEN;` wird der Farb-Sektor begonnen. Sämtlicher CSS-Code, der bis zum nächsten Sektor oder Dateiende folgt, wird in die globalen Dateien `/css/hell.css`, `/css/dunkel.css` und `/css/dunkelroh.css` geschrieben. Der CSS-Code wird von je keinem, `@media` für Geräte mit dem Dunkelmodus und keinem umklammert. Ist vom Nutzer der helle oder dunkle Anstrich gewählt (Cookie), wird entsprechend nur `/css/hell.css` bzw. `/css/dunkelroh.css` eingebunden. Ist diese Präferenz nicht gesetzt, wird `/css/hell.css` und `/css/dunkel.css` eingebunden, sodass der Browser entscheidet, welcher Anstrich gewählt wird. Inhalte der Variablen für `/css/hell.css` werden vom hellen Anstrich gewählt, für `/css/dunkel.css` und `/css/dunkelroh.css` wird der dunkle Anstrich gewählt. Ist kein dunkler Wert definiert, wird auf den hellen zurückgefallen.
##### // DRUCKEN;<span id="ModulTechnikStylesheetsDrucken"></span>
Mit `// DRUCKEN;` wird der Drunk-Sektor begonnen. Sämtlicher CSS-Code, der bis zum nächsten Sektor oder Dateiende folgt, wird in die globale Datei `/css/drucken.css` geschrieben und **nur** bei der Druckansicht eingebunden. Inhalte der Variablen werden vom hellen Anstrich genommen.

## Styleguide<span id="Styleguide"></span>
### Allgemeines<span id="StyleguideAllgemeines"></span>
Einrückungen betragen 2 Leerzeichen. Datien sind mit der Zeilenendung LF (`\n`) enkodiert. *Windows-CLRF ist doof und untersagt :)* Ein Zeilenvorschub ist nicht zulässig.

Für Zeilenlängen wird kein Limit festgelegt. Jedoch insbesondere außerhalb von Code generierenden Zeilen sollte das empfohlene Zeilenlängenlimit von **80** Zeichen nicht überschritten werden.

Die Verwendung von Ternary ist untersagt!

#### Entwicklungssprache<span id="StyleguideAllgemeinesEntwicklungssprache"></span>
Die Entwicklungssprache des Digitalen Schulhofs ist Deutsch. Umlaute sowie ß sind in Variablennamen, CSS-Klassen, etc. sind untersagt. Außnahmen zum Deutschen bilden die folgenden englische Begriffe, die sich mehr oder minder als Eigennamen in der Programmierwelt etabliert haben:
- `get`
- `set`
- `add`
- `remove`

### Versionierung<span id="StyleguideVersionierung"></span>
Die Versionierung erfolgt jeweils innerhalb eines Moduls. Der Gesamte Digitale Schulhof erhält damit keine eigene einheitliche Versionsnummer.

Die Versionierung erfolgt in einem drei-Bereiche System.
- Die erste Stelle dient der Angabe großer Neuerungen
- Die zweite Stelle gibt kleinere Änderungen und ggf. Ergänzungen einzelner Funktionalitäten an
- Die dritte Stelle enthält BugFixes.

**Beispiel: 4.7.38**\
Release **4** des Moduls\
Neuerungen bis zu Version **7**\
Mit **38** veröffentlichten BugFixes

### PHP<span id="StyleguidePhp"></span>

#### Dokumentation <span id="StyleguidePhpDokumentation"></span>
Klassen erfordern eine ausführliche Typ-Dokumentation, die alle Attribute, Parameter und Rückgabewerte einschließen. Allgemein gilt das [PHPDoc](https://docs.phpdoc.org/latest/references/phpdoc/index.html).

#### Klassen<span id="StyleguidePhpKlassen"></span>
Grundsätzlich gilt der Digitale Schulhof als objektorientiertes Softwareprojekt. Imperative Programmteile haben zu unterbleiben. Für den Aufruf von Funktionen oder Attributen von Klassen wird die Schreibweise mit `->` verwendet. Wird ein Attribut der eigenen Klasse (oder ggf. Elternklasse) verwendet, muss direkt auf das Attribut zugegriffen werden (`$this->ATTRIBUTNAME`). Sollte das Attribut in einer Elternklasse als `protected` angelegt sein, gilt dasselbe. Im Fall von als `private` deklarierten Attributs sind *Getter* oder *Setter* zulässig.

#### Funktionen<span id="StyleguidePhpFunktionen"></span>
Funktionen sind im Sinne der Objektorientierung immer einer Klasse eines Moduls zugeordnet.

#### Verzweigungen und Schleifen<span id="StyleguidePhpVerzweigungenschleifen"></span>
Verzweigungen (`if` / `switch`) und Schleifen (`for`, `foreach`, `while`, `do`) sind ausnahmslos mit `{...}` begrenzt. Bei Verzweigungen mit `if` gilt folgende Schreibweise:

~~~php
<?
if () {

} [else if () {

}] [else {

}]
?>
~~~

#### Datenbankzugriffe<span id="StyleguidePhpDatenbankzugriffe"></span>
Datenbankzugriffe dürfen aussschließlich über das Modul *Kern* unter Verwendung der Klasse `DB` erfolgen. Bei Anfragen ist dabei der Anfragencode in **SQL** zu übergeben. Zur Angabe von verschlüsselnden Werten werden von `[]` und von zu entschlüsselnden Werten von `{}` umschlossen. Der Anfragencode selbst darf keine Variablen enthalten. Diese sind im Sinne von *prepared-Requests* extra zu übergeben.

#### HTML-Code-Erzeugung<span id="StyleguidePhpHtmlcodeerzeugung"></span>
HTML-Code muss mithilfe der Klassen `Zeile`, `Spalte` und `Element` oder einer entsprechenden Kind-Klasse und deren `__toString()`-Methode erfolgen.

#### Javascript-Specimen<span id="StyleguidePhpJavascriptspecimen"></span>
Wenn mittels Javascript HTML-Code einer bestimmten Klasse erzeugt werden soll, so hat dies über eine AJAX-Anfrage zu erfolgen. Eine doppelte HTML-Code-Erstellung in Javscript ist unzulässig.

#### Grundbausteine<span id="StyleguidePhpGrundbausteine"></span>
Für die Erstellung von HTML-Code sind die folgenden Klassen grundlegend:
- Elemente und Kind-Klassen müssen aus der Klasse `Element` aus `/module/UI/klassen/elemente/element.php` stammen
- HTML-Events werden über die Klasse `Aktionen` aus `module/UI/klassen/elemente/aktionen.php` verwaltet.
- Eingabefelder aller Art sind in der Klasse `Eingabe` in `module/UI/klassen/elemente/eingabe.php` bereitgestellt.
- Generell ist zur Erstellung von Code dem Modul *UI* besondere Beachtung zu schenken.
- Für Verbindungen zur Datenbank ist die Klasse `DB` aus dem Modul *Kern* zu verwenden.

### Javscript<span id="StyleguideJavascript"></span>

#### Allgemeines<span id="StyleguideJavascriptAllgemeines"></span>
Die Verwendung von **jQuery** ist untersagt.

Innerhalb von Java-Script wird versucht, objektähnliche Atrukturen aufrecht zu erhalten. Dies erfolgt durch die Nennung des Moduls gefolgt von sinnvollen Abstufungen innerhalb des Moduls:

~~~Javascript
var modulname = {
  generieren: {
    // Code-Generierung hier - falls die Erstellung nicht an vordefinierte Klassen gebunden ist
    // Andernfalls ist die Verwendung von AJAX geboten.
    neuesDings: (art, bla) => {
      if (art == 'dings') {
        return bla;
      } else {
        return "blub";
      }
    }
  },
  check: {
    // Prüfen von Eingaben oder Werten hier
    irgendwas: (x) => x.match(/blub/)
  },
  elementname: {
    // Sonstiges nach Elementen sortiert
  }
}
~~~

#### Funktionen<span id="StyleguideJavascriptFunktionen"></span>
Funktionen sind im Sinne der Objektorientierung immer einem Javascript-Objekt eines Moduls zugeordnet. Funktionen werden als *Arrow functions* (`() => {}`) definiert (siehe: Codebeispiel Allgemeines). Wenn möglich, sind diese inline zu schreiben (`(x) => {return x**2;}` wird zu `(x) => x**2`)

#### Verzweigungen und Schleifen<span id="StyleguideJavascriptVerzweigungenschleifen"></span>
Es gelten die gleichen Angaben wie bei [PHP](#StyleguidePhpVerzweigungenschleifen)

### AJAX<span id="StyleguideAjax"></span>
To be continued :)