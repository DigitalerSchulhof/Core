import { SortierParameter } from "module/UI/ts/elemente/tabelle";
import { AnfrageDatenLeer } from "./ajax";

export type PersonenArt = "s" | "l" | "v" | "e" | "x";
export type PersonenGeschlecht = "w" | "m" | "d";
export type ToggleWert = "0" | "1";
export type ProfilArt = "person" | "nutzerkonto";

export type IconArt = string;

interface PersonenDaten {
  art: PersonenArt,
  geschlecht: PersonenGeschlecht,
  titel: string,
  vorname: string;
  nachname: string,
}

export interface AnfrageDaten {
  "Core": {
    0: {
      seite: string
    },
    1: {
      bereich: "Schulhof" | string
    }
  },
  "Kern": {
    0: {
      benutzer: string;
      passwort: string;
    },
    1: AnfrageDatenLeer,
    2: AnfrageDatenLeer,
    3: {
      benutzer: string;
      mail: string;
    },
    4: {
      mail: string;
    },
    5: {
      aktiv: ToggleWert,
      typ: "DSH" | "EXT",
    },
    6: PersonenDaten & {
      klasse: string,
      passwort: string,
      passwort2: string,
      mail: string,
      datenschutz: ToggleWert,
      entscheidung: ToggleWert,
      korrektheit: ToggleWert,
      spamschutz: string,
      spamid: string;
    },
    7: {
      id: number;
      kuerzel: string;
    } & PersonenDaten,
    8: {
      id: number,
      benutzer: string;
      email: string;
    },
    9: {
      id: number;
      passwortalt: string;
      passwortneu: string;
      passwortneu2: string;
    },
    10: {
      id: number;
      inaktivitaetszeit: number;
      uebersichtselemente: number;
      wiki: ToggleWert,
    },
    11: {
      id: number,
      notifikationen: ToggleWert,
      blog: ToggleWert,
      termin: ToggleWert,
      galerie: ToggleWert,
    },
    12: AnfrageDatenLeer,
    13: AnfrageDatenLeer,
    14: {
      nutzerid: number;
      sessionid: number | "alle";
    },
    15: {
      id: number | "alle",
    } & SortierParameter,
    16: {
      nutzerid: number | "alle",
      logid: number | "alle",
    },
    17: {
      nutzerid: number,
      logid: number | "alle",
    },
    18: {
      id: number | "alle",
      datum: number,
    } & SortierParameter,
    19: {
      id: number,
      datum: number,
    } & SortierParameter,
    20: {
      modulname: string;
    },
    21: {
      schulname: string;
      schulort: string;
      strhnr: string;
      plzort: string;
      telefon: string;
      fax: string;
      mail: string;
      domain: string;
    },
    22: {
      slname: string;
      slmail: string;
      daname: string;
      damail: string;
      prname: string
      prmail: string;
      wename: string;
      wemail: string;
      adname: string;
      admail: string;
    },
    23: {
      mailadresse: string;
      mailtitel: string;
      mailuser: string;
      mailpass: string,
      mailhost: string;
      mailport: number;
      mailauth: ToggleWert;
      mailsigp: string;
      mailsigh: string;
    },
    24: {
      ldapaktiv: ToggleWert;
      ldapuser: string;
      ldappass: string;
      ldaphost: string;
      ldapport: number;
    },
    25: {
      aktionslog: ToggleWert
    },
    26: {
      mailadresse: string;
      mailtitel: string;
      mailuser: string;
      mailpass: string,
      mailhost: string;
      mailport: number;
      mailauth: ToggleWert;
      mailsigp: string;
      mailsigh: string;
    },
    28: {
      passwortalt: string;
      passwortneu: string;
      passwortneu2: string;
      hinweise: string;
    },
    29: {
      art: "Verzeichnisse" | "Schulhof";
    } & ({
      basis: string;
    } | {
      host: string;
      port: number;
      datenbank: string;
      benutzer: string;
      passwort: string
      schluessel: string;
    }),
    30: {
      fehler: [string, number][]
    },
    31: {
      vorname: string;
      nachname: string;
      klasse: string;
      schueler: ToggleWert,
      lehrer: ToggleWert;
      erzieher: ToggleWert;
      verwaltung: ToggleWert;
      externe: ToggleWert;
    },
    32: {
      id: number
    },
    33: {
      id: number,
      art: ProfilArt,
    },
    34: PersonenDaten & {
      kuerzel: string;
      nutzerkonto: ToggleWert;
      benutzername: string;
      mail: string;
    },
    35: {
      id: number;
      benutzername: string;
      mail: string;
    },
    36: {
      id: number,
    },
    37: AnfrageDatenLeer,
    38: {
      id: number
    },
    39: {
      id: number,
      rolle: number;
      wert: ToggleWert;
    }
    41: {
      id: number;
    },
    42: {
      id: number,
      rechte: string[]
    },
    43: SortierParameter,
    44: {
      bezeichnung: string;
      rechte: string[];
    },
    45: {
      id: number
    },
    46: {
      id: number;
      bezeichnung: string;
      rechte: string[];
    },
    47: SortierParameter,
    48: {
      poolKennung: string;
      poolToken: string
    },
    49: {
      id: number;
    }
    50: {
      id: number;
      pool: string;
      gewaehlt: string;
      vorname: string;
      nachname: string;
      schueler: ToggleWert,
      lehrer: ToggleWert,
      erziehungsberechtigte: ToggleWert,
      verwaltungsangestellte: ToggleWert,
      externe: ToggleWert,
      nutzerkonto: ToggleWert,
      recht: string,
    }
  },
  "UI": {
    0: {
      id: string;
      tag: number;
      monat: number;
      jahr: number;
    },
    1: {
      meldemodul: string;
      meldeid: number;
      meldeparameter: {[key: string]: any};
    },
    2: {
      komponente: "IconKnopf",
      inhalt: string,
      icon: string
      art?: IconArt,
      klickaktion?: string;
    } | {
      komponente: "IconKnopfPerson"
      inhalt: string;
      personart: PersonenArt,
      id?: string;
      klickaktion?: string;
    }
  }
}
