/**
 * Lädt eine Seite asynchron nach
 * @param  {string} seite URL
 */
core.seiteLaden = (seite, push) => {
  if(push === undefined) {
    push = true;
  }

  new Promise((erfolg) => {
    var daten = new FormData();
    daten.append("modul", "Core");
    daten.append("ziel", 0);
    daten.append("seite", seite);

    var anfrage = new XMLHttpRequest();
    anfrage.onreadystatechange = () => {
      if (anfrage.readyState == 4 && anfrage.status == 200) {
        erfolg(anfrage.responseText);
      }
    }
    anfrage.open("POST", "anfrage.php", true);
    anfrage.send(daten);
    core.seiteladebalken.an();
    core.seiteladebalken.seite = seite;

  }).then((r) => {
    core.seiteladebalken.aus();
    try {
		    var rueck = JSON.parse(r);
    } catch(e) {
      r = r.replace(/&/g, "&amp");
      r = r.replace(/</g, "&lt");
      r = r.replace(/>/g, "&gt");
      console.error("Fehler beim Laden der Seite " + seite, r);
      ui.meldung.fehler("Beim Laden der Seite ist ein Fehler aufgetreten!", "<pre style=\"white-space:pre-wrap\">"+r+"</pre>").then((r) => $("#dshSeite").html(r));
      document.title = "Fehler";
      return;
    }
    if(push) {
      window.history.pushState({}, rueck["daten"]["seitentitel"], seite);
    }
    document.title = rueck["daten"]["seitentitel"];
    $("#dshHauptteilI").html(rueck["seite"]);

    // Script austauschen
    var r = (n) => {
      if(n.is("script")) {
        var c  = document.createElement("script");
        c.text = n.html();
        for(let i = 0; i < n[0].attributes.length; i++) {
          c.setAttribute(n[0].attributes[i].name, n[0].attributes[i].value);
        }
        n.parent()[0].replaceChild(c, n[0]);
      } else {
        for(let i = 0; i < n.children().length; i++) {
          r($(n.children()[i]));
        }
      }
    }
    r($("body"));

    $("a.extern:not([target])").attr("target", "_blank");

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
    b.css({width: core.seiteladebalken.fortschritt + "%", opacity: "1"});
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

    b.css("width", Math.min(core.seiteladebalken.fortschritt, 92)+"%");
    core.seiteladebalken.timeout = setTimeout(() => {
      core.seiteladebalken.fortschritt += Math.floor(Math.random() * 4);
      core.seiteladebalken.update();
    }, Math.floor(100 + (Math.random()*200)));
  },
  aus: () => {
    let b = core.seiteladebalken.balken;
    core.seiteladebalken.fortschritt = 100;
    b.css("width", core.seiteladebalken.fortschritt+"%");
    setTimeout(() => {
      b.css("opacity", "0");
      setTimeout(() => {
        b.css("width", "0%");
        core.seiteladebalken.fortschritt = 0;
      }, 300);
    }, 400);
    clearTimeout(core.seiteladebalken.timeout);
  }
}


core.navigationAnpassen = (ziel) => {
  if(ziel === $("#dshKopfnavi").wert()) {
    return;
  }
}

window.addEventListener("load", () => {
  core.seiteladebalken.balken = $("#dshSeiteladenI");
});

window.addEventListener("click", (e) => {
  var ziel = $(e.target);

  if(ziel.is("a[href]:not(.extern)")) {
    core.seiteLaden(ziel.attr("href"));
    if(ziel.is("[onhref]")) {
      new Function(ziel.attr("onhref")).call(ziel[0]);
    }
    e.preventDefault();
  }
});

window.addEventListener("popstate", (e) => {
  core.seiteLaden(document.location.pathname.substring($("base").attr("href").length), false);
});