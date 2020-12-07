import seiteLaden from "./laden";

export interface Antworten {
  0: {
    Titel: string;
    Code: string;
  } & {
    Weiterleitung: true;
    Ziel: string;
  },
  1: {
    Navigation: string;
  }
}

export interface Daten {
  0: {
    seite: string
  },
  1: {
    bereich: "Schulhof" | string
  }
}

export default {
  seiteLaden: seiteLaden
};