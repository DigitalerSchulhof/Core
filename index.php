<?php
	namespace Core;

	$DSH_URLGANZ = $_GET["URL"] ?? "";
	$DSH_URL = explode("/", $DSH_URLGANZ);
	$DSH_MODULE = __DIR__."/module";
	
	$DSH_LINKMUSTER = "[\.\-a-zA-Z0-9äöüßÄÖÜ()_]*[\-a-zA-Z0-9äöüßÄÖÜ()_]";
?>
<html>
	<head>

	</head>
	<body>
		<?php
			include __DIR__."/core/include.php";
		?>
	</body>
</html>
