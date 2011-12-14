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

$min_latitude  = isset($arFilter['>LATITUDE'])  ? $arFilter['>LATITUDE']  : false;
$max_latitude  = isset($arFilter['<LATITUDE'])  ? $arFilter['<LATITUDE']  : false;
$min_longitude = isset($arFilter['>LONGITUDE']) ? $arFilter['>LONGITUDE'] : false;
$max_longitude = isset($arFilter['<LONGITUDE']) ? $arFilter['<LONGITUDE'] : false;
if(in_array(false, array($min_latitude, $max_latitude, $min_longitude, $max_longitude), true))
{
	foreach($res as &$hole)
	{
		if($min_latitude === false || $min_latitude < $hole['LATITUDE'])
		{
			$min_latitude = $hole['LATITUDE'];
		}
		if($max_latitude === false || $max_latitude > $hole['LATITUDE'])
		{
			$max_latitude = $hole['LATITUDE'];
		}
		if($min_longitude === false || $min_longitude < $hole['LONGITUDE'])
		{
			$min_longitude = $hole['LONGITUDE'];
		}
		if($max_longitude === false || $max_longitude > $hole['LONGITUDE'])
		{
			$max_longitude = $hole['LONGITUDE'];
		}
	}
}

// группировка объектов карты по квадратикам
$delta_lat     = ($max_latitude  - $min_latitude)  / 6;
$delta_lon     = ($max_longitude - $min_longitude) / 12;
$_groupped_res = array();
foreach($res as &$hole)
{
	$lon = floor(($hole['LONGITUDE'] - $min_longitude) / $delta_lon);
	$lat = floor(($hole['LATITUDE']  - $min_latitude)  / $delta_lat);
	$_groupped_res[$lon][$lat][$hole['STATE']]++;
}

/// Создание объектов карты
// одиночные точки
foreach($res as &$hole)
{
	if($_REQUEST['skip_id'] != $hole['ID'])
	{
		$lon = floor(($hole['LONGITUDE'] - $min_longitude) / $delta_lon);
		$lat = floor(($hole['LATITUDE']  - $min_latitude)  / $delta_lat);
		if(array_sum($_groupped_res[$lon][$lat]) > 5)
		{
			continue;
		}
		?>
		var s = new YMaps.Style();
		s.iconStyle = new YMaps.IconStyle();
		s.iconStyle.href = "/images/st1234/<?= $hole['TYPE'] ?>_<?= $hole['STATE'] ?>.png";
		s.iconStyle.size = new YMaps.Point(28, 30);
		s.iconStyle.offset = new YMaps.Point(-14, -30);
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
		<?
	}
}

// группы точек
?>
var st = new YMaps.Template(
	"<div class=\"groupPlacemark\" style=\"$[metaDataProperty.groupstyle]\">\
		<span style=\"$[metaDataProperty.spanstyle]\">$[name|0]<\/span>\
		<div class=\"achtung\" style=\"$[metaDataProperty.achtungstyle]\"><div style=\"$[metaDataProperty.achtungstyle2]\"><img src=\"\/images\/st1234\/achtung.png\" style=\"$[metaDataProperty.groupstyle]\"><\/div><\/div>\
		<div class=\"prosecutor\" style=\"$[metaDataProperty.prosecutorstyle]\"><div style=\"$[metaDataProperty.prosecutorstyle2]\"><img src=\"\/images\/st1234\/prosecutor.png\" style=\"$[metaDataProperty.groupstyle]\"><\/div><\/div>\
		<div class=\"inprogress\" style=\"$[metaDataProperty.inprogressstyle]\"><div style=\"$[metaDataProperty.inprogressstyle2]\"><img src=\"\/images\/st1234\/inprogress.png\" style=\"$[metaDataProperty.groupstyle]\"><\/div><\/div>\
		<div class=\"gibddre\" style=\"$[metaDataProperty.gibddrestyle]\"><div style=\"$[metaDataProperty.gibddrestyle2]\"><img src=\"\/images\/st1234\/gibddre.png\" style=\"$[metaDataProperty.groupstyle]\"><\/div><\/div>\
		<div class=\"fresh\" style=\"$[metaDataProperty.freshstyle]\"><div style=\"$[metaDataProperty.freshstyle2]\"><img src=\"\/images\/st1234\/fresh.png\" style=\"$[metaDataProperty.groupstyle]\"><\/div><\/div>\
		<div class=\"fixed\" style=\"$[metaDataProperty.fixedstyle]\"><div style=\"$[metaDataProperty.fixedstyle2]\"><img src=\"\/images\/st1234\/fixed.png\" style=\"$[metaDataProperty.groupstyle]\"><\/div><\/div>\
	<\/div>"
);
var s = new YMaps.Style();
s.iconStyle = new YMaps.IconStyle(st);
s.iconStyle.href = "/images/st1234/achtung_circle.png";
<?
foreach($_groupped_res as $column_id => $column)
{
	foreach($column as $row_id => &$row)
	{
		$_state_count = array
		(
			'achtung'    => 0,
			'prosecutor' => 0,
			'inprogress' => 0,
			'gibddre'    => 0,
			'fresh'      => 0,
			'fixed'      => 0
		);
		$text    = '';
		$counter = 0;
		foreach($row as $state_name => &$cell)
		{
			$text    .= GetMessage('HOLE_STATE_'.$state_name).': '.$cell.'<br>';
			$counter += $cell;
			$_state_count[$state_name] = $cell;
		}
		if($counter > 5)
		{
			$latitude  = $min_latitude  + $delta_lat * $row_id;
			$longitude = $min_longitude + $delta_lon * $column_id;
			$text      = 'Всего в окрестности метки: '.$counter.'<br>'.$text;
			$cell_id = $column_id.'_'.$row_id;
			
			// размер пимпы
			$iconsize = 0;
			if($counter >= 150)
			{
				$iconsize = 80;
			}
			else
			{
				$iconsize = 40 + $counter / 3;
			}
			// размер шрифта в метке
			$fontsize = 0;
			if($counter >= 150)
			{
				$fontsize = 18;
			}
			else
			{
				$fontsize = ceil(10 + $counter / 14);
			}
			
			// вывод js формирования метки
			?>
			s.iconStyle.offset = new YMaps.Point(-15 -<?= round($iconsize / 2) ?> + Math.round(Math.random() * 30), -35 -<?= $iconsize ?> + Math.round(Math.random() * 30));
			PlaceMarks['<?= $cell_id ?>'] = new YMaps.Placemark
			(
				new YMaps.GeoPoint(<?= $longitude?>, <?= $latitude ?>),
				{
					hasHint: false,
					hideIcon: true,
					hasBalloon: true,
					style: s,
					balloonOptions: {
						hasCloseButton: true,
						mapAutoPan: 0
					}
				}
			);
			PlaceMarks['<?= $cell_id ?>'].name        = '<?= $counter ?>';
			PlaceMarks['<?= $cell_id ?>'].description = '<?= $text ?>';
			<?
			$h  = 0; // общая высота метки
			$dh = 0; // прирост высоты метки
			echo "PlaceMarks['".$cell_id."'].metaDataProperty.groupstyle = 'width: ".$iconsize."px'; ";
			foreach($_state_count as $state => $state_count)
			{
				$dh = $state_count / $counter * $iconsize + 2;
				echo "PlaceMarks['".$cell_id."'].metaDataProperty.".$state."style = 'height: ".(int)$dh."px;'; ";
				echo "PlaceMarks['".$cell_id."'].metaDataProperty.".$state."style2 = 'margin-top: -".(int)$h."px;'; ";
				echo "PlaceMarks['".$cell_id."'].metaDataProperty.spanstyle = 'font-size: ".$fontsize."px'; ";
				$h += $dh;
			}
			echo "map.addOverlay(PlaceMarks['".$cell_id."']); ";
		}
	}
}

die();

?>