window.console.log("%cHalt!", "font-weight: bold; font-style: dshStandard,sans-serif; color: #EF5350; text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000; font-size: 36px;")
window.console.log("%cDiese Konsole ist für Entwickler gedacht. Geben Sie hier nichts ein, was Sie nicht selbst verstehen!\nDie Benutzung erfolgt auf eigene Gefahr!","font-style: dshStandard,sans-serif; color: #EF5350; font-weight: bold; font-size: 16px;text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;");
window.console.log("%cWenn Sie wissen, was Sie tun, freuen wir uns über einen Besuch auf GitHub :)","font-style: dshStandard,sans-serif; color: #F5F5F5; font-weight: bold; font-size: 12px;text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;");
var core = {};
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register("sw.js")
  .then(function(registration) {
    console.log("Service Worker registriert.");
  })
  .catch(function(error) {
    console.log("Service Worker nicht registriert.");
  });
}
core.a2hs = {
  prompt: null,
  handler: e => {
    e.preventDefault();
    core.a2hs.prompt = e;
    let box = $("#dshPWAInstallation");
    box.einblenden();
  },
  install: _ => {
    let box = $("#dshPWAInstallation");
    core.a2hs.prompt.prompt();
    box.addKlasse("dshUiKnopfLeer");
    core.a2hs.prompt.userChoice.then(r => {
      box.removeKlasse("dshUiKnopfLeer");
      if (r.outcome === 'accepted') {
        console.log('A2HS akzeptiert');
        box.ausblenden();
      } else {
        console.log('A2HS doch nicht');
        box.einblenden();
      }
      core.a2hs.prompt = undefined;
    });
  }
};
window.addEventListener('beforeinstallprompt', (e) => {
  core.a2hs.handler(e);
});
window.addEventListener("dshSeiteGeladen", _ => {
  if(core.a2hs.prompt !== null) {
    let box = $("#dshPWAInstallation");
    box.einblenden();
  }
});