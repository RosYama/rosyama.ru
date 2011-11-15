<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/// Подключение модуля для работы с записями ям
if(!CModule::IncludeModule('greensight_utils'))
{
	die();
}

/// По умолчанию версия карт 1.0
if (!isset($arParams['YANDEX_VERSION']))
	$arParams['YANDEX_VERSION'] = '1.0';

$arParams['KEY'] = trim($arParams['KEY']);

/// Определение ключа карты по адресу сервера или из настроек модуля
if (!$arParams['KEY'] && !$arParams['WAIT_FOR_EVENT'])
{
	$MAP_KEY = '';
	$strMapKeys = COption::GetOptionString('fileman', 'map_yandex_keys');
	$strDomain = $_SERVER['HTTP_HOST'];
	$wwwPos = strpos($strDomain, 'www.');
	if ($wwwPos === 0)
		$strDomain = substr($strDomain, 4);

	if ($strMapKeys)
	{
		$arMapKeys = unserialize($strMapKeys);
		
		if (array_key_exists($strDomain, $arMapKeys))
			$MAP_KEY = $arMapKeys[$strDomain];
	}
	$MAP_KEY = '';
	$strMapKeys = COption::GetOptionString('fileman', 'map_yandex_keys');

	$strDomain = $_SERVER['HTTP_HOST'];
	$wwwPos = strpos($strDomain, 'www.');
	if ($wwwPos === 0)
		$strDomain = substr($strDomain, 4);

	if ($strMapKeys)
	{
		$arMapKeys = unserialize($strMapKeys);
		
		if (array_key_exists($strDomain, $arMapKeys))
			$MAP_KEY = $arMapKeys[$strDomain];
	}
	if (!$MAP_KEY)
	{
		ShowError(GetMessage('MYMS_ERROR_NO_KEY'));
		return;
	}
	else
		$arParams['KEY'] = $MAP_KEY;
}

$arParams['DEV_MODE'] = $arParams['DEV_MODE'] == 'Y' ? 'Y' : 'N';

if (!defined('BX_YMAP_SCRIPT_LOADED'))
{
	if ($arParams['DEV_MODE'] != 'Y')
	{
		/// Добавление java скрипта карты
		$APPLICATION->AddHeadString(
			'<script src="http://api-maps.yandex.ru/'.$arParams['YANDEX_VERSION'].'/?key='.$arParams['KEY'].'&wizard=bitrix" type="text/javascript" charset="utf-8"></script>'
		);
		define('BX_YMAP_SCRIPT_LOADED', 1);
	}
}

$arParams['MAP_ID'] = 
	(strlen($arParams["MAP_ID"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ? 
	'MAP_'.RandString() : $arParams['MAP_ID']; 

if(isset($_COOKIE['map_settings']))
{
	$res = json_decode(str_replace("'", '"', $_COOKIE['map_settings']), true);
	$coords = explode(',', $res['center']);
	$arParams['INIT_MAP_LON'] = (float) $coords[0];
	$arParams['INIT_MAP_LAT'] = (float) $coords[1];
	$arParams['INIT_MAP_SCALE'] = (int) $res['zoom'];
}
else
{
	if($_SERVER['HTTP_X_FORWARDED_FOR'])
	{
		$forwarded_for = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$forwarded_for = $forwarded_for[0];
	}
	
	list($arParams['INIT_MAP_LAT'], $arParams['INIT_MAP_LON']) = array_values(CGreensightGeoip::GetCoordinatesByIP($forwarded_for ? $forwarded_for : $_SERVER['REMOTE_ADDR']));
	
	/// Массив данных масштаба и положения карты
	if(!$arParams['INIT_MAP_LAT'] || !$arParams['INIT_MAP_LON'])
	{
		$arParams['INIT_MAP_LON'] = floatval($arParams['INIT_MAP_LON']);
		$arParams['INIT_MAP_LON'] = $arParams['INIT_MAP_LON'] ? $arParams['INIT_MAP_LON'] : 37.64;
		$arParams['INIT_MAP_LAT'] = floatval($arParams['INIT_MAP_LAT']);
		$arParams['INIT_MAP_LAT'] = $arParams['INIT_MAP_LAT'] ? $arParams['INIT_MAP_LAT'] : 55.76;
	}
	$arParams['INIT_MAP_SCALE'] = intval($arParams['INIT_MAP_SCALE']);
	$arParams['INIT_MAP_SCALE'] = $arParams['INIT_MAP_SCALE'] ? $arParams['INIT_MAP_SCALE'] : 10;
}

$arResult['ALL_MAP_TYPES'] = array('MAP', 'SATELLITE', 'HYBRID');
$arResult['ALL_MAP_OPTIONS'] = array('ENABLE_SCROLL_ZOOM' => 'ScrollZoom', 'ENABLE_DBLCLICK_ZOOM' => 'DblClickZoom', 'ENABLE_DRAGGING' => 'Dragging', 'ENABLE_HOTKEYS' => 'HotKeys', 'ENABLE_RULER' => 'Ruler');
$arResult['ALL_MAP_CONTROLS'] = array('TOOLBAR' => 'ToolBar', 'ZOOM' => 'Zoom', 'SMALLZOOM' => 'SmallZoom', 'MINIMAP' => 'MiniMap', 'TYPECONTROL' => 'TypeControl', 'SCALELINE' => 'ScaleLine');

if (!$arParams['INIT_MAP_TYPE'] || !in_array($arParams['INIT_MAP_TYPE'], $arResult['ALL_MAP_TYPES']))
	$arParams['INIT_MAP_TYPE'] = 'MAP';

if (!is_array($arParams['OPTIONS']))
	$arParams['OPTIONS'] = array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING');
else
{
	foreach ($arParams['OPTIONS'] as $key => $option)
	{
		if (!$arResult['ALL_MAP_OPTIONS'][$option])
			unset($arParams['OPTIONS'][$key]);
	}
	
	$arParams['OPTIONS'] = array_values($arParams['OPTIONS']);
}

if (!is_array($arParams['CONTROLS']))
	$arParams['CONTROLS'] = array('TOOLBAR', 'ZOOM', 'MINIMAP', 'TYPECONTROL', 'SCALELINE');
else
{
	foreach ($arParams['CONTROLS'] as $key => $control)
	{
		if (!$arResult['ALL_MAP_CONTROLS'][$control])
			unset($arParams['CONTROLS'][$key]);
	}
	
	$arParams['CONTROLS'] = array_values($arParams['CONTROLS']);
}

$arParams['MAP_WIDTH'] = trim($arParams['MAP_WIDTH']);
if (ToUpper($arParams['MAP_WIDTH']) != 'AUTO' && substr($arParams['MAP_WIDTH'], -1, 1) != '%')
{
	$arParams['MAP_WIDTH'] = intval($arParams['MAP_WIDTH']);
	if ($arParams['MAP_WIDTH'] <= 0) $arParams['MAP_WIDTH'] = 600;
	$arParams['MAP_WIDTH'] .= 'px';
}

$arParams['MAP_HEIGHT'] = trim($arParams['MAP_HEIGHT']);
if (substr($arParams['MAP_HEIGHT'], -1, 1) != '%')
{
	$arParams['MAP_HEIGHT'] = intval($arParams['MAP_HEIGHT']);
	if ($arParams['MAP_HEIGHT'] <= 0) $arParams['MAP_HEIGHT'] = 500;
	$arParams['MAP_HEIGHT'] .= 'px';
}

$this->IncludeComponentTemplate();
?>