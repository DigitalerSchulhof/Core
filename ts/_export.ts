import seiteLaden from "./laden";

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