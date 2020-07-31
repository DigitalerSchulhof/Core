/**
 * eQuery
 * @param  {mixed} arguments Liste an Elementen, Kann eine Mischung aus <code>HTMLElement</code> und <code>string</code>, Selektoren, sein
 */
var $ = (...arguments) => {
  let el = []
  for(let a of arguments) {
    if(a instanceof HTMLElement) {
      el.push(a);
    } else if(typeof a === "string") {
      el.push(...document.querySelectorAll(a));
    }
  }
  l = el.length;
  let proto = {
    each: (fn)            => {
      for(let i = 0; i < el.length; i++) {
        fn.call($(el[i]), el[i], i);
      }
      return el;
    },
    einblenden: (d)       => el.each(o => o.style.display = d || "block"),
    ausblenden: (d)       => el.each(o => o.style.display = d || "none"),
    getHTML: ()           => el[0].innerHTML,
    setHTML: (html)       => el.each(o => o.innerHTML = html),
    getAttr: (attr)       => el[0].getAttribute(attr),
    setAttr: (attr, wert) => el.each(o => o.setAttribute(attr, wert)),
    // setCss: Nimmt entweder: ("CSS-Property", "Wert") oder ({CSS-Property: "Wert", CSS-Property2: "Wert", ...})
    setCss: (k, v)        => {
      if(typeof k === "object") {
        for(let kk in k) {
          el.each(o => o.style[kk] = k[kk]);
        }
        return el;
      }
      return el.each(o => o.style[k] = v);
    },
    getCss: (property)    => el[0].style[property],
    setWert: (val)        => el.each(o => o.value = val),
    getWert: ()           => el[0].value,
    removeKlasse: (...k)  => el.each(o => o.classList.remove(...k)),
    addKlasse: (...k)     => el.each(o => o.classList.add(...k)),
    setKlasse: (b, ...k)  => {
      if (b) {
        return el.addKlasse(...k);
      }
      return el.removeKlasse(...k);
    },
    ist: (s)              => el[0].matches(s),
    parent: ()            => $(el[0].parentNode),
    children: (s)         => {
      if (s === undefined) {
        return $(...el[0].childNodes);
      }
      return $(...el[0].querySelectorAll(">".s));
    },
    find: (s)             => $(...el[0].querySelectorAll(s)),
  };
  el.__proto__ = proto;
  el.length = l;
  return el;
}
