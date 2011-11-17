<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<script type="text/javascript">
if (!window.GLOBAL_arMapObjects)
	window.GLOBAL_arMapObjects = {};

function onMapUpdate(m)
{
	var obj = document.getElementById('MAPLAT');
	if(obj)
	{
		obj.value = m.getCenter();
	}
	obj = document.getElementById('MAPZOOM');
	if(obj)
	{
		obj.value = m.getZoom();
	}
}

function init_<?= $arParams['MAP_ID']?>(context) 
{
	if (null == context)
		context = window;

	if (!context.YMaps)
		return;
	
	window.GLOBAL_arMapObjects['<?= $arParams['MAP_ID']?>'] = new context.YMaps.Map(context.document.getElementById("BX_YMAP_<?= $arParams['MAP_ID']?>"));
	var map = window.GLOBAL_arMapObjects['<?= $arParams['MAP_ID']?>'];
	
	map.bx_context = context;
	map.setCenter(new context.YMaps.GeoPoint(<?= $arParams['INIT_MAP_LON']?>, <?= $arParams['INIT_MAP_LAT']?>), <?= $arParams['INIT_MAP_SCALE']?>, context.YMaps.MapType.<?= $arParams['INIT_MAP_TYPE']?>);
	
	<?if($_REQUEST["MAPLAT"]):?> map.setCenter(new context.YMaps.GeoPoint(<?=$_REQUEST["MAPLAT"]?>), <?= $arParams['INIT_MAP_SCALE']?>, context.YMaps.MapType.<?= $arParams['INIT_MAP_TYPE']?>); <?endif?>
	<?if($_REQUEST['MAPZOOM']):?>map.setZoom('<?= (int)$_REQUEST['MAPZOOM']?>');<?endif;?>
	context.YMaps.Events.observe(map, map.Events.Update, function() { onMapUpdate(map); } );
	context.YMaps.Events.observe(map, map.Events.MoveEnd, function() { onMapUpdate(map); } );
	
	<?
	foreach ($arResult['ALL_MAP_OPTIONS'] as $option => $method)
	{
		if (in_array($option, $arParams['OPTIONS'])):
		?>
		map.enable<?= $method?>();
		<?
		else:
		?>
		map.disable<?= $method?>();
		<?
		endif;
	}
	foreach ($arResult['ALL_MAP_CONTROLS'] as $control => $method)
	{
		if (in_array($control, $arParams['CONTROLS'])):
		?>
		map.addControl(new context.YMaps.<?= $method?>());
		<?	
		endif;
	}
	if ($arParams['DEV_MODE'] == 'Y'):
	?>
		context.bYandexMapScriptsLoaded = true;
	<?
	endif;
	
	if ($arParams['ONMAPREADY2']):
	?>
		if (window.<?= $arParams['ONMAPREADY2']?>)
		{
			<?
			if ($arParams['ONMAPREADY_PROPERTY']):
			?>
				<?= $arParams['ONMAPREADY_PROPERTY']?> = map;
				window.<?= $arParams['ONMAPREADY2']?>();
			<?
			else:
			?>
				window.<?= $arParams['ONMAPREADY2']?>(map);
			<?
			endif;
			?>
		}
	<?
	endif;
	?>
	<?
	if ($arParams['ONMAPREADY']):
	?>
		if (window.<?= $arParams['ONMAPREADY']?>)
		{
			<?
			if ($arParams['ONMAPREADY_PROPERTY']):
			?>
				<?= $arParams['ONMAPREADY_PROPERTY']?> = map;
				window.<?= $arParams['ONMAPREADY']?>();
			<?
			else:
			?>
				window.<?= $arParams['ONMAPREADY']?>(map);
			<?
			endif;
			?>
		}
	<?
	endif;
	?>
	var loc = new String(document.location);
	loc = loc.split('#');
	if(loc[1])
	{
		loc = loc[1].split(';');
		loc[0] = loc[0].split(':');
		loc[1] = loc[1].split(':');
		loc[0][1] = loc[0][1].split(',');
		map.setCenter(new context.YMaps.GeoPoint(loc[0][1][0], loc[0][1][1]), loc[1][1]);
	}
}

if (window.attachEvent) // IE
	window.attachEvent("onload", function(){init_<?= $arParams['MAP_ID']?>()});
else if (window.addEventListener) // Gecko / W3C
	window.addEventListener('load', function(){init_<?= $arParams['MAP_ID']?>()}, false);
else
	window.onload = function(){init_<?= $arParams['MAP_ID']?>()};

</script>
<div id="BX_YMAP_<?= $arParams['MAP_ID']?>" class="bx-yandex-map" style="height: <?= $arParams['MAP_HEIGHT'];?>; width: <?= $arParams['MAP_WIDTH']?>;"><?= GetMessage('MYS_LOADING'.($arParams['WAIT_FOR_EVENT'] ? '_WAIT' : ''));?></div>