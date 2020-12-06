import $, { eQuery } from "./eQuery";

let balken: eQuery = $("#dshSeiteladenI");
let fortschritt = 0;
let timeout: number | null = null;

export const setBalken = (neuerBalken: eQuery): void => {
  balken = neuerBalken;
};

// Weil sonst NodeJS' genommen wird
declare function setTimeout(handler: TimerHandler, timeout?: number, ...arguments: any[]): number;

export const an = (): void => {
  balken.addKlasse("dshNoTransition");
  balken.setCss({ width: "0%", opacity: "0" });
  balken[0].offsetHeight;  // CSS-Cache leeren
  balken.removeKlasse("dshNoTransition");
  balken.setCss({ opacity: "1" });
  fortschritt = 0;

  if (timeout !== null) {
    clearTimeout(timeout);
  }

  timeout = setTimeout(() => {
    fortschritt += 12;
    update();
  }, 10);
};

export const update = (): void => {
  if (fortschritt > 120) {
    aus();
    $("#dshMeldungInitial").ausblenden();
    $("#dshMeldungOffline").einblenden();
    $("#dshHauptnavigation").ausblenden();
  }

  balken.setCss("width", Math.min(fortschritt, 92) + "%");
  timeout = setTimeout(() => {
    fortschritt += Math.floor(Math.random() * 3);
    update();
  }, Math.floor(100 + (Math.random() * 50)));
};

export const aus = (): void => {
  fortschritt = 100;
  balken.setCss("width", fortschritt + "%");
  setTimeout(() => {
    balken.setCss("opacity", "0");
    setTimeout(() => {
      balken.addKlasse("dshNoTransition");
      balken.setCss("width", "0%");
      balken.addKlasse("dshNoTransition");
      fortschritt = 0;
    }, 300);
  }, 400);
  if (timeout !== null) {
    clearTimeout(timeout);
  }
};