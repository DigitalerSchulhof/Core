/**
 * Das eQuery Objekt
 * @param {...(string|HTMLElement)} args Elemente
 *
 * Fehlt in einer Methodenbeschreibung das <b>Subjekt</b>, ist das eQuery-Objekt gemeint.
 * Wenn in einer Methodenbeschreibung von <i>das eQuery-Objekt</i> die Rede ist, ist, sofern nicht <i>explizit</i> genannt, jedes zugehörige HTMLElement gemeint, nicht das eQuery-Objekt selbst.
 */
function $(...args) {
  let eQuery = {
    /**
     * Das Array an HTMLElement des eQuery-Objekts (explizit)
     * @private
     */
    el: [],
    /**
     * Führt die übergebene Funktion für jedes HTMLElement des eQuery-Objekts aus
     * @param  {function} fn Die auszuführende Funktion
     * Der erste Parameter ist das jeweilige HTMLElement
     * Der zweite Parameter ist der Index des HTMLElements (bei 0 beginnend)
     * <code>this</code> ist das alleinige HTMLElement als eQuery-Objekt
     * @return {eQuery}      [description]
     */
    each: (fn)            => {
     for(let i = 0; i < eQuery.el.length; i++) {
       fn.call($(eQuery.el[i]), eQuery.el[i], i);
     }
     return eQuery;
    },
    /**
     * Blendet ein
     * @param  {string} [d="block"] Der style="display: " - Wert neue des eQuery-Objekts
     * @return {eQuery}
     */
    einblenden: (d)       => eQuery.each(o => o.style.display = d || "block"),
    /**
     * Blendet aus
     * @param  {string} [d="none"] Der style="display: " - Wert neue des eQuery-Objekts
     * @return {eQuery}
     */
    ausblenden: (d)       => eQuery.each(o => o.style.display = d || "none"),
    /**
     * Gibt den innerHTML-Code des ersten HTMLElements des eQuery-Objekts (explizit) zurück
     * @return {string}
     */
    getHTML: ()           => eQuery.el[0].innerHTML,
    /**
     * Setzt den innerHTML-Code
     * @param {string} html Der Code
     * @return {eQuery}
     */
    setHTML: (html)       => eQuery.each(o => o.innerHTML = html),
    /**
     * Gibt den innerText-Text des ersten HTMLElements des eQuery-Objekts (explizit) zurück
     * @return {string}
     */
    getText: ()           => eQuery.el[0].innerText,
    /**
     * Setzt den innerText-Text
     * @param {string} text Der Text
     * @return {eQuery}
     */
    setText: (text)       => eQuery.each(o => o.innerText = text),
    /**
     * Gibt zurück, ob das erste HTMLElements des eQuery-Objekts (explizit) das übergebene Attribut hat
     * @param  {string} attr Das Attribute
     * @return {bool}
     */
    hatAttr: (attr)       => eQuery.el[0].hasAttribute(attr),
    /**
     * Gibt den Wert des übergebenen Attributes des ersten HTMLElements des eQuery-Objekts (explizit) zurück
     * @param  {string} attr Das Attribute
     * @return {*}
     */
    getAttr: (attr)       => eQuery.el[0].getAttribute(attr),
    /**
     * Setzt das übergebene Attribute auf den übergebenen Wert
     * @param {[type]} attr Das Attribut
     * @param {[type]} wert Der Wert
     * @return {eQuery}
     */
    setAttr: (attr, wert) => eQuery.each(o => o.setAttribute(attr, wert)),
    // setCss: Nimmt entweder: ("CSS-Property", "Wert") oder ({CSS-Property: "Wert", CSS-Property2: "Wert", ...})
    /**
     * Setzt eine oder mehrere CSS-Properties des eQuery-Objekts
     * @param {(string|Object)} k Wenn string: CSS-Property, die gesetzt werden soll | Wenn Object: [CSS-Property => Wert]
     * @param {string} [v] Nur notwendig wenn typeof k === "string". Der Wert, auf welchen die CSS-Property gesetzt werden soll
     * @return {eQuery}
     */
    setCss: (k, v)        => {
     if(typeof k === "object") {
       for(let kk in k) {
         eQuery.each(o => o.style[kk] = k[kk]);
       }
       return eQuery;
     }
     return eQuery.each(o => o.style[k] = v);
    },
    /**
     * Gibt den Wert der übergebenen CSS-Property des ersten HTMLElement des eQuery-Objekts (explizit) zurück
     * @param  {string} property Die CSS-Property
     * @return {string}
     */
    getCss: (property)    => eQuery.el[0].style[property],
    /**
     * Wechselt eine CSS-Property zwischen zwei Werten ab
     * Beträgt die Property a, so wird diese auf b gesetzt, und anders herum.
     * @param  {string} property Die CSS-Property, welche abgewechselt wird.
     * @param  {string} a        Der erste Wert
     * @param  {string} [b=""]   Der zweite Wert
     * @return {eQuery}
     */
    toggleCss: (property, a, b) => {
     b = b||"";
     return eQuery.each(o => {
       o = $(o);
       if(o.getCss(property) === a) {
         o.setCss(property, b);
       } else {
         o.setCss(property, a);
       }
     });
    },
    /**
     * Setzt den value-Wert auf den übergebenen Wert
     * @param {*} val Der Wert, auf welchen .value gesetzt werden soll
     * @return {eQuery}
     */
    setWert: (val)        => eQuery.each(o => o.value = val),
    /**
     * Gibt den value-Wert des ersten HTMLElements des eQuery-Objekts (explizit) zurück
     * @return {*}
     */
    getWert: ()           => (eQuery.el[0] || {value: null}).value,
    /**
     * Entfernt eine oder mehrere CSS-Klassen vom eQuery-Objekt
     * @param  {...string} k Die zu entfernenden Klassen
     * @return {eQuery}
     */
    removeKlasse: (...k)  => eQuery.each(o => o.classList.remove(...k)),
    /**
     * Fügt eine oder mehrere CSS-Klassen dem eQuery-Objekt hinzu
     * @param  {...string} k Die hinzuzufügenden Klassen
     * @return {eQuery}
     */
    addKlasse: (...k)     => eQuery.each(o => o.classList.add(...k)),
    /**
     * Fügt hinzu oder entfernt eine oder mehrere CSS-Klassen
     * @param {bool} b Bei true werden die Klassen hinzugefügt, bei false entfernt
     * @param {...string} k Die CSS-Klassen
     * @return {eQuery}
     */
    setKlasse: (b, ...k)  => {
     if (b) {
       return eQuery.addKlasse(...k);
     }
     return eQuery.removeKlasse(...k);
    },
    /**
    * Prüft, ob das erste HTMLElement des eQuery-Objekts (explizit) dem übergebenen Selektor entspricht
    * @param {string} s Der zu überprüfende Selektor
    * @return {bool}
    */
    ist: (s)              => eQuery.el[0].matches(s),
    /**
     * Prüft, ob das erste HTMLElement des eQuery-Objekts (explizit) dem übergebenen HTMLElement entspricht
     * @param  {HTMLElement} e Das zu prüfende HTMLElement
     * @return {bool}
     */
    istElement: (e)       => eQuery.el[0].isSameNode(e),
    /**
     * Gibt das Elternteil zurück
     * @return {eQuery} eQuery-Objekt (explizit) des Elternteils
     */
    parent: ()            => $(eQuery.el[0].parentNode),
    /**
     * Gibt das nächste Elternteil, das dem übergebenen Selektor entspricht, zurück
     * @param  {string} s Der zu prüfende Selektor
     * @return {eQuery}   eQuery-Objekt (explizit) des nächsten passenden Elternteils
     */
    parentSelector: (s)   => $(eQuery.el[0].closest(s)),
    /**
     * Gibt alle Kinder des ersten HTMLElements des eQuery-Objekts (explizit) zurück
     * @return {eQuery}
     */
    kinder: ()            => $(...eQuery.el[0].childNodes),
    /**
     * Gibt alle direkten Kinder des ersten HTMLElements des eQuery-Objekts (explizit) zurück, welche dem übergebenen Selektor entsprechen
     * @param  {string} s Der zu überprüfende Selektor
     * @return {eQuery}
     */
    kinderSelector: (s)   => $(...eQuery.el[0].querySelectorAll(">".s)),
    /**
     * Gibt alle Kinder, direkt und über mehrere Generationen, des ersten HTMLElements des eQuery-Objekts (explizit) zurück, welche dem übergebenen Selektor entsprechen
     * @param  {string} s Der zu überprüfende Selektor
     * @return {eQuery}
     */
    finde: (s)            => $(...eQuery.el[0].querySelectorAll(s)),
    /**
     * Gibt das vorherige Element zurück
     * @return {eQuery} eQuery-Objekt (explizit) des vorherigen Elements
     */
    siblingVor: ()        => $(eQuery.el[0].previousSibling),
    /**
     * Gibt das nächste Element zurück
     * @return {eQuery} eQuery-Objekt (explizit) des nächsten Elements
     */
    siblingNach: ()       => $(eQuery.el[0].nextSibling),
    /**
     * Gibt zurück, ob das eQuery-Objekt existiert, ob es mehr als 0 HTMLElemente fässt
     * @return {bool}
     */
    existiert: ()         => eQuery.el.length > 0,
    /**
     * Entfernt alle HTMLElemente des eQuery-Objekts (explizit)
     * @type {eQuery}
     */
    entfernen: ()         => eQuery.each(o => o.remove())
  };
  for(let a of args) {
    // Purer Text oder Sonstiges
    if(!a || (a.nodeType && a.nodeType === 3)) {
      continue;
    }
    if(a instanceof HTMLElement) {
      eQuery.el.push(a);
    } else if(typeof a === "string") {
      eQuery.el.push(...document.querySelectorAll(a));
    } else {
      console.error("Ungültiger eQuery-Parameter: ", a);
    }
  }
  let r = eQuery;
  r.__proto__ = eQuery.el;
  return r;
}