/**
 * Führt eine asynchrone Anfrage aus
 *
 * Ist laden "string", so wird dieser als Titel für das Ladefenster genommen.
 * Ist laden "string[2]", so wird laden[0] als Titel, laden[1] als Beschreibung genommen.
 * Ist laden === false, so wird die Anfrage im Hintergrund ausgeführt, und kein Ladefenster geöffnet.
 *
 * Ist host === true, so wird dies auf CMS_LN_HOST gesetzt
 * Ist host === CMS_LN_HOST, so werden die Lehrerdatenbankzugangsdaten angehängt
 *
 * @param {string} modul Das Modul der Anfrage
 * @param {string} ziel Das Anfrageziel
 * @param {(string|string[]|boolean)} laden=["Die Anfrage wird behandelt"] Daten für die Ladenanzeige
 * @param {Object} daten={} Die Anfrageparameter
 * @param {string|boolean} [host=""] Das Netz, in das die Anfrage geht
 */
core.ajax = (modul, ziel, laden, daten, host) => {
	host    = host  || "";
  daten   = daten || {};
  
	if(laden !== null) {
		if(typeof laden === "string") {
			ui.laden.an(laden, null);
		} else {
			ui.laden.an(laden[0], laden[1]);
		}
	} else {
		// HINTERGRUND
	}

  // Daten
  var pDaten = daten;
  var daten = new FormData();
  for(let key in pDaten) {
    if(Array.isArray(pDaten[key])) {
      daten.append(key, JSON.stringify(pDaten[key]));
    } else {
      daten.append(key, pDaten[key]);
    }
  }
  daten.append("modul", modul);
  daten.append("ziel",  ziel);

  // Host
  if(host === true) {
    host = CMS_LN_HOST;
  }
  if(typeof CMS_LN_HOST !== "undefined" && host === CMS_LN_HOST) {
    cms_lehrerdatenbankzugangsdaten(daten);
  }


  return new Promise((erfolg) => {
		var anfrage = new XMLHttpRequest();
		anfrage.onreadystatechange = () => {
			if (anfrage.readyState == 4 && anfrage.status == 200) {
        let fehler = false;
        let r;
        try {
          r = JSON.parse(anfrage.responseText);
        }
        catch(err) {
          console.log("Fehler bei AJAX-Anfrage", anfrage.responseText);
          fehler = true;
        }
        if(fehler) {
          $("#dshMeldungInitial").ausblenden();
          $("#dshFehlerbox").einblenden();
          meld = anfrage.responseText;
          $("#dshFehlerbox pre").setHTML(meld.replace(/^<br \/>\n/, "").replace(/\n$/, ""));
          ui.laden.aus();
        } else {
          $("#dshFehlerbox").ausblenden();
          if (r.Typ == "Meldung") {
            ui.laden.aendern(null, r.Meldung, r.Knoepfe);
            if (r.Autoschliessen) {
              window.setTimeout('ui.laden.aus()', 1500);
            }
          }
          else if (r.Typ == "Weiterleitung") {
            core.seiteLaden(r.Ziel);
            ui.laden.aus();
          }
          else if (r.Typ == "Fortsetzen") {
            eval(r.Funktion);
          }
          erfolg(r);
        }
			}
		};
		anfrage.open("POST",host+"anfrage.php", true);
		anfrage.send(daten);
	});
}

core.multiajax = (modul, ziel, laden, arrays, statisch, host) => {
  var host = host || "";

  if(laden !== null) {
    if(typeof laden === "string") {
      ui.laden.an(laden, null);
    } else {
      ui.laden.an(laden[0], laden[1]);
    }
  } else {
    // HINTERGRUND
  }

  return new Promise((erfolg) => {
    var anfrage = (i) => {
      ajax(modul, ziel, null, daten[i], host).then(erfolg);
    }

    anfrage(0);
  });
}
