import { Daten as DatenCore } from "ts/_export";
import { Daten as DatenWebsite } from "module/Website/ts/_export";
import { Daten as DatenKern } from "module/Kern/ts/_export";
import { Daten as DatenUI } from "module/UI/ts/_export";

export interface AnfrageDaten {
  "Core": DatenCore,
  "Kern": DatenKern,
  "UI": DatenUI,
  "Website": DatenWebsite
}
