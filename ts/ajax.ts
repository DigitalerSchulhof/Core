import $ from "ts/eQuery";
import * as ladebalken from "ts/ladebalken";
import { AnfrageAntworten } from "./AnfrageAntworten";
import * as uiLaden from "module/UI/ts/elemente/laden";
import * as uiTabelle from "module/UI/ts/elemente/tabelle";
import { AnfrageDaten } from "./AnfrageDaten";

// eslint-disable-next-line @typescript-eslint/no-empty-interface
export interface AnfrageAntwortLeer { }

export interface AnfrageAntwortCore {
  Code: string
}

export interface AnfrageAntwortErfolg {
  Erfolg: true;
}

interface AnfrageAntwortFehler {
  Fehler: [string, number][];
}

// eslint-disable-next-line @typescript-eslint/no-empty-interface
export interface AnfrageDatenLeer {}

export type ANTWORTEN = AnfrageAntworten;
export type DATEN = AnfrageDaten;

type MODUL = keyof ANTWORTEN & string;
type ZIEL = keyof ANTWORTEN[MODUL];
export type AjaxAntwort<A extends ANTWORTEN[MODUL][ZIEL]> = Promise<AnfrageAntwortErfolg & A>

export let letzteAnfrage: XMLHttpRequest | null = null;

const ajax = <M extends keyof AA & keyof AD & string, Z extends keyof AA[M] & keyof AD[M], A extends AA[M][Z], D extends AD[M][Z], AA = ANTWORTEN, AD = DATEN>(
  modul: M,
  ziel: Z,
  laden?: string | { titel: string; beschreibung?: string; } | false,
  daten?: D,
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
  // daten = daten || {};

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

  return new Promise((erfolg: (rueckgabe: AnfrageAntwortErfolg & A) => void, fehler: (fehler: AnfrageAntwortFehler) => void) => {
    const anfrage = new XMLHttpRequest();
    anfrage.onreadystatechange = () => {
      if (anfrage.readyState === 4 && anfrage.status === 200) {
        try {
          const r: { Erfolg: boolean } & A & AnfrageAntwortFehler = JSON.parse(anfrage.responseText) as { Erfolg: boolean } & A & AnfrageAntwortFehler;
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
              erfolg(r as AnfrageAntwortErfolg & A);
            } else {
              console.error("Fehler: ", r.Fehler);
              ajax("Kern", 30, { titel: "Fehler werden geladen", beschreibung: "Bitte warten" }, { fehler: r.Fehler }).then((r) => uiLaden.aendern("Fehler", r.Meldung, r.Knoepfe));
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