<script src="http://api-maps.yandex.ru/1.1/index.xml?key=<?= $arResult['YANDEX_MAP_KEY'] ?>&onerror=apifault" type="text/javascript"></script>

<? foreach($arResult['HOLES'] as $date => $_date):?>
	<? foreach($_date as $elem): ?>
	<div class="holes_list">
		<table class="holes_table">
			<tr>
				<td valign="top">
		<? if($elem['LATITUDE'] && $elem['LONGITUDE']):?>
			<div id="ymapcontainer<?=$elem['ID']?>" class="ymapcontainer"></div>
		<? endif;?>
		<script type="text/javascript">
			var map_centery = <?= $elem['LATITUDE'] ?>;
			var map_centerx = <?= $elem['LONGITUDE'] ?>;
			var map = new YMaps.Map(YMaps.jQuery("#ymapcontainer<?=$elem['ID']?>")[0]);
			YMaps.Events.observe(map, map.Events.DblClick, function () { toggleMap(); } );
			map.enableScrollZoom();
			map.setCenter(new YMaps.GeoPoint(map_centerx, map_centery), 16);
			var s = new YMaps.Style();
			s.iconStyle = new YMaps.IconStyle();
			s.iconStyle.href = "/images/st1234/<?= $elem['TYPE']?>_<?= $elem['STATE'] ?>.png";
			s.iconStyle.size = new YMaps.Point(54, 61);
			s.iconStyle.offset = new YMaps.Point(-30, -61);
			var placemark = new YMaps.Placemark(new YMaps.GeoPoint(map_centerx, map_centery), { hideIcon: false, hasBalloon: false, style: s } );
			YMaps.Events.observe(placemark, placemark.Events.Click, function () { toggleMap(); } );
			map.addOverlay(placemark);
		</script>
	</td>
	<td valign="top">
		<a href="/<?= $elem['ID'] ?>/" class="photo"><img src="<?= $elem['STATE'] == 'fixed' && $elem['pictures']['medium']['fixed'][0] ? $elem['pictures']['medium']['fixed'][0] : $elem['pictures']['medium']['fresh'][0] ?>" /></a>
	</td>
	</tr>
	</table>
<!--	<tr>
	<td colspan="2" valign="top">
-->		<div class="properties">
			<div class="service">
				<b>Адрес: </b><?= $elem['ADDRESS'] ?><br>
				<b>Координаты: </b><?= $elem['~COORDINATES_R'] ?><br>
				<b>Комментарий: </b><?= $elem['COMMENT1'] ?><br>
			</div>
		</div>
<!--	</td>
	</tr>
-->
</div>
	<?endforeach;?>
<?endforeach;?>

<?
	$arResult['PAGINATOR_REQUEST_STR'] = '&print=Y'.
	(!empty($_REQUEST['filter_rf_subject']) ? '&filter_rf_subject='.htmlspecialchars($_REQUEST['filter_rf_subject']) : '').
	(!empty($_REQUEST['filter_city']) ? '&filter_city='.htmlspecialchars($_REQUEST['filter_city']) : '').
	(!empty($_REQUEST['filter_type']) ? '&filter_type='.htmlspecialchars($_REQUEST['filter_type']) : '').
	(!empty($_REQUEST['filter_status']) ? '&filter_status='.htmlspecialchars($_REQUEST['filter_status']) : '');
?>

<? if(!sizeof($arResult['HOLES'])): ?>
	<?= GetMessage('NOHOLES'); ?>
<? endif;?>

<? if($arResult['PAGES_COUNT'] > 1): ?>

<div class="pagination">
	<? if($arResult['PAGES_COUNT'] > 1): ?>
		<? if($arResult['PAGE'] > 0): ?>
			<a class="arrow" href="?p=<?= ($arResult['PAGE'] - 1).$arResult['PAGINATOR_REQUEST_STR'] ?>">&larr;</a>
		<? endif; ?>
		<? if($arResult['PAGE'] > 5): ?>
			<a href="?p=<?= $p.$arResult['PAGINATOR_REQUEST_STR'] ?>">1</a>
			<? if($arResult['PAGE'] > 6): ?><span>...</span><? endif; ?>
		<? endif; ?>
		<? if($arResult['PAGE']): ?>
			<? for($p = max(0, $arResult['PAGE'] - 5); $p < $arResult['PAGE']; $p++): ?>
				<a href="?p=<?= $p.$arResult['PAGINATOR_REQUEST_STR'] ?>"><?= $p + 1 ?></a>
			<? endfor; ?>
		<? endif; ?>
		<span><?= $arResult['PAGE'] + 1 ?></span>
		<? if($arResult['PAGES_COUNT'] > $arResult['PAGE'] - 1): ?>
			<? for($p = $arResult['PAGE'] + 1; $p < min($arResult['PAGES_COUNT'], $arResult['PAGE'] + 6); $p++): ?>
				<a href="?p=<?= $p.$arResult['PAGINATOR_REQUEST_STR'] ?>"><?= $p + 1 ?></a>
			<? endfor; ?>
		<? endif; ?>
		<? if($arResult['PAGE'] < $arResult['PAGES_COUNT'] - 6): ?>
			<? if($arResult['PAGE'] < $arResult['PAGES_COUNT'] - 7): ?><span>...</span><? endif; ?> <a href="?p=<?= ($arResult['PAGES_COUNT'] - 1).$arResult['PAGINATOR_REQUEST_STR'] ?>"><?= $arResult['PAGES_COUNT'] ?></a>
		<? endif; ?>
		<? if($arResult['PAGE'] < $arResult['PAGES_COUNT'] - 1): ?>
			<a class="arrow" href="?p=<?= ($arResult['PAGE'] + 1).$arResult['PAGINATOR_REQUEST_STR'] ?>">&rarr;</a>
		<? endif; ?>
	<? endif; ?>
</div>
<? endif; ?>