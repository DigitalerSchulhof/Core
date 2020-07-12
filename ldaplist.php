<?php
namespace Core;

$DSH_URLGANZ = $_GET["URL"] ?? "";
$DSH_URL = explode("/", $DSH_URLGANZ);
$DSH_MODULE = __DIR__."/module";
$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]{3,}";

$DSH_DATENBANKEN = array();

include __DIR__."/core/include.php";
aktuellesModulBestimmen();
$DSH_DATENBANKEN = array("schulhof");
modulLaden("Kern", true, false);

echo "<!DOCTYPE html>";
echo "<html>";
	echo "<head>";
		echo "<link rel=\"stylesheet\" href=\"css/hell.css\">";
		if(!isset($_COOKIE["dunkelmodus"])) {
			echo "<link rel=\"stylesheet\" href=\"css/dunkel.css\">";
		}
		if($_COOKIE["dunkelmodus"] ?? "nein" == "ja") {
			echo "<link rel=\"stylesheet\" href=\"css/dunkelroh.css\">";
		}
	echo "</head>";
	echo "<body>";
		echo "<div id=\"dsh_hauptteil\">";
			echo "<form action=\"ldapanmelden.php\" method=\"post\">";
				echo "<input type=\"text\" name=\"user\" id=\"user\">";
				echo "<input type=\"password\" name=\"pass\" id=\"pass\">";
				echo "<input type=\"submit\" name=\"go\" id=\"go\" value=\"Abschicken\">";
			echo "</form>";

			session_start();
			if (isset($_SESSION['angemeldet']) && ($_SESSION['angemeldet'])) {
				if (isset($_SESSION['user'])) {
					echo "Angemeldet als: ".$_SESSION['user'];
				} else {
					echo "Angemeldet ohne CN.";
				}
			} else {
				echo "Bisher nicht angemedlet!";
			}

			// LDAP-VERBINDUNGSTEST
			$ldapverbindung = ldap_connect('ldap://localhost:10389');
			if ($ldapverbindung) {
				echo "<p>Verbindung hergestellt!</p>";
				ldap_set_option($ldapverbindung, LDAP_OPT_PROTOCOL_VERSION, 3);

				// ALLE NUTZER AUSLESEN
				$ldapbindung = @ldap_bind($ldapverbindung, "uid=admin,ou=system", "secret");
				if ($ldapbindung) {
					echo "<p>Anmeldung erfolgreich.</p>";

					$filter = "(cn=*)";
					$ldapsuche = ldap_search($ldapverbindung, "dc=dsh,dc=de", $filter);

					if ($ldapsuche) {
						$rueckgabe = ldap_get_entries($ldapverbindung, $ldapsuche);
						echo "<p>Suche erfolgreich:</p><pre>";
						print_r($rueckgabe);
						echo "</pre>";
					} else {
						echo "<p>Suche fehlgeschlagen!</p>";
					}

				} else {
					echo "<p>Anmeldung fehlgeschalgen.</p>";
				}

			} else {
				echo "<p>Keine Verbindung möglich!</p>";
			}
		echo "</div>";
	echo "</body>";
echo "</body>";
?>
<!-- Digitaler Schulhof - Version 1.2..4.5.6.87.8.. --><!-- 🍪 -->
