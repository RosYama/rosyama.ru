<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if ($arParams['BX_EDITOR_RENDER_MODE'] == 'Y'):
?>
<img src="/bitrix/components/bitrix/map.yandex.view/templates/.default/images/screenshot.png" border="0" />
<?
else:
?>
<script type="text/javascript" src="/bitrix/js/jquery-1.5.2.min.js"></script>
<div class="bx-yandex-search-layout">
	<div class="bx-yandex-search-form">
		<form id="search_form_<?echo $arParams['MAP_ID']?>" name="search_form_<?echo $arParams['MAP_ID']?>" onsubmit="jsYandexSearch_<?echo $arParams['MAP_ID']?>.searchByAddress(this.address.value); return false;">
			<p><?echo GetMessage('MYMS_TPL_SEARCH')?></p>
			<input type="text" id="address_inp" name="address" class="textInput" value="<?= htmlspecialcharsEx($_GET['q']) ?>" style="width: 300px;" />
			<input type="submit" value="<?echo GetMessage('MYMS_TPL_SUBMIT')?>" />
			<a style="display:none;" id="clear_result_link" href="#" onclick="clearSerchResults('<?= $arParams['MAP_ID']?>', JCBXYandexSearch_arSerachresults); document.getElementById('address_inp').value=''; return false;">Очистить</a>
		</form>
	</div>

	<div class="bx-yandex-search-results" id="results_<?echo $arParams['MAP_ID']?>"></div>
		
<?		



	$arTransParams = array(
		'KEY' => $arParams['KEY'],
		'INIT_MAP_TYPE' => $arParams['INIT_MAP_TYPE'],
		'INIT_MAP_LON' => $arResult['POSITION']['yandex_lon'],
		'INIT_MAP_LAT' => $arResult['POSITION']['yandex_lat'],
		'INIT_MAP_SCALE' => $arResult['POSITION']['yandex_scale'],
		'MAP_WIDTH' => $arParams['MAP_WIDTH'],
		'MAP_HEIGHT' => $arParams['MAP_HEIGHT'],
		'CONTROLS' => $arParams['CONTROLS'],
		'OPTIONS' => $arParams['OPTIONS'],
		'MAP_ID' => $arParams['MAP_ID'],
		'ONMAPREADY' => 'BX_SetPlacemarks_'.$arParams['MAP_ID'],
	);

	if ($arParams['DEV_MODE'] == 'Y')
	{
		$arTransParams['DEV_MODE'] = 'Y';
		if ($arParams['WAIT_FOR_EVENT'])
			$arTransParams['WAIT_FOR_EVENT'] = $arParams['WAIT_FOR_EVENT'];
	}
?>

<div class="bx-yandex-view-layout">
	<div class="bx-yandex-view-map">
<?
//echo '<pre>'; print_r($arResult['POSITION']); echo '</pre>';
$arTransParams['ONMAPREADY2'] = 'BXWaitForMap_search'.$arParams['MAP_ID'];
	$APPLICATION->IncludeComponent('greensight:holes.yandex.system', '.default', $arTransParams, false, array('HIDE_ICONS' => 'Y'));
?>

	</div>
</div>
<img src="<?=SITE_TEMPLATE_PATH?>/images/map_shadow.jpg" class="mapShadow" alt="" />
<script type="text/javascript">
<?
foreach($arResult['FILTER_STATES'] as $k => $v)
{
	echo 'filter_state['.$k.'] = "'.$v.'"; ';
}
foreach($arResult['FILTER_TYPES'] as $k => $v)
{
	echo 'filter_type['.$k.'] = "'.$v.'"; ';
}
?>
function BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>(map)
{
	var arObjects = {PLACEMARKS:[],POLYLINES:[]};
<?
	if (is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS']))):
		for($i = 0; $i < $cnt; $i++):
?>
	arObjects.PLACEMARKS[arObjects.PLACEMARKS.length] = BX_YMapAddPlacemark(map, <?echo CUtil::PhpToJsObject($arResult['POSITION']['PLACEMARKS'][$i])?>);
<?
		endfor;
	endif;
	if (is_array($arResult['POSITION']['POLYLINES']) && ($cnt = count($arResult['POSITION']['POLYLINES']))):
		for($i = 0; $i < $cnt; $i++):
?>
	arObjects.POLYLINES[arObjects.POLYLINES.length] = BX_YMapAddPolyline(map, <?echo CUtil::PhpToJsObject($arResult['POSITION']['POLYLINES'][$i])?>);
<?
		endfor;
	endif;
	
	if ($arParams['ONMAPREADY']):
?>
	if (window.<?echo $arParams['ONMAPREADY']?>)
	{
		window.<?echo $arParams['ONMAPREADY']?>(map, arObjects);
	}
<?
	endif;
?>

<? if($_GET['q']): ?>
	jsYandexSearch_<?= $arParams['MAP_ID'] ?>.searchByAddress("<?= htmlspecialcharsEx($_GET['q']) ?>");
<? endif; ?>

	YMaps.Events.observe(map, map.Events.MoveEnd, function() {
		var res = "{ 'center': '" + map.getCenter() + "', 'zoom': '" + map.getZoom() + "' }"
		document.cookie = "map_settings="+res
		res = "center:" + map.getCenter() + ";zoom:" + map.getZoom();
		var loc = new String(document.location);
		loc = loc.split('#');
		document.location = loc[0] + '#' + res;
		GetPlacemarks(map);
	} );
	YMaps.Events.observe(map, map.Events.Move, function() { GetPlacemarks(map);	} );
	YMaps.Events.observe(map, map.Events.Update, function() {
		var res = "{ 'center': '" + map.getCenter() + "', 'zoom': '" + map.getZoom() + "' }"
		document.cookie = "map_settings="+res
		res = "center:" + map.getCenter() + ";zoom:" + map.getZoom();
		var loc = new String(document.location);
		loc = loc.split('#');
		document.location = loc[0] + '#' + res;
		GetPlacemarks(map);
	} );
	GetPlacemarks(map);
}
</script>
<script type="text/javascript">
function BXWaitForMap_search<?echo $arParams['MAP_ID']?>() 
{
	window.jsYandexSearch_<?echo $arParams['MAP_ID']?> = new JCBXYandexSearch('<?echo $arParams['MAP_ID']?>', document.getElementById('results_<?echo $arParams['MAP_ID']?>'), {
		mess_error: '<?echo GetMessage('MYMS_TPL_JS_ERROR')?>',
		mess_search: '<?echo GetMessage('MYMS_TPL_JS_SEARCH')?>',
		mess_found: '<?echo GetMessage('MYMS_TPL_JS_RESULTS')?>',
		mess_search_empty: '<?echo GetMessage('MYMS_TPL_JS_RESULTS_EMPTY')?>'
	});
}
</script>
<?
endif;
?>