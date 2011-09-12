<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if ($arParams['BX_EDITOR_RENDER_MODE'] == 'Y'):
?>
<img src="/bitrix/components/bitrix/map.yandex.search/templates/.default/images/screenshot.png" border="0" />
<?
else:
?>
<div class="bx-yandex-search-layout">
	<div class="bx-yandex-search-form">
		<form name="search_form_<?echo $arParams['MAP_ID']?>" onsubmit="jsYandexSearch_<?echo $arParams['MAP_ID']?>.searchByAddress(this.address.value); return false;">
			<?echo GetMessage('MYMS_TPL_SEARCH')?>: <input type="text" name="address" value="" style="width: 300px;" /><input type="submit" value="<?echo GetMessage('MYMS_TPL_SUBMIT')?>" />
		</form>
	</div>

	<div class="bx-yandex-search-results" id="results_<?echo $arParams['MAP_ID']?>"></div>

	<div class="bx-yandex-search-map">
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
		
	);

	/*if ($arParams['DEV_MODE'] == 'Y')
	{
		$arTransParams['DEV_MODE'] = 'Y';
		if ($arParams['WAIT_FOR_EVENT'])
			$arTransParams['WAIT_FOR_EVENT'] = $arParams['WAIT_FOR_EVENT'];
	} */

	$arParams = array_merge($arTransParams,$arParams);

		$arParams['ONMAPREADY'] = 'BXWaitForMap_search'.$arParams['MAP_ID'];
		$arParams['ONMAPREADY2'] = 'BX_SetPlacemarks_'.$arParams['MAP_ID'];
		$APPLICATION->IncludeComponent('greensight:holes.yandex.system', '.default', $arParams, null, array('HIDE_ICONS' => 'Y'));
?>
	</div>
	
</div>
<script type="text/javascript">
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
	
	
?>

<?
	if ($arParams['ONMAPREADY2']):
?>
	if (window.<?='BX_SetPlacemarks_'.$arParams['MAP_ID']?>)
	{
		window.<?='BX_SetPlacemarks_'.$arParams['MAP_ID']?>(map, arObjects);
	}
<?
	endif;
?>
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