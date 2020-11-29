import $ from "ts/eQuery";
import ajax from "./ajax";

let bereich = "";

export default function navigationAnpassen(ziel?: string, force?: boolean): void {
  if (!force && ziel === bereich) {
    return;
  }
  if (ziel === undefined) {
    ziel = bereich;
  }
  bereich = ziel;
  ajax("Core", 1, false, { bereich: ziel }).then(r => {
    $("#dshHauptnavigation").setHTML(r.Navigation);
  });
}