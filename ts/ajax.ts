import { Antworten as AntwortenCore, Daten as DatenCore } from "./_export";
import $ from "ts/eQuery";
import * as ladebalken from "ts/ladebalken";
import { AnfrageAntworten } from "./AnfrageAntworten";
import * as uiLaden from "module/UI/ts/elemente/laden";
import * as uiTabelle from "module/UI/ts/elemente/tabelle";
import { AnfrageDaten } from "./AnfrageDaten";

// eslint-disable-next-line @typescript-eslint/no-empty-interface
export interface AnfrageAntwortLeer { }

export interface AnfrageAntwortCode {
  Code: string
}

export interface AnfrageAntwortErfolg {
  Erfolg: true;
}

interface AnfrageAntwortFehler {
  Fehler: [string, number][];
}

// eslint-disable-next-line @typescript-eslint/no-empty-interface
export interface AnfrageDatenLeer { }

export type ANTWORTEN = AnfrageAntworten & {
  "Core": AntwortenCore
};

export type DATEN = AnfrageDaten & {
  "Core": DatenCore
};

type AAMODUL = keyof ANTWORTEN & string;
type AAZIEL = keyof ANTWORTEN[AAMODUL];
export type AjaxAntwort<A extends ANTWORTEN[AAMODUL][AAZIEL]> = Promise<AnfrageAntwortErfolg & A>

export let letzteAnfrage: XMLHttpRequest | null = null;

const ajax = <MODUL extends keyof AA & keyof AD & string, ZIEL extends keyof AA[MODUL] & keyof AD[MODUL], AANTWORT extends AA[MODUL][ZIEL], ADATEN extends AD[MODUL][ZIEL], AA extends Record<string, any> = ANTWORTEN, AD extends Record<string, any> = DATEN>(
  modul: MODUL,
  ziel: ZIEL,
  laden?: string | { titel: string; beschreibung: string | null; } | false,
  daten?: ADATEN,
  meldung?: number | { modul: string; meldung: number; } | false,
  sortieren?: string | string[] | false,
  host?: string
): AjaxAntwort<AANTWORT> => {
  if (laden === undefined) {
    laden = "Die Anfrage wird behandelt...";
  }
  if (laden !== false) {
    if (typeof laden === "string") {
      laden = { titel: laden, beschreibung: "Bitte warten..." };
    }
    uiLaden.an(laden.titel, laden.beschreibung);
  }

  return new Promise((erfolg: (rueckgabe: AnfrageAntwortErfolg & AANTWORT) => void, fehler: (fehler: AnfrageAntwortFehler) => void) => {
    if (meldung === undefined) {
      meldung = false;
    }
    if (sortieren === undefined) {
      sortieren = false;
    }
    if (host === undefined) {
      host = "";
    }

    const formDaten = new FormData();
    for (const key in daten) {
      if (["string", "Blob"].includes(typeof daten[key])) {
        formDaten.append(key, daten[key]);
      } else if (["number"].includes(typeof daten[key])) {
        formDaten.append(key, (daten[key] as { toString: () => string }).toString());
      } else {
        formDaten.append(key, JSON.stringify(daten[key]));
      }
    }
    formDaten.append("modul", modul);
    formDaten.append("ziel", ziel.toString());

    const anfrage = new XMLHttpRequest();
    anfrage.onreadystatechange = () => {
      if (anfrage.readyState === 4 && anfrage.status === 200) {
        try {
          const r: { Erfolg: boolean } & AANTWORT & AnfrageAntwortFehler = JSON.parse(anfrage.responseText) as { Erfolg: boolean } & AANTWORT & AnfrageAntwortFehler;
          try {
            $("#dshFehlerbox").ausblenden();
            if (r.Erfolg) {
              if (sortieren !== false) {
                if (typeof sortieren === "string") {
                  sortieren = [sortieren];
                }
                for (const t of sortieren as string[]) {
                  if ($("#" + t).existiert()) {
                    uiTabelle.sortieren(t);
                  }
                }
              }
              if (meldung !== null) {
                if (typeof meldung === "object") {
                  uiLaden.meldung(meldung.modul, meldung.meldung);
                } else if (typeof meldung === "number") {
                  uiLaden.meldung(modul, meldung);
                }
              }
              erfolg(r as AnfrageAntwortErfolg & AANTWORT);
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