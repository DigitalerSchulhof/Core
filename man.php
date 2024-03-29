<?php
  header("Content-Type: application/manifest+json");
  $DSH_MODULE = __DIR__."/module";

  $DSH_DATENBANKEN = [];

  include __DIR__."/core/config.php";
  include __DIR__."/core/funktionen.php";
  include __DIR__."/core/angebote.php";
  include __DIR__."/core/include.php";

  use Core\Einbinden;

  Einbinden::modulLaden("UI", true, false);
  Einbinden::modulLaden("Kern", true, false);

  $DBS->anfrage("SELECT wert_h FROM kern_styles WHERE bezeichnung = 'akzent1'")
        ->werte($thema1);
  $thema1hex = UI\Generieren::RgbaZuHex($thema1);
?>
{
  "name": "Digitaler Schulhof",
  "short_name": "DSH",
  "start_url": "/",
  "display": "standalone",
  "background_color": "<?php echo $thema1hex; ?>",
  "theme_color": "<?php echo $thema1hex; ?>",
  "description": "Der Digitaler Schulhof des @TODO: Name.",
  "icons": [{
    "src": "dateien/schulspezifisch/favicon/48.png",
    "sizes": "48x48",
    "type": "image/png"
  }, {
    "src": "dateien/schulspezifisch/favicon/72.png",
    "sizes": "72x72",
    "type": "image/png"
  }, {
    "src": "dateien/schulspezifisch/favicon/96.png",
    "sizes": "96x96",
    "type": "image/png"
  }, {
    "src": "dateien/schulspezifisch/favicon/144.png",
    "sizes": "144x144",
    "type": "image/png"
  },<?php /* {
    "src": "@TODO: 168",
    "sizes": "168x168",
    "type": "image/png"
  }, */?>{
    "src": "dateien/schulspezifisch/favicon/192.png",
    "sizes": "192x192",
    "type": "image/png"
  }],
  "related_applications": [{
    "platform": "play",
    "url": "https://play.google.com/store/apps/details?id=com.dsh.digitalerschulhof",
    "id": "com.dsh.digitalerschulhof"
  },
  {
    "platform": "itunes",
    "url": "https://apps.apple.com/de/app/digitaler-schulhof/id1500912100",
    "id": "1500912100"
  }],
  "shortcuts": [{
    "name": "Website öffnen",
    "short_name": "Website",
    "description": "Öffnet direkt die Website",
    "url": "/",
    "icons": [{
      "src": "dateien/schulspezifisch/favicon/96.png",
      "sizes": "96x96",
      "purpose": "any"
    }]
  },
  {
    "name": "Schulhof öffnen",
    "short_name": "Schulhof",
    "description": "Öffnet direkt den Schulhof",
    "url": "/Schulhof",
    "icons": [{
      "src": "dateien/schulspezifisch/favicon/96.png",
      "sizes": "96x96",
      "purpose": "any"
    }]
  },
  {
    "name": "Stundenplan öffnen",
    "short_name": "Stundenplan",
    "description": "Öffnet direkt den Stundenplan",
    "url": "/Schulhof/Stundenplan",
    "icons": [{
      "src": "dateien/schulspezifisch/favicon/96.png",
      "sizes": "96x96",
      "purpose": "any"
    }]
  }]
}