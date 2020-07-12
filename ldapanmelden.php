<?php
session_start();
$user = $_POST['user'];
$pass = $_POST['pass'];


function ad_auth($username, $password) {
	$ldap = ldap_connect('ldap://localhost:10389');
	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

	unset($_SESSION['angemeldet']);
	unset($_SESSION['user']);

	if (!$ldap) {
		return false;
	}

	if (@ldap_bind($ldap, "uid=".$username.",ou=Nutzer,dc=dsh,dc=de", $password)) {
		ldap_unbind($ldap);
		$_SESSION['angemeldet'] = true;


		$ldap = ldap_connect('ldap://localhost:10389');
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		$ldapbindung = @ldap_bind($ldap, "uid=admin,ou=system", "secret");
		if ($ldap) {
			$filter = "(uid=$username)";
			$ldapsuche = ldap_search($ldap, "dc=dsh,dc=de", $filter);

			if ($ldapsuche) {
				$rueckgabe = ldap_get_entries($ldap, $ldapsuche);
				$_SESSION['user'] = $rueckgabe[0]['cn'][0];
			}
		}

		return true;
	}
	else{
		return false;
	}
}

$angemeldet = ad_auth($user, $pass);
header("Location: ldaplist.php");
?>
