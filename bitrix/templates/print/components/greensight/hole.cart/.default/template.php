<?
global $USER;
$hole = $arResult['HOLE'];
?>
<script src="http://api-maps.yandex.ru/1.1/index.xml?key=<?= $arResult['YANDEX_MAP_KEY'] ?>&onerror=apifault" type="text/javascript"></script>


	<div class="holes_list">
		<table class="holes_table">
			<tr>
				<td valign="top">
		<? if($hole['LATITUDE'] && $hole['LONGITUDE']):?>
			<div id="ymapcontainer<?=$hole['ID']?>" class="ymapcontainer"></div>
		<? endif;?>
		<script type="text/javascript">
			var map_centery = <?= $hole['LATITUDE'] ?>;
			var map_centerx = <?= $hole['LONGITUDE'] ?>;
			var map = new YMaps.Map(YMaps.jQuery("#ymapcontainer<?=$hole['ID']?>")[0]);
			YMaps.Events.observe(map, map.Events.DblClick, function () { toggleMap(); } );
			map.enableScrollZoom();
			map.setCenter(new YMaps.GeoPoint(map_centerx, map_centery), 16);
			var s = new YMaps.Style();
			s.iconStyle = new YMaps.IconStyle();
			s.iconStyle.href = "/images/st1234/<?= $hole['TYPE']?>_<?= $hole['STATE'] ?>.png";
			s.iconStyle.size = new YMaps.Point(54, 61);
			s.iconStyle.offset = new YMaps.Point(-30, -61);
			var placemark = new YMaps.Placemark(new YMaps.GeoPoint(map_centerx, map_centery), { hideIcon: false, hasBalloon: false, style: s } );
			YMaps.Events.observe(placemark, placemark.Events.Click, function () { toggleMap(); } );
			map.addOverlay(placemark);
		</script>
	</td>
	<td valign="top">
		<a href="/<?= $hole['ID'] ?>/" class="photo"><img src="<?= $hole['STATE'] == 'fixed' && $hole['pictures']['medium']['fixed'][0] ? $hole['pictures']['medium']['fixed'][0] : $hole['pictures']['medium']['fresh'][0] ?>" /></a>
	</td>
	</tr>
	</table>
<!--	<tr>
	<td colspan="2" valign="top">
-->		<div class="properties">
			<div class="service">
				<b>Адрес: </b><?= $hole['ADDRESS'] ?><br>
				<b>Координаты: </b><?= $hole['~COORDINATES_R'] ?><br>
				<b>Комментарий: </b><?= $hole['COMMENT1'] ?><br>
			</div>
		</div>
<!--	</td>
	</tr>
-->
</div>
