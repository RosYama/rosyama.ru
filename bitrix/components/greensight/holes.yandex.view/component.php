<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['KEY'] = trim($arParams['KEY']);

$arParams['MAP_ID'] = 
	(strlen($arParams["MAP_ID"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ? 
	'MAP_'.RandString() : $arParams['MAP_ID']; 


/// Обработка списка объектов на карте
if (($strPositionInfo = $arParams['~MAP_DATA']) && CheckSerializedData($strPositionInfo) && ($arResult['POSITION'] = unserialize($strPositionInfo)))
{
	if (is_array($arResult['POSITION']) && is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS'])))
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arResult['POSITION']['PLACEMARKS'][$i]['TEXT'] = str_replace('###RN###', "\r\n", $arResult['POSITION']['PLACEMARKS'][$i]['TEXT']);
		}
	}

	if (is_array($arResult['POSITION']) && is_array($arResult['POSITION']['POLYLINES']) && ($cnt = count($arResult['POSITION']['POLYLINES'])))
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arResult['POSITION']['POLYLINES'][$i]['TITLE'] = str_replace('###RN###', "\r\n", $arResult['POSITION']['POLYLINES'][$i]['TITLE']);
		}
	}
}

$arResult['FILTER_STATES'] = array();
foreach($_REQUEST['STATE'] as $k => $v)
{
	$arResult['FILTER_STATES'][(int)$k] = htmlspecialcharsEx($v);
}
$arResult['FILTER_TYPES'] = array();
foreach($_REQUEST['TYPE'] as $k => $v)
{
	$arResult['FILTER_TYPES'][(int)$k] = htmlspecialcharsEx($v);
}

$this->IncludeComponentTemplate();

?>