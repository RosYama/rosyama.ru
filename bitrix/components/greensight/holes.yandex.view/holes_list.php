<?

// возвращает (аяксом) джаваскрипт с данными о ямах, попадающих в указанную область

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
ob_end_clean();
ob_end_clean();
ob_end_clean();
ob_end_clean();
ob_end_clean();

/// Подключение модуля обработки ям
if(!CModule::IncludeModule('st1234holes'))
{
	die();
}

/// Фильтрация по масштабу позиции карты
$arFilter = array(
	'>LATITUDE'  => (float)$_GET['bottom'],
	'>LONGITUDE' => (float)$_GET['left'],
	'<LONGITUDE' => (float)$_GET['right'],
	'<LATITUDE'  => (float)$_GET['top']
);

/// Фильтрация по состоянию ямы
if(sizeof($_GET['state']) && is_array($_GET['state']))
{
	$arFilter['STATE'] = $_GET['state'];
}

/// Фильтрация по типу ямы
if(sizeof($_GET['type']) && is_array($_GET['type']))
{
	$arFilter['TYPE'] = $_GET['type'];
}

// определение, нужна ли премодерация
$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/index.php');
preg_match('/(\'|\")PREMODERATION\1 => (\"|\')(Y|N|)\2/', $raw, $_match);
if($_match[3] == 'Y')
{
	$arFilter['PREMODERATED'] = 1;
}

/// Если не заданы параметры, то производится выборка всех записей ям
if(!$_GET['bottom'] && !$_GET['top'] && !$_GET['bottom'] && !$_GET['right'])
{
	$res = C1234Hole::GetList();
}
else
{
	$res = C1234Hole::GetList
	(
		array(),
		$arFilter,
		array('nopicts' => true)
	);
}

/// Создание объектов карты 
foreach($res as &$hole)
{
	if($_REQUEST['skip_id'] != $hole['ID'])
	{
		?>
		if(!PlaceMarks[<?= $hole['ID'] ?>])
		{
			var s = new YMaps.Style();
			s.iconStyle = new YMaps.IconStyle();
			s.iconStyle.href = "/images/st1234/<?= $hole['TYPE'] ?>_<?= $hole['STATE'] ?>.png";
			s.iconStyle.size = new YMaps.Point(54, 61);
			s.iconStyle.offset = new YMaps.Point(-30, -61);
			PlaceMarks[<?= $hole['ID'] ?>] = new YMaps.Placemark(new YMaps.GeoPoint(<?= $hole['LONGITUDE'] ?>, <?= $hole['LATITUDE'] ?>), { hasHint: false, hideIcon: false, hasBalloon: false, style: s });
			map.addOverlay(PlaceMarks[<?= $hole['ID'] ?>]);
			<? if(!$_REQUEST['noevents']): ?>
			YMaps.Events.observe
			(
				PlaceMarks[<?= $hole['ID'] ?>],
				PlaceMarks[<?= $hole['ID'] ?>].Events.Click,
				function (obj)
				{
					window.location="/<?= $hole['ID'] ?>/";
				}
			)
			<? endif; ?>
		}
		<?
	}
}

die();

?>