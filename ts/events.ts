import * as laden from "./laden";
import * as core from "./core";

document.addEventListener("click", laden.click);
window.addEventListener("load", laden.load);
window.addEventListener("popstate", laden.popstate);

window.addEventListener("beforeinstallprompt", core.beforeinstallprompt);
window.addEventListener("dshSeiteGeladen", core.dshSeiteGeladen);