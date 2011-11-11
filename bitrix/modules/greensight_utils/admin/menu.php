<?php

/**
 * Создание пункта меню для гринсайтовских утилиток
 */

IncludeModuleLangFile(__FILE__);

global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/greensight.css');

$aMenu = array();

$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"section"     => "GENERAL",
		"sort"        => 1,
		"text"        => GetMessage('GREENSIGHT'),
		"title"       => GetMessage('GREENSIGHT'),
		"icon"        => "greensight_menu_icon_main",
		"page_icon"   => "greensight_menu_icon_page",
		"items_id"    => "greensight_utils",
		"url"         => "greensight_iblock_export.php",
		"items"       => array
		(
			array
			(
				"text"     => GetMessage('GREENSIGHT_IBLOCK_STRUCTURE_EXPORT'),
				"url"      => "greensight_iblock_export.php",
				"more_url" => array(),
				"title"    => GetMessage('GREENSIGHT_IBLOCK_STRUCTURE_EXPORT')
			),
			array
			(
				"text"     => GetMessage('GREENSIGHT_IBLOCK_STRUCTURE_IMPORT'),
				"url"      => "greensight_iblock_import.php",
				"more_url" => array(),
				"title"    => GetMessage('GREENSIGHT_IBLOCK_STRUCTURE_IMPORT')
			),
			array
			(
				"text"     => GetMessage('GREENSIGHT_UPDATE'),
				"url"      => "greensight_update.php",
				"more_url" => array(),
				"title"    => GetMessage('GREENSIGHT_UPDATE')
			)
		)
	);

return $aMenu;

?>