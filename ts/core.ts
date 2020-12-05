/**
 * The BeforeInstallPromptEvent is fired at the Window.onbeforeinstallprompt handler
 * before a user is prompted to "install" a web site to a home screen on mobile.
 *
 * @deprecated Only supported on Chrome and Android Webview.
 */
interface BeforeInstallPromptEvent extends Event {

  /**
   * Returns an array of DOMString items containing the platforms on which the event was dispatched.
   * This is provided for user agents that want to present a choice of versions to the user such as,
   * for example, "web" or "play" which would allow the user to chose between a web version or
   * an Android version.
   */
  readonly platforms: Array<string>;

  /**
   * Returns a Promise that resolves to a DOMString containing either "accepted" or "dismissed".
   */
  readonly userChoice: Promise<{
    outcome: "accepted" | "dismissed",
    platform: string
  }>;

  /**
   * Allows a developer to show the install prompt at a time of their own choosing.
   * This method returns a Promise.
   */
  prompt(): Promise<void>;

}

import $ from "ts/eQuery";

window.console.log("%cHalt!", "font-weight: bold; font-style: dshStandard,sans-serif; color: #EF5350; text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000; font-size: 36px;");
window.console.log("%cDiese Konsole ist für Entwickler gedacht. Geben Sie hier nichts ein, was Sie nicht selbst verstehen!\nDie Benutzung erfolgt auf eigene Gefahr!", "font-style: dshStandard,sans-serif; color: #EF5350; font-weight: bold; font-size: 16px;text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;");
window.console.log("%cWenn Sie wissen, was Sie tun, freuen wir uns über einen Besuch auf GitHub :)", "font-style: dshStandard,sans-serif; color: #F5F5F5; font-weight: bold; font-size: 12px;text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;");
const core: any = {};
if ("serviceWorker" in navigator) {
  navigator.serviceWorker.register("sw.js")
    .then(() => {
      console.log("Service Worker registriert.");
    })
    .catch(() => {
      console.log("Service Worker nicht registriert.");
    });
}
core.a2hs = {
  prompt: null as any,
  handler: (e: any) => {
    e.preventDefault();
    core.a2hs.prompt = e;
    const box = $("#dshPWAInstallation");
    box.einblenden();
  },
  install: () => {
    const box = $("#dshPWAInstallation");
    core.a2hs.prompt.prompt();
    box.addKlasse("dshUiKnopfLeer");
    core.a2hs.prompt.userChoice.then((r: any) => {
      box.removeKlasse("dshUiKnopfLeer");
      if (r.outcome === "accepted") {
        console.log("A2HS akzeptiert");
        box.ausblenden();
      } else {
        console.log("A2HS doch nicht");
        box.einblenden();
      }
      core.a2hs.prompt = undefined;
    });
  }
};

export const beforeinstallprompt = (e: BeforeInstallPromptEvent): void => {
  core.a2hs.handler(e);
};

export const dshSeiteGeladen = (): void => {
  if (core.a2hs.prompt !== null) {
    const box = $("#dshPWAInstallation");
    box.einblenden();
  }
};