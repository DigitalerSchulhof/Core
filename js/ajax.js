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
 * @param {(string|string[]|bool)} laden=["Die Anfrage wird behandelt"] Daten für die Ladenanzeige
 * @param {Object} daten={} Die Anfrageparameter
 * @param {(number|array)} meldung=null Die Meldung, die geöffnet wird, wenn die Anfrage erfolgreich gewesen ist.
 * Wenn number: ui.laden.meldung(modul, meldung)
 * Sonst: ui.laden.meldung(meldung[0], meldung[1]);
 * @param {string[]} sortieren=[] Array an Tabellenids, welche nach der Anfrage neu sortiert werden sollen.
 * @param {string|boolean} [host=""] Das Netz, in das die Anfrage geht
 */
core.ajax = (modul, ziel, laden, daten, meldung, sortieren, host) => {
	host    = host    || "";
  if (meldung === undefined) {
    meldung = null;
  }
  sortieren = sortieren || [];
  daten   = daten   || {};

	if(laden !== null) {
		if(typeof laden === "string") {
			ui.laden.an(laden, "Bitte warten");
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
    if(Array.isArray(pDaten[key]) || typeof pDaten[key] === "object") {
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


  return new Promise((erfolg, fehler) => {
		var anfrage = new XMLHttpRequest();
		anfrage.onreadystatechange = () => {
			if (anfrage.readyState == 4 && anfrage.status == 200) {
        try {
          let r = JSON.parse(anfrage.responseText);
          $("#dshFehlerbox").ausblenden();
          if(r.Erfolg) {
            for(let t of sortieren) {
              if($("#"+t).existiert()) {
                ui.tabelle.sortieren(t);
              }
            }
            if(meldung !== null) {
              if(Number.isInteger(meldung)) {
                ui.laden.meldung(modul, meldung);
              } else {
                ui.laden.meldung(meldung[0], meldung[1]);
              }
            }
            erfolg(r);
          } else {
            console.error("Fehler: ", r.Fehler);
            core.ajax("Kern", 30, ["Fehler werden geladen", "Bitte warten"], {fehler: r.Fehler}).then((r) => ui.laden.aendern("Fehler", r.Meldung, r.Knoepfe));
            fehler(r);
          }
        } catch(err) {
          console.error("Kein gültiges JOSN: ", anfrage.responseText);
          $("#dshMeldungInitial").ausblenden();
          $("#dshFehlerbox").einblenden();
          let meld = anfrage.responseText;
          $("#dshFehlerbox pre").setText(meld.replace(/^<br \/>\n/, "").replace(/\n$/, ""));
          ui.laden.aus();
          core.seiteladebalken.aus();
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