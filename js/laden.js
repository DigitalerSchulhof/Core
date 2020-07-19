/**
 * Lädt eine Seite asynchron nach
 * @param  {string} seite [description]
 */
core.seiteLaden = (seite) => {
	core.ajax("Kern", 0, {"seite": seite}).then((r) => {
		var rueck = JSON.parse(r);
		if(rueck["kopfzeile"]) {

		}
    if(rueck["seite"]) {

    }
	})
}