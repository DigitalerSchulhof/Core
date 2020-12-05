import ui from "module/UI/ts/ui";
import $ from "ts/eQuery";
import * as ladebalken from "ts/ladebalken";
import { AnfrageAntworten } from "./AnfrageAntworten";
import { AnfrageAntwortenInoffiziell } from "./AnfrageAntwortenInoffiziell";

// eslint-disable-next-line @typescript-eslint/no-empty-interface
export interface AntwortLeer { }

export interface AntwortCode {
  Code: string
}

export interface AnfrageErfolg {
  Erfolg: true;
}

interface AnfrageFehler {
  Fehler: [string, number][];
}

type ANTWORTEN = AnfrageAntworten & AnfrageAntwortenInoffiziell;

type MODUL = keyof ANTWORTEN & string;
type ZIEL = keyof ANTWORTEN[MODUL];
export type AjaxAntwort<A extends ANTWORTEN[MODUL][ZIEL]> = Promise<AnfrageErfolg & A>

export let letzteAnfrage: XMLHttpRequest | null = null;

const ajax = <M extends keyof AA & string, Z extends keyof AA[M], A extends AA[M][Z], AA extends Record<string, any> = ANTWORTEN>(
  modul: M,
  ziel: Z,
  laden?: string | { titel: string; beschreibung?: string; } | false,
  daten?: { [key: string]: any; },
  meldung?: number | { modul: string; meldung: number; },
  sortieren?: string | string[],
  host?: string
): AjaxAntwort<A> => {
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
    ui.elemente.laden.an(laden.titel, laden.beschreibung);
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

  return new Promise((erfolg: (rueckgabe: AnfrageErfolg & A) => void, fehler: (fehler: AnfrageFehler) => void) => {
    const anfrage = new XMLHttpRequest();
    anfrage.onreadystatechange = () => {
      if (anfrage.readyState === 4 && anfrage.status === 200) {
        try {
          const r: { Erfolg: boolean } & AnfrageFehler & A = JSON.parse(anfrage.responseText);
          try {
            $("#dshFehlerbox").ausblenden();
            if (r.Erfolg) {
              if (typeof sortieren === "object") {
                for (const t of sortieren) {
                  if ($("#" + t).existiert()) {
                    ui.elemente.tabelle.sortieren(t);
                  }
                }
              }
              if (typeof meldung === "object") {
                ui.elemente.laden.meldung(meldung.modul, meldung.meldung);
              }
              erfolg(r as AnfrageErfolg & A);
            } else {
              console.error("Fehler: ", r.Fehler);
              ajax("Kern", 30, { titel: "Fehler werden geladen", beschreibung: "Bitte warten" }, { fehler: r.Fehler }).then((r) => ui.elemente.laden.aendern("Fehler", r.Meldung, r.Knoepfe));
              fehler(r);
            }
          } catch (err) {
            console.error(err);
            $("#dshMeldungInitial").ausblenden();
            $("#dshFehlerbox").einblenden();
            $("#dshFehlerbox pre").setText("Bei der Anfrage ist ein unbekannter Fehler aufgetreten!");
            ui.elemente.laden.aus();
            ladebalken.aus();
          }
        } catch (err) {
          console.error("Kein gültiges JOSN: ", anfrage.responseText);
          $("#dshMeldungInitial").ausblenden();
          $("#dshFehlerbox").einblenden();
          $("#dshFehlerbox pre").setText("Bei der Anfrage ist ein unbekannter Fehler aufgetreten!");
          const meld = anfrage.responseText;
          $("#dshFehlerbox pre").setText(meld.replace(/^<br \/>\n/, "").replace(/\n$/, ""));
          ui.elemente.laden.aus();
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