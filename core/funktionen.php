<?php

function style($css) {
	$cb = substr(sha1(filemtime(__DIR__."/../$css")),0, 7);
	return "<link rel=\"stylesheet\" href=\"$css?$cb\">";
}

?>