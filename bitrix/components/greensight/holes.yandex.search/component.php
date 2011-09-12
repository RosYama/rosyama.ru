<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/*************************************************** Модуль вывода записей ям и вывода их на карту ****************************/



/// Параметр ключа карты Google Map
$arParams['KEY'] = trim($arParams['KEY']);


/// Выборка записей,  принадлежащих текущему пользователю
$_holes = C1234Hole::GetList
(
	array
	(
		'ID' => 'desc'
	),
	array
	(
		'USER_ID' => $USER->GetID()  /// Выборка записей текущего пользователя
	)
);


/// Обход массива записей 
foreach($_holes as &$hole)
{
	switch($hole['STATE'])  /// Разбор записей в зависимости от статуса ям и вычисление срока со дня создания
	{
		case 'fresh':  /// новая
		{
			$arResult['HOLES']['FRESH'][] = $hole;
			break;
		}
		case 'inprogress': // в процессе устранения
		{
			if($hole['DATE_SENT'])
			{
				$hole['WAIT_DAYS'] = 38 - ceil((time() - $hole['DATE_SENT']) / 86400);
				$hole['WAIT_DAYS'] = GetMessage('WAIT').' '.(string)$hole['WAIT_DAYS'];
				$last_digit = (int)substr($hole['WAIT_DAYS'], strlen($hole['WAIT_DAYS']) - 1);
				if(substr($hole['WAIT_DAYS'], strlen($hole['WAIT_DAYS']) - 2, 1) == '1')
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 4 || !$last_digit)
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 1)
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS2');
				}
				else
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAY');
				}
			}
			$arResult['HOLES']['INPROGRESS'][] = $hole;
			break;
		}
		case 'achtung': // не заделанная
		{
			if($hole['DATE_SENT'])
			{
				$hole['PAST_DAYS'] = GetMessage('PAST').' '.(string)(ceil((time() - $hole['DATE_SENT']) / 86400) - 37);
				$last_digit = (int)substr($hole['PAST_DAYS'], strlen($hole['PAST_DAYS']) - 1);
				if(substr($hole['PAST_DAYS'], strlen($hole['PAST_DAYS']) - 2, 1) == '1')
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 4 || !$last_digit)
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 1)
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAYS2');
				}
				else
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAY');
				}
			}
			$arResult['HOLES']['INPROGRESS'][] = $hole;
			break;
		}
		case 'fixed'; /// заделанная
		default:
		{
			$arResult['HOLES']['FIXED'][] = $hole;
			break;
		}
	}
	
	//// Массив точек на карте	
	$arResult['POSITION']['PLACEMARKS'][$f]["TYPE"]     = $hole['TYPE'];       // Тип ямы    
	$arResult['POSITION']['PLACEMARKS'][$f]["LON"]      = $hole["LONGITUDE"];  // Долгота
	$arResult['POSITION']['PLACEMARKS'][$f]["LAT"]      = $hole["LATITUDE"];   // Широта
	$arResult['POSITION']['PLACEMARKS'][$f]["TEXT"]     = $hole["TYPE"];       // Текст для метки
	$arResult['POSITION']['PLACEMARKS'][$f]["COMMENT1"] = $hole["COMMENT1"];   // Комментарий
	$arResult['POSITION']['PLACEMARKS'][$f]["STATE"]    = $hole["STATE"];      // Состояние
	
	
	}
	
	
	
	
	
	
	
}
		
//// Определение ключа для карты из настроек модуля и адреса сервера

if (!$arParams['KEY'])
{
	$MAP_KEY = '';
	$strMapKeys = COPtion::GetOptionString('fileman', 'map_yandex_keys');

	$strDomain = $_SERVER['HTTP_HOST'];
	$wwwPos = strpos($strDomian, 'www.');
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

$arParams['MAP_ID'] = 
	(strlen($arParams["MAP_ID"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ? 
	'MAP_'.RandString() : $arParams['MAP_ID']; 

$current_search = strip_tags(htmlspecialchars($_GET['ys']));

/// Определение масштаба и позиции карты

if (($strPositionInfo = $arParams['~MAP_DATA']) && CheckSerializedData($strPositionInfo) && ($arResult['POSITION'] = unserialize($strPositionInfo)))
{
	$arParams['INIT_MAP_LON'] = $arResult['POSITION']['yandex_lon'];
	$arParams['INIT_MAP_LAT'] = $arResult['POSITION']['yandex_lat'];
	$arParams['INIT_MAP_SCALE'] = $arResult['POSITION']['yandex_scale'];
}




$this->IncludeComponentTemplate();
?>