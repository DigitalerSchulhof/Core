import $ from "ts/eQuery";
import ajax from "./ajax";

let bereich = "";

export const navigationAnpassen = (ziel?: string | null, force?: boolean): void => {
  if (!force && ziel === bereich) {
    return;
  }
  if (ziel === undefined || ziel === null) {
    ziel = bereich;
  }
  bereich = ziel;
  ajax("Core", 1, false, { bereich: ziel }).then(r => {
    $("#dshHauptnavigation").setHTML(r.Navigation);
  });
};