/**
 * Lädt eine Seite asynchron nach
 * @param  {string} seite [description]
 */
core.seiteLaden = (seite) => {
	ajax("Kern", 0, {"seite": seite}).then((r) => {
		var rueck = JSON.parse(d);
		if(rueck["kopfzeile"]) {
      
		}
	})
}