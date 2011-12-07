<?
$arUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^/sprav/detail/([0-9]+)#",
		"RULE"	=>	"ID=$1",
		"ID"	=>	"",
		"PATH"	=>	"/sprav/detail/index.php",
	),
	array(
		"CONDITION"	=>	"#^/sprav/prosecutor/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	"/sprav/prosecutor/index.php",
	),
	array(
		"CONDITION"	=>	"#^/([0-9]+)/#",
		"RULE"	=>	"ID=$1",
		"PATH"	=>	"/index.php",
	),
	array(
		"CONDITION"	=>	"#^/([0-9]+)#",
		"RULE"	=>	"ID=$1",
		"PATH"	=>	"/index.php",
	),
	array(
		"CONDITION"	=>	"#^/gibdd/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	"/gibdd/index.php",
	),
);

?>