/**
 * Lädt eine Seite asynchron nach
 * @param  {string} seite URL
 */
core.seiteLaden = (seite, push) => {
  if(push === undefined) {
    push = true;
  }
  $("#dshHauptteilI").classList.add("dshSeiteLaedt");
  document.title = "Seite wird geladen...";
	core.ajax("Core", 0, null, {"seite": seite}).then((r) => {
    $("#dshHauptteilI").classList.remove("dshSeiteLaedt");
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
    if(rueck["daten"]) {
      if(push) {
        window.history.pushState({}, rueck["daten"]["seitentitel"], seite);
      }
      document.title = rueck["daten"]["seitentitel"];
    }
    if(rueck["seite"]) {
      $("#dshSeite").innerHTML = rueck["seite"];
    }
	})
}

core.navigationAendern = (ziel) => {
  if(ziel === $("#dshKopfnavi").value) {
    return;
  }


}

window.addEventListener("click", (e) => {
  var ziel = e.target;
  if(ziel.tagName === "A") {
    if(ziel.hasAttribute("href")) {
      if(!ziel.classList.contains("extern")) {
        core.seiteLaden(ziel.getAttribute("href"));
        e.preventDefault();
      }
    }
  }
});

window.addEventListener("popstate", (e) => {
  core.seiteLaden(document.location.pathname.substring(1), false);
});