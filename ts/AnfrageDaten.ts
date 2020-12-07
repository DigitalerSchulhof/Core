import { Daten as DatenWebsite } from "module/Website/ts/_export";
import { Daten as DatenKern } from "module/Kern/ts/_export";
import { Daten as DatenUI } from "module/UI/ts/_export";

export interface AnfrageDaten {
  "Kern": DatenKern,
  "UI": DatenUI,
  "Website": DatenWebsite
}
