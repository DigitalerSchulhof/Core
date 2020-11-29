import * as uiLaden from "module/UI/ts/elemente/laden";
import * as uiTabelle from "module/UI/ts/elemente/tabelle";
import $ from "ts/eQuery";
import * as ladebalken from "ts/ladebalken";


export interface AnfrageErfolg {
  Erfolg: boolean;
}

interface AnfrageFehler {
  Fehler: [string, number][];
}

export let letzteAnfrage: XMLHttpRequest | null = null;

const ajax = <T>(
  modul: string,
  ziel: number,
  laden?: string | { titel: string; beschreibung?: string; } | false,
  daten?: { [key: string]: any; },
  meldung?: number | { modul: string; meldung: number; },
  sortieren?: string | string[],
  host?: string
): Promise<AnfrageErfolg & T> => {
  // Laden Fix
  if (laden === undefined) {
    laden = "Die Anfrage wird behandelt...";
  }
  if (typeof laden === "string") {
    laden = { titel: laden };
  }

  // Daten Fix
  daten = daten || {};

  // Sortieren Fix
  if (typeof sortieren === "string") {
    sortieren = [sortieren];
  }
  sortieren = sortieren || [];

  // Meldung Fix
  if (typeof meldung === "number") {
    meldung = { meldung: meldung, modul: modul };
  }

  host = host || "";

  if (laden !== false) {
    uiLaden.an(laden.titel, laden.beschreibung);
  }

  // Daten
  const formDaten = new FormData();
  for (const key in daten) {
    if (["string", "Blob"].includes(typeof daten[key])) {
      formDaten.append(key, daten[key]);
    } else {
      formDaten.append(key, JSON.stringify(daten[key]));
    }
  }
  formDaten.append("modul", modul);
  formDaten.append("ziel", ziel.toString());

  return new Promise((erfolg: (rueckgabe: AnfrageErfolg & T) => void, fehler: (fehler: AnfrageFehler) => void) => {
    const anfrage = new XMLHttpRequest();
    anfrage.onreadystatechange = () => {
      if (anfrage.readyState === 4 && anfrage.status === 200) {
        try {
          const r: AnfrageErfolg & AnfrageFehler & T = JSON.parse(anfrage.responseText);
          try {
            $("#dshFehlerbox").ausblenden();
            if (r.Erfolg) {
              if (typeof sortieren === "object") {
                for (const t of sortieren) {
                  if ($("#" + t).existiert()) {
                    uiTabelle.sortieren(t);
                  }
                }
              }
              if (typeof meldung === "object") {
                uiLaden.meldung(meldung.modul, meldung.meldung);
              }
              erfolg(r);
            } else {
              console.error("Fehler: ", r.Fehler);
              ajax<{
                Meldung: string;
                Knoepfe: string;
              }>("Kern", 30, { titel: "Fehler werden geladen", beschreibung: "Bitte warten" }, { fehler: r.Fehler }).then((r) => uiLaden.aendern("Fehler", r.Meldung, r.Knoepfe));
              fehler(r);
            }
          } catch (err) {
            console.error(err);
            $("#dshMeldungInitial").ausblenden();
            $("#dshFehlerbox").einblenden();
            $("#dshFehlerbox pre").setText("Bei der Anfrage ist ein unbekannter Fehler aufgetreten!");
            uiLaden.aus();
            ladebalken.aus();
          }
        } catch (err) {
          console.error("Kein gültiges JOSN: ", anfrage.responseText);
          $("#dshMeldungInitial").ausblenden();
          $("#dshFehlerbox").einblenden();
          $("#dshFehlerbox pre").setText("Bei der Anfrage ist ein unbekannter Fehler aufgetreten!");
          const meld = anfrage.responseText;
          $("#dshFehlerbox pre").setText(meld.replace(/^<br \/>\n/, "").replace(/\n$/, ""));
          uiLaden.aus();
          ladebalken.aus();
        }
      }
    };
    anfrage.open("POST", host + "anfrage.php", true);
    anfrage.send(formDaten);

    letzteAnfrage = anfrage;
  });
};

export default ajax;