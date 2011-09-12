<?php

/**
 * Создание пункта меню для управления ямами
 */

IncludeModuleLangFile(__FILE__);

global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/greensight.css');

$aMenu = array();

$aMenu[] = array(
		"parent_menu" => "global_menu_content",
		"section"     => "GENERAL",
		"sort"        => 1,
		"text"        => GetMessage('GREENSIGHT'),
		"title"       => GetMessage('GREENSIGHT'),
		"icon"        => "greensight_menu_icon_main",
		"page_icon"   => "greensight_menu_icon_page",
		"items_id"    => "greensight",
		"url"         => "greensight_holes_list.php",
		"items"       => array
		(
			array
			(
				"text"     => GetMessage('GREENSIGHT_ST1234'),
				"url"      => "greensight_holes_list.php",
				"more_url" => array(),
				"title"    => GetMessage('GREENSIGHT_ST1234')
			),
			array
			(
				"text"     => GetMessage('GREENSIGHT_ST1234_ALARM'),
				"url"      => "greensight_holes_alarm.php",
				"more_url" => array(),
				"title"    => GetMessage('GREENSIGHT_ST1234_ALARM')
			)
		)
	);

return $aMenu;

?>