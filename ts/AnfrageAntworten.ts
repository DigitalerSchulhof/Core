import { AnfrageAntwortLeer, AnfrageAntwortCore } from "./ajax";


export interface AnfrageAntworten {
  "Core": {
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
  },
  "Kern": {
    0: AnfrageAntwortLeer;
    1: AnfrageAntwortLeer;
    2: {
      Limit: number;
      Ende: number;
    };
    3: AnfrageAntwortLeer;
    4: AnfrageAntwortLeer;
    5: AnfrageAntwortLeer;
    6: AnfrageAntwortLeer;
    7: AnfrageAntwortLeer;
    8: AnfrageAntwortLeer;
    9: AnfrageAntwortLeer;
    10: AnfrageAntwortLeer;
    11: AnfrageAntwortLeer;
    12: AnfrageAntwortLeer;
    13: AnfrageAntwortCore;
    14: AnfrageAntwortLeer;
    15: AnfrageAntwortCore;
    16: AnfrageAntwortLeer;
    17: AnfrageAntwortCore;
    18: AnfrageAntwortCore;
    19: AnfrageAntwortCore;
    20: AnfrageAntwortCore;
    21: AnfrageAntwortLeer;
    22: AnfrageAntwortLeer;
    23: AnfrageAntwortLeer;
    24: AnfrageAntwortLeer;
    25: AnfrageAntwortLeer;
    26: AnfrageAntwortLeer;
    27: AnfrageAntwortLeer;
    28: AnfrageAntwortLeer;
    29: AnfrageAntwortLeer;
    30: {
      Meldung: string;
      Knoepfe: string;
    };
    31: AnfrageAntwortCore;
    32: AnfrageAntwortCore;
    33: AnfrageAntwortLeer;
    34: {
      ID: number;
    };
    35: AnfrageAntwortCore;
    36: AnfrageAntwortLeer;
    37: {
      Limit: number;
      Ende: number;
    };
    38: AnfrageAntwortCore;
    39: AnfrageAntwortLeer;
    40: AnfrageAntwortLeer;
    41: AnfrageAntwortLeer;
    42: AnfrageAntwortLeer;
    43: AnfrageAntwortCore;
    44: AnfrageAntwortLeer;
    45: AnfrageAntwortLeer;
    46: AnfrageAntwortLeer;
    47: AnfrageAntwortCore;
    48: AnfrageAntwortLeer;
    49: AnfrageAntwortCore;
    50: {
      ergebnisse: string;
    };
  };
  "UI": {
    0: AnfrageAntwortCore;
    1: {
      Meldung: string;
      Knoepfe: string;
    };
    2: AnfrageAntwortCore;
  };
  "Postfach": {
    0: AnfrageAntwortLeer;
    1: AnfrageAntwortLeer;
    2: AnfrageAntwortCore;
    3: AnfrageAntwortCore;
    4: AnfrageAntwortLeer;
    5: AnfrageAntwortLeer;
    6: AnfrageAntwortCore;
    7: AnfrageAntwortLeer;
  };
  "Website": {
    0: AnfrageAntwortCore;
    1: AnfrageAntwortCore;
    2: AnfrageAntwortLeer;
    3: AnfrageAntwortCore;
    4: AnfrageAntwortLeer;
    5: AnfrageAntwortLeer;
    6: AnfrageAntwortLeer;
    7: AnfrageAntwortCore;
    8: AnfrageAntwortCore;
    9: AnfrageAntwortLeer;
    10: AnfrageAntwortCore;
    11: AnfrageAntwortLeer;
    12: AnfrageAntwortLeer;
    13: AnfrageAntwortLeer;
    14: AnfrageAntwortLeer;
    15: AnfrageAntwortCore;
    16: AnfrageAntwortLeer;
    17: AnfrageAntwortCore;
    18: AnfrageAntwortLeer;
    19: AnfrageAntwortLeer;
    20: AnfrageAntwortLeer;
    21: AnfrageAntwortLeer;
    22: AnfrageAntwortLeer;
  };
}
