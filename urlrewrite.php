<?
$arUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^/([0-9]+)/#",
		"RULE"	=>	"ID=$1",
		"PATH"	=>	"/index.php",
	),
	array(
		"CONDITION"	=>	"#^/([0-9]+)#",
		"RULE"	=>	"ID=$1",
		"PATH"	=>	"/index.php",
	)
);

?>