<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карта дефектов");
?>

<div id="addmess" style="display:none; color:#0C0"><p><b>Выберите место на карте и кликните по нему два раза, чтобы отметить расположение ямы.</b></p></div>

<?php require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php'); ?>
<?$APPLICATION->IncludeComponent("greensight:holes.yandex.view", ".default", array(
	"KEY" => CIBlockPropertyMapYandex::_GetMapKey("yandex",$_SERVER["SERVER_NAME"]),
	"INIT_MAP_TYPE" => "MAP",
	"MAP_DATA" => "a:3:{s:10:\"yandex_lat\";s:7:\"55.7383\";s:10:\"yandex_lon\";s:7:\"37.5946\";s:12:\"yandex_scale\";i:10;}",
	"MAP_WIDTH" => "100%",
	"MAP_HEIGHT" => "600",
	"CONTROLS" => array(
		0 => "TOOLBAR",
		1 => "ZOOM",
		2 => "MINIMAP",
		3 => "TYPECONTROL",
		4 => "SCALELINE",
	),
	"OPTIONS" => array(
		0 => "ENABLE_SCROLL_ZOOM",
		1 => "ENABLE_DBLCLICK_ZOOM",
		2 => "ENABLE_DRAGGING",
	),
	"MAP_ID" => ""
	),
	false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>