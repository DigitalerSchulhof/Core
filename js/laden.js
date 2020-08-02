/**
 * Lädt eine Seite asynchron nach
 * @param  {string} seite URL
 */
core.seiteLaden = (seite, push) => {
  if(push === undefined) {
    push = true;
  }

  core.seiteladebalken.an();
  core.seiteladebalken.seite = seite;
  core.ajax("Core", 0, null, {seite: seite}).then((rueck) => {
    core.seiteladebalken.aus();
    if(push) {
      window.history.pushState({}, rueck["Titel"], seite);
    }
    document.title = rueck["Titel"];
    $("#dshHauptteilI").setHTML(rueck["Code"]);

    // Script austauschen
    var r = (n) => {
      if(n.ist("script")) {
        var c  = document.createElement("script");
        c.text = n.getHTML();
        for(let i = 0; i < n[0].attributes.length; i++) {
          c.setAttribute(n[0].attributes[i].name, n[0].attributes[i].value);
        }
        n.parent()[0].replaceChild(c, n[0]);
      } else {
        for(let i = 0; i < n.kinder().length; i++) {
          r($(n.kinder()[i]));
        }
      }
    }
    r($("body"));

    // Target von unvollständigen externen Links korrekt setzen
    $("a.dshExtern:not([target])").setAttr("target", "_blank");

    window.dispatchEvent(new Event("dshSeiteGeladen"));
    window.dispatchEvent(new Event("resize"));
  });
}

core.seiteladebalken = {
  balken: $("#dshSeiteladenI"),
  fortschritt: 12,
  timeout: null,
  seite: null,
  an: () => {
    let b = core.seiteladebalken.balken;
    b.setCss({width: core.seiteladebalken.fortschritt + "%", opacity: "1"});
    core.seiteladebalken.fortschritt = 12;
    core.seiteladebalken.timeout = setTimeout(() => {
      core.seiteladebalken.fortschritt += 2;
      core.seiteladebalken.update();
    }, 0);
  },
  update: () => {
    let b = core.seiteladebalken.balken;

    if(core.seiteladebalken.fortschritt > 200) {
      window.location.href = core.seiteladebalken.seite;
    }

    b.setCss("width", Math.min(core.seiteladebalken.fortschritt, 92)+"%");
    core.seiteladebalken.timeout = setTimeout(() => {
      core.seiteladebalken.fortschritt += Math.floor(Math.random() * 4);
      core.seiteladebalken.update();
    }, Math.floor(100 + (Math.random()*200)));
  },
  aus: () => {
    let b = core.seiteladebalken.balken;
    core.seiteladebalken.fortschritt = 100;
    b.setCss("width", core.seiteladebalken.fortschritt+"%");
    setTimeout(() => {
      b.setCss("opacity", "0");
      setTimeout(() => {
        b.setCss("width", "0%");
        core.seiteladebalken.fortschritt = 0;
      }, 300);
    }, 400);
    clearTimeout(core.seiteladebalken.timeout);
  }
}


core.navigationAnpassen = (ziel) => {
  if(ziel === $("#dshKopfnavi").getWert()) {
    return;
  }
}

window.addEventListener("load", () => {
  core.seiteladebalken.balken = $("#dshSeiteladenI");
});

window.addEventListener("click", (e) => {
  var ziel = $(e.target);
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