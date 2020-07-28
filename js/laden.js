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

  }).then((r) => {
    core.seiteladebalken.aus();
    try {
		    var rueck = JSON.parse(r);
    } catch(e) {
      r = r.replace(/&/g, "&amp");
      r = r.replace(/</g, "&lt");
      r = r.replace(/>/g, "&gt");
      console.error("Fehler beim Laden der Seite " + seite, r);
      ui.meldung.fehler("Beim Laden der Seite ist ein Fehler aufgetreten!", "<pre style=\"white-space:pre-wrap\">"+r+"</pre>").then((r) => $("#dshSeite").innerHTML = r);
      document.title = "Fehler";
      return;
    }
    if(push) {
      window.history.pushState({}, rueck["daten"]["seitentitel"], seite);
    }
    document.title = rueck["daten"]["seitentitel"];
    $("#dshHauptteilI").innerHTML = rueck["seite"];

    // Script austauschen
    var r = (n) => {
      if(n.tagName === "SCRIPT") {
        var c  = document.createElement("script");
        c.text = n.innerHTML;
        for(let i = 0; i < n.attributes.length; i++) {
          c.setAttribute(n.attributes[i].name, n.attributes[i].value);
        }
        n.parentNode.replaceChild(c, n);
      } else {
        for(let i = 0; i < n.childNodes.length; i++) {
          r(n.childNodes[i]);
        }
      }
    }
    r($("body"));
    for(let a of $("a.extern:not([target])", true)) {
      a.setAttribute("target", "_blank");
    }
    window.dispatchEvent(new Event("dshSeiteGeladen"));
  });
}

core.seiteladebalken = {
  balken: $("#dshSeiteladenI"),
  fortschritt: 12,
  timeout: null,
  an: () => {
    let b = core.seiteladebalken.balken;
    b.style.width = core.seiteladebalken.fortschritt+"%";
    core.seiteladebalken.fortschritt = 12;
    b.style.opacity = "1";
    core.seiteladebalken.timeout = setTimeout(() => {
      core.seiteladebalken.fortschritt += 2;
      core.seiteladebalken.update();
    }, 0);
  },
  update: () => {
    let b = core.seiteladebalken.balken;
    b.style.width = Math.min(core.seiteladebalken.fortschritt, 92)+"%";
    core.seiteladebalken.timeout = setTimeout(() => {
      core.seiteladebalken.fortschritt += Math.floor(Math.random() * 4);
      core.seiteladebalken.update();
    }, Math.floor(100 + (Math.random()*200)));
  },
  aus: () => {
    let b = core.seiteladebalken.balken;
    core.seiteladebalken.fortschritt = 100;
    b.style.width = core.seiteladebalken.fortschritt+"%";
    setTimeout(() => {
      b.style.opacity = "0";
      setTimeout(() => {
        b.style.width = "0%";
        core.seiteladebalken.fortschritt = 0;
      }, 300);
    }, 400);
    clearTimeout(core.seiteladebalken.timeout);
  }
}


core.navigationAnpassen = (ziel) => {
  if(ziel === $("#dshKopfnavi").value) {
    return;
  }
}

window.addEventListener("load", () => {
  core.seiteladebalken.balken = $("#dshSeiteladenI");
});

window.addEventListener("click", (e) => {
  var ziel = e.target;
  if(ziel.tagName === "A") {
    if(ziel.hasAttribute("href")) {
      if(!ziel.classList.contains("extern")) {
        core.seiteLaden(ziel.getAttribute("href"));
        if(ziel.getAttribute("onhref") !== undefined) {
          new Function(ziel.getAttribute("onhref")).call(ziel);
        }
        e.preventDefault();
      }
    }
  }
});

window.addEventListener("popstate", (e) => {
  core.seiteLaden(document.location.pathname.substring($("base").getAttribute("href").length), false);
});