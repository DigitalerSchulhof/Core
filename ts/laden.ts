import ajax, { letzteAnfrage } from "./ajax";
import $, { eQuery } from "ts/eQuery";
import * as ladebalken from "ts/ladebalken";
import navigationAnpassen from "./navigation";
import ui from "module/UI/ts/ui";

let letzteSeitenanfrage: XMLHttpRequest | null = null;

const seiteLaden = (seite: string, push?: boolean, navigation?: boolean): void => {
  if (letzteSeitenanfrage !== null) {
    letzteSeitenanfrage.abort();
    letzteSeitenanfrage = null;
  }
  if (push === undefined) {
    push = true;
  }
  navigationAnpassen(seite.split("/")[0], navigation);
  ui.elemente.laden.aus();
  ladebalken.an();
  if (push) {
    window.history.pushState({}, "Digitaler Schulhof - Die Seite wird geladen...", seite);
  }
  ajax("Core", 0, false, { seite: seite }).then((rueck) => {
    if (rueck.Weiterleitung === true) {
      seiteLaden(rueck.Ziel, false);
    } else {
      if (push) {
        window.history.replaceState({}, "Digitaler Schulhof - " + rueck.Titel, seite);
      }
      document.title = rueck["Titel"];
      if (rueck.Code || rueck.Code === "") {
        $("#dshSeite").setHTML(rueck.Code);
        $("#dshMeldungInitial", "#dshFehlerbox").ausblenden();

        scriptAn($("#dshSeite"));
        // Target von unvollständigen externen Links korrekt setzen
        $("a.dshExtern:not([target])").setAttr("target", "_blank");

        if ($(".autofocus").existiert()) {
          $(".autofocus")[0].focus();
          if ($(".autofocus").length > 1) {
            console.warn("Mehr als ein .autofocus gefunden!");
          }
        }
        window.dispatchEvent(new Event("dshSeiteGeladen"));
        window.dispatchEvent(new Event("resize"));
        ladebalken.aus();
      }
    }
  });
  letzteSeitenanfrage = letzteAnfrage;
};

export default seiteLaden;

export const neuladen = (): void => seiteLaden(document.location.pathname.substring(($("base").getAttr("href") as string).length), false);

export const rueck = (): void => window.history.back();

export const scriptAn = (feld: eQuery): void => {
  feld.finde("script").each((n) => {
    const c = document.createElement("script");
    c.text = n.innerHTML;
    for (let i = 0; i < n.attributes.length; i++) {
      c.setAttribute(n.attributes[i].name, n.attributes[i].value);
    }
    n.parentNode?.replaceChild(c, n);
  });
};

window.addEventListener("load", () => {
  ladebalken.setBalken($("#dshSeiteladenI"));
});

window.addEventListener("click", (e: MouseEvent) => {
  let ziel = $(e.target);
  if (e.ctrlKey) {
    return;
  }
  while (!ziel.ist("html") && !ziel.ist("a")) {
    ziel = ziel.parent();
    if (ziel.length === 0) {
      return;
    }
  }

  const href = ziel.getAttr("href");
  if (ziel.ist("a[href]:not(.dshExtern)") && href !== null) {
    seiteLaden(href);
    if (ziel.ist("[onhref]")) {
      new Function(href).call(ziel[0]);
    }
    e.preventDefault();
  }
});

window.addEventListener("popstate", () => {
  seiteLaden(document.location.pathname.substring(($("base").getAttr("href") as string).length), false);
});

if (document.location.pathname.slice(-1) === "/") {
  window.history.replaceState({}, document.title, document.location.pathname.substring(0, document.location.pathname.length - 1));
}