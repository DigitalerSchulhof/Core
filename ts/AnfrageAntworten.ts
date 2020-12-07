import { Antworten as AntwortenKern } from "module/Kern/ts/_export";
import { Antworten as AntwortenUI } from "module/UI/ts/_export";
import { Antworten as AntwortenWebsite } from "module/Website/ts/_export";

export interface AnfrageAntworten {
  "Kern": AntwortenKern,
  "UI": AntwortenUI,
  // "Postfach": {
  //   0: AnfrageAntwortLeer;
  //   1: AnfrageAntwortLeer;
  //   2: AnfrageAntwortCode;
  //   3: AnfrageAntwortCode;
  //   4: AnfrageAntwortLeer;
  //   5: AnfrageAntwortLeer;
  //   6: AnfrageAntwortCode;
  //   7: AnfrageAntwortLeer;
  // };
  "Website": AntwortenWebsite
}
