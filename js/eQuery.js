/**
 * eQuery
 * @param  {mixed} arguments
 *  1 Parameter: [String: Selector; Element: Element]
 *  2 Parameter: [Element: (2. Parameter String: Elternteil; 2. Parameter Element: Erstes Element)][String: Kind-Selektor; Element: Zweites Element]
 *  n Parameter: [String: Selector; Element: Element]...
 */
var $ = (...arguments) => {
  let el = []
  if(arguments.length === 1) {
    if(arguments[0] instanceof HTMLElement) {
      el = arguments;
    } else if(typeof arguments[0] === "string") {
      el = document.querySelectorAll(arguments[0]);
    }
  } else if(arguments.length === 2) {
    if(arguments[0] instanceof HTMLElement) {
      if(arguments[1] instanceof HTMLElement) {
        el = arguments;
      } else if(typeof arguments[1] === "string") {
        el = arguments[0].querySelectorAll(arguments[1]);
      }
    } else {
      if(typeof arguments[0] === "string") {
        el.push(...document.querySelectorAll(arguments[0]));
      }
      if(typeof arguments[1] === "string") {
        el.push(...document.querySelectorAll(arguments[1]));
      }
    }
  } else {
    for(let a of arguments) {
      if(a instanceof HTMLElement) {
        el.push(a);
      } else if(typeof a === "string") {
        el.push(document.querySelectorAll(...a));
      }
    }
  }
  l = el.length;
  let proto = {
    each: (fn)    => {
      for(let i = 0; i < el.length; i++) {
        fn.call($(el[i]), el[i], i);
      }
      return el;
    },
    her: ()               => el.each(o => o.style.display = ""),
    weg: ()               => el.each(o => o.style.display = "none"),
    html: (c)             => c === undefined ? el[0].innerHTML        : el.each(o => o.innerHTML = c),
    attr: (k, v)          => v === undefined ? el[0].getAttribute(k)  : el.each(o => o.setAttribute(k, v)),
    css: (k, v)           => {
      if(typeof k === "object") {
        for(let kk in k) {
          el.each(o => o.style[kk] = k[kk]);
        }
        return el;
      }
      if(v === undefined) {
        return el[0].style[k];
      }
      return el.each(o => o.style[k] = v);
    },
    wert: (v)             => v === undefined ? el[0].value            : el.each(o => o.value = v),
    removeKlasse: (...k)  => el.each(o => o.classList.remove(...k)),
    addKlasse: (...k)     => el.each(o => o.classList.add(...k)),
    setKlasse: (b, ...k)  => b ? el.addKlasse(...k) : el.removeKlasse(...k),
    is: (s)               => el[0].matches(s),
    parent: ()            => $(el[0].parentNode),
    children: (s)         => s === undefined ? $(...el[0].childNodes) : $(...el[0].querySelectorAll(">".s)),
    find: (s)             => $(...el[0].querySelectorAll(s)),
  };
  el.__proto__ = proto;
  el.length = l;
  return el;
}