/**
 * Lädt eine Seite asynchron nach
 * @param {string} seite URL
 * @param {bool} push
 * @param {bool} navigation Ob die Navigation geändert werden soll
 */
core.ladendAnfrage = null;
core.seiteLaden = (seite, push, navigation) => {
  if(core.ladendAnfrage !== null) {
    core.ladendAnfrage.abort();
    core.ladendAnfrage = null;
  }
  if(push === undefined) {
    push = true;
  }
  core.navigationAnpassen(seite.split("/")[0], navigation);
  ui.laden.aus();
  core.seiteladebalken.an();
  core.seiteladebalken.seite = seite;
  if(push) {
    window.history.pushState({}, "Digitaler Schulhof - Die Seite wird geladen...", seite);
  }
  core.ajax("Core", 0, null, {seite: seite}).then((rueck) => {
    if(rueck.Weiterleitung === true) {
      core.seiteLaden(rueck.Ziel, false);
    }
    if(push) {
      window.history.replaceState({}, "Digitaler Schulhof - "+rueck["Titel"], seite);
    }
    document.title = rueck["Titel"];
    if(rueck.Code || rueck.Code === "") {
      $("#dshSeite").setHTML(rueck.Code);
      $("#dshMeldungInitial", "#dshFehlerbox").ausblenden();


      var scriptDa = _ => {
        core.scriptAn($("#dshSeite"));

        // Target von unvollständigen externen Links korrekt setzen
        $("a.dshExtern:not([target])").setAttr("target", "_blank");

        if($(".autofocus").existiert()) {
          $(".autofocus")[0].focus();
          if($(".autofocus").length > 1) {
            console.warn("Mehr als ein .autofocus gefunden!");
          }
        }
        window.dispatchEvent(new Event("dshSeiteGeladen"));
        window.dispatchEvent(new Event("resize"));
        core.seiteladebalken.aus();
      }

      if(rueck.Scripts && rueck.Scripts.length > 0) {
        let kopf = $("head");
        var ladend = 0;
        for(let s of rueck.Scripts) {
          if(!$("head script[src='js/modul.php?modul="+s+"']").existiert()) {
            ladend++;
            var c = document.createElement("script");
            c.setAttribute("src", "js/modul.php?modul="+s);
            c.onload = _ => {
              if(--ladend == 0) {
                // Alle Script geladen
                scriptDa();
              }
            }
            kopf[0].appendChild(c);
          }
        }
        if(ladend === 0) {
          scriptDa();
        }
      } else {
        // Keine Scripts übergeben
        scriptDa();
      }
    }
  });
  core.ladendAnfrage = core.ajaxanfrage;
}

core.neuladen = _ => core.seiteLaden(document.location.pathname.substring($("base").getAttr("href").length), false);

core.seiteladebalken = {
  balken: $("#dshSeiteladenI"),
  fortschritt: 0,
  timeout: null,
  seite: null,
  an: _ => {
    let b = core.seiteladebalken.balken;

    b.addKlasse("dshNoTransition");
    b.setCss({width: "0%", opacity: "0"});
    b[0].offsetHeight;  // CSS-Cache leeren
    b.removeKlasse("dshNoTransition");
    b.setCss({opacity: "1"});
    core.seiteladebalken.fortschritt = 0;

    clearTimeout(core.seiteladebalken.timeout);

    core.seiteladebalken.timeout = setTimeout(_ => {
      core.seiteladebalken.fortschritt += 12;
      core.seiteladebalken.update();
    }, 10);
  },
  update: _ => {
    let b = core.seiteladebalken.balken;

    if(core.seiteladebalken.fortschritt > 200) {
      window.location.href = core.seiteladebalken.seite;
    }

    b.setCss("width", Math.min(core.seiteladebalken.fortschritt, 92)+"%");
    core.seiteladebalken.timeout = setTimeout(_ => {
      core.seiteladebalken.fortschritt += Math.floor(Math.random() * 4);
      core.seiteladebalken.update();
    }, Math.floor(100 + (Math.random()*200)));
  },
  aus: _ => {
    let b = core.seiteladebalken.balken;
    core.seiteladebalken.fortschritt = 100;
    b.setCss("width", core.seiteladebalken.fortschritt+"%");
    setTimeout(_ => {
      b.setCss("opacity", "0");
      setTimeout(_ => {
        b.addKlasse("dshNoTransition");
        b.setCss("width", "0%");
        b.removeKlasse("dshNoTransition");
        core.seiteladebalken.fortschritt = 0;
      }, 300);
    }, 400);
    clearTimeout(core.seiteladebalken.timeout);
  }
}

core.bereich = "";
core.navigationAnpassen = (ziel, force) => {
  if(!force && ziel === core.bereich) {
    return;
  }
  if(ziel === null) {
    ziel = core.bereich;
  }
  core.bereich = ziel;
  core.ajax("Core", 1, null, {bereich: ziel}).then(r => {
    $("#dshHauptnavigation").setHTML(r.Navigation);
  });
}

core.rueck = _ => window.history.back();

core.scriptAn = (feld) => {
  feld.finde("script").each((n) => {
    var c  = document.createElement("script");
    c.text = n.innerHTML;
    for(let i = 0; i < n.attributes.length; i++) {
      c.setAttribute(n.attributes[i].name, n.attributes[i].value);
    }
    n.parentNode.replaceChild(c, n);
  });
}

window.addEventListener("load", _ => {
  core.seiteladebalken.balken = $("#dshSeiteladenI");
});

window.addEventListener("click", (e) => {
  var ziel = $(e.target);
  if(e.ctrlKey) {
    return;
  }
  while(!ziel.ist("html") && !ziel.ist("a")) {
    ziel = ziel.parent();
    if(ziel.length === 0) {
      return;
    }
  }
  if(ziel.ist("a[href]:not(.dshExtern)")) {
    core.seiteLaden(ziel.getAttr("href"));
    if(ziel.ist("[onhref]")) {
      new Function(ziel.getAttr("onhref")).call(ziel[0]);
    }
    e.preventDefault();
  }
});

window.addEventListener("popstate", (e) => {
  core.seiteLaden(document.location.pathname.substring($("base").getAttr("href").length), false);
});

if(document.location.pathname.slice(-1) === "/") {
  window.history.replaceState({}, document.title, document.location.pathname.substring(0, document.location.pathname.length-1));
}