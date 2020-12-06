interface eQueryInterface {
  [n: number]: HTMLElement;

  el: HTMLElement[],
  eQuery: true,
  each: (fn: (this: eQuery, element: HTMLElement, index: number) => void) => eQuery;

  einblenden: (d?: string) => eQuery;
  ausblenden: (d?: string) => eQuery;

  getHTML: () => string;
  setHTML: (html: string) => eQuery;

  getText: () => string;
  setText: (html: string) => eQuery;

  hatAttr: (attr: string) => boolean;
  getAttr: (attr: string) => string | null;
  setAttr: (attr: string, wert: string | null) => eQuery;

  getID: () => string | null;
  setID: (id: string) => eQuery;

  toggleCss: (k: string, a: string, b?: string) => eQuery;

  getCss: ((k: string) => string) & ((k: string[]) => { [key: string]: string });

  setCss: ((k: string, v: string) => eQuery) & ((k: { [key: string]: string }) => eQuery);

  getWert: () => string;
  setWert: (val: string) => eQuery;
  getEditor: () => string;

  hatKlasse: (k: string) => boolean;
  setKlasse: (b: boolean, ...k: string[]) => eQuery;
  toggleKlasse: (...k: string[]) => eQuery;
  addKlasse: (...k: string[]) => eQuery;
  removeKlasse: (...k: string[]) => eQuery;

  ist: (s: string | HTMLElement) => boolean;
  parent: (s?: string) => eQuery;
  kinder: (s?: string) => eQuery;
  finde: (s: string) => eQuery;

  siblingVor: () => eQuery;
  siblingNach: () => eQuery;
  siblings: (s?: string) => eQuery;

  existiert: () => boolean;
  entfernen: () => eQuery;
  anhaengen: (...e: (string | HTMLElement | eQuery)[]) => eQuery;

  filter: (filter: string | ((this: HTMLElement, element: eQuery) => boolean)) => eQuery;
  ersetzen: (e: HTMLElement) => eQuery;
}

export type eQuery = eQueryInterface & Array<HTMLElement>;

const $ = (...args: (string | Element | undefined | null | EventTarget)[]): eQuery => {
  const obj: eQueryInterface = {
    el: [],
    eQuery: true,
    each: (fn: (this: eQuery, element: HTMLElement, index: number) => void): eQuery => {
      for (let i = 0; i < obj.el.length; i++) {
        fn.call($(obj.el[i]), obj.el[i], i);
      }
      return obj as eQuery;
    },
    einblenden: (d) => obj.each(o => o.style.display = d || "block"),
    ausblenden: (d) => obj.each(o => o.style.display = d || "none"),
    getHTML: () => (obj.el[0]).innerHTML,
    setHTML: (html) => obj.each(o => o.innerHTML = html),
    getText: () => (obj.el[0]).innerText,
    setText: (text) => obj.each(o => o.innerText = text),
    hatAttr: (attr) => (obj.el[0]).hasAttribute(attr),
    getAttr: (attr) => (obj.el[0]).getAttribute(attr),
    setAttr: (attr, wert) => obj.each(o => wert === null ? o.removeAttribute(attr) : o.setAttribute(attr, wert)),
    getID: () => obj.getAttr("id"),
    setID: (id) => obj.setAttr("id", id),
    toggleCss: (k, a, b) => {
      return obj.each(function () {
        if (this.getCss(k) === a) {
          this.setCss(k, b || "");
        } else {
          this.setCss(k, a);
        }
      });
    },
    getCss: (k: string | string[]) => {
      if (typeof k === "string") {
        return obj.el[0].style[k as any];
      }
      const r: any = {};
      for (const p of k as (keyof CSSStyleDeclaration)[]) {
        r[p] = obj.el[0].style[p];
      }
      return r;
    },
    setCss: (k: string | { [key: string]: string }, v?: string) => {
      if (typeof k === "string" && typeof v === "string") {
        return obj.each(o => o.style[k as any] = v);
      }
      for (const kk in k as { [key: string]: string }) {
        obj.each(o => o.style[kk as any] = (k as { [key: string]: string })[kk]);
      }
      return obj as eQuery;
    },
    getWert: () => (obj.el[0] as HTMLInputElement).value,
    setWert: (val) => obj.each(o => (o as HTMLInputElement).value = val),
    getEditor: () => (obj.el[0] as HTMLInputElement).value,
    hatKlasse: (k) => {
      let r = true;
      obj.each(o => o.classList.contains(k) || (r = false));
      return r;
    },
    setKlasse: (b, ...k) => {
      if (b) {
        return obj.addKlasse(...k);
      }
      return obj.removeKlasse(...k);
    },
    toggleKlasse: (...k) => {
      k.forEach(k => obj.each(function () { this.setKlasse(!this.hatKlasse(k), k); }));
      return obj as eQuery;
    },
    addKlasse: (...k) => obj.each(o => o.classList.add(...k)),
    removeKlasse: (...k) => obj.each(o => o.classList.remove(...k)),
    ist: (s) => {
      if (typeof s === "string") {
        return (obj.el[0]).matches(s);
      }
      return (obj.el[0]).isSameNode(s);
    },
    parent: (s) => {
      if (s === undefined) {
        return $((obj.el[0]).parentNode as HTMLElement);
      }
      return $(((obj.el[0]).parentNode as HTMLElement).closest(s) as HTMLElement);
    },
    kinder: (s) => {
      if (s === undefined) {
        return $(...(obj.el[0]).children as any);
      }
      const l: any = [];
      obj.each(o => {
        for (const ob of o.children) {
          if (ob.matches(s)) {
            l.push(ob);
          }
        }
      });
      return $(...l);
    },
    finde: (s) => {
      const l: Element[] = [];
      obj.each(o => l.push(...o.querySelectorAll(s)));
      return $(...l);
    },
    siblingVor: () => $((obj.el[0]).previousSibling as HTMLElement),
    siblingNach: () => $((obj.el[0]).nextSibling as HTMLElement),
    siblings: (s) => {
      if (s === undefined) {
        const l: HTMLElement[] = [];
        obj.each(o => l.push(...$(...(o.parentNode as HTMLElement || { children: [] }).children as any).filter(ob => !ob.ist(o)) as any));
        return $(...l);
      }
      return obj.siblings().filter(s);
    },
    existiert: () => obj.el.length > 0,
    entfernen: () => obj.each(o => o.remove()),
    anhaengen: (...e) => {
      for (const el of e) {
        let a: eQuery;
        if (el instanceof HTMLElement) {
          a = $(el);
        } else if (typeof el === "string") {
          a = ($ as any).parse(el);
        } else if (el.eQuery) {
          a = el;
        } else {
          console.error("Ungültiger Parameter: ", el);
          continue;
        }
        obj.each(o => a.each(e => o.append(e)));
      }
      return obj as eQuery;
    },
    filter: (filter) => {
      if (typeof filter === "string") {
        return obj.filter(e => e.ist(filter) as boolean);
      }
      const el: HTMLElement[] = [];
      obj.each(o => {
        if (filter.call(o, $(o))) {
          el.push(o);
        }
      });
      return $(...el);
    },
    ersetzen: (e) => obj.each(o => o.replaceWith(e))
  };

  for (const a of args) {
    // Purer Text oder Sonstiges
    if (!a || ((a as Element).nodeType && (a as Element).nodeType === 3)) {
      continue;
    }
    if (a instanceof HTMLElement) {
      obj.el.push(a);
    } else if (typeof a === "string") {
      obj.el.push(...document.querySelectorAll<HTMLElement>(a));
    } else if (a === undefined || a === null) {
      // Passiert...
    } else {
      console.error("Ungültiger eQuery-Parameter: ", a);
    }
  }

  const r: eQuery = obj as eQuery;
  // @ts-ignore
  r.__proto__ = obj.el;
  return r as eQuery;
};

export const eQuery = {
  parse: (code: string): eQuery => {
    const t = document.createElement("template");
    t.innerHTML = code;
    return $(...t.content.children);
  }
};

export default $;