<?php

if($arResult['ERROR_STR'])
{
	echo '<div class="error">'.$arResult['ERROR_STR'].'</div>';
}

$F = &$arResult['FORM']['FIELDS'];

?>
<script src="http://api-maps.yandex.ru/1.1/index.xml?key=<?= $arResult['YANDEX_MAP_KEY'] ?>" type="text/javascript"></script>
<script src="/bitrix/js/jquery-1.5.2.min.js" type="text/javascript"></script>
<script type="text/javascript">
var mess = {
	'ADD_HOLE_FORM_ERROR_NO_ADDRESS': '<?= GetMessage('ADD_HOLE_FORM_ERROR_NO_ADDRESS') ?>',
	'ADD_HOLE_FORM_ERROR_NO_COORD':   '<?= GetMessage('ADD_HOLE_FORM_ERROR_NO_COORD') ?>'
};
var hole_id = '<?= $F['ID']['VALUE'] ?>';
</script>
<form action="<?= $arResult['FORM']['ACTION'] ?>" method="<?= $arResult['FORM']['METHOD'] ?>" enctype="<?= $arResult['FORM']['ENCTYPE'] ?>" name="<?= $arResult['FORM']['ID'] ?>" id="<?= $arResult['FORM']['ID'] ?>">
	<input type="hidden" name="ID" value="<?= $F['ID']['VALUE'] ?>">
	<? if($F['FIX_ID']): ?>
		<input type="hidden" name="FIX_ID" value="<?= $F['FIX_ID']['VALUE'] ?>">
	<? elseif($F['GIBDD_REPLY_ID']): ?>
		<input type="hidden" name="GIBDD_REPLY_ID" value="<?= $F['GIBDD_REPLY_ID']['VALUE'] ?>">
	<? endif; ?>

	<!-- левая колоночка -->
	<div class="lCol">
		<!-- тип дефекта -->
		<div class="f">
			<? if($F['TYPE']): ?>
				<label for="type"><?= $F['TYPE']['LABEL'] ?><span class="required">*</span></label>
				<select name="TYPE" id="type">
					<? foreach($F['TYPE']['ITEMS'] as $k => $v): ?>
						<option value="<?= $k ?>"<?= $F['TYPE']['VALUE'] == $k ? ' selected' : '' ?>><?= $v ?></option>
					<? endforeach; ?>
				</select>
			<? elseif($arResult['HOLE']): ?>
				<?= GetMessage('FORM_HOLE_TYPE_'.$arResult['HOLE']['TYPE']) ?>
			<? endif; ?>
		</div>
		
		<!-- адрес -->
		<div class="f">
			<? if($F['ADDRESS']): ?>
				<label for="address"><?= $F['ADDRESS']['LABEL'] ?><span class="required">*</span></label>
				<input type="text" class="textInput" value="<?= $F['ADDRESS']['VALUE'] ?>" name="ADDRESS" id="address">
			<? elseif($arResult['HOLE']): ?>
				<?= htmlspecialcharsEx($arResult['HOLE']['ADDRESS']) ?>
				<input id="address" value="1" type="hidden">
			<? endif; ?>
		</div>
		
		<!-- фотки -->
		<div class="f">
			<label><?= $F['PHOTOS']['LABEL'] ?><?= $F['PHOTOS']['REQUIRED'] ? '<span class="required">*</span>' : '' ?></label>
			<span class="comment"><?= GetMessage('upload_max_filesize').$arResult['upload_max_filesize'] ?>.
			<?= GetMessage('post_max_size').$arResult['post_max_size'] ?>.</span>
			<? for($i = 0; $i < 10; $i++): ?>
				<div id="photos_<?= $i ?>" class="inpphoto <?= $i ? 'hidden' : '' ?>">
					<input type="file" class="textInput" size="18" name="PHOTOS_<?= $i ?>">
					<? if($i < 9): ?>
						<span onclick="document.getElementById('photos_<?= ($i + 1) ?>').style.display = 'block'; this.style.display='none';">Ещё</span>
					<? endif; ?>
				</div>
			<? endfor; ?>
		</div>
		
		<!-- камент -->
		<div class="f">
			<? if($F['COMMENT1']): ?>
				<label for="comment"><?= $F['COMMENT1']['LABEL'] ?></label>
				<textarea name="COMMENT" id="comment"><?= $F['COMMENT1']['VALUE'] ?></textarea>
			<? elseif($arResult['HOLE']): ?>
				<?= htmlspecialcharsEx($arResult['HOLE']['COMMENT1']) ?>
			<? endif; ?>
		</div>
	</div>
	<!-- /левая колоночка -->
	
	<!-- правая колоночка -->
	<div class="rCol">
		<div class="f">
		<?
		$map_center = $arResult['map_center'];
		if($F['COORDINATES'])
		{
			?><p class="tip"><?= GetMessage('HOLE_FORM_COORDINATES') ?><span class="required">*</span></p>
			<div class="bx-yandex-search-layout">
				<div class="bx-yandex-search-form">
					<p>Введите название места для быстрого поиска</p>
					<input type="text" class="textInput" name="mapaddress" id="mapaddress" value="" onkeypress="if(event.keyCode==13) searchByAddress(this.value);""/>
					<input type="button" onclick="searchByAddress(document.getElementById('mapaddress').value);" value="Поиск" />
					<a style="display:none;" id="clear_result_link" href="#" onclick="clearSerchResults(); document.getElementById('mapaddress').value=''; return false;">Очистить</a>
				</div>
				<div class="bx-yandex-search-results" id="searchresults"></div>
			</div>
			<input type="hidden" name="adr_subjectrf" id="adr_subjectrf" value="">
			<input type="hidden" name="adr_city" id="adr_city" value="">
			<span id="recognized_address_str" title="Субъект РФ и населённый пункт"></span>
			<span id="other_address_str"></span>
			<div id="ymapcontainer" class="ymapcontainer"></div>
			<img src="<?=SITE_TEMPLATE_PATH?>/images/map_shadow.jpg" class="mapShadow" alt="" />
			<script type="text/javascript">
				var map = new YMaps.Map(YMaps.jQuery("#ymapcontainer")[0]);
				map.enableScrollZoom();
				map.disableDblClickZoom();
				map.addControl(new YMaps.TypeControl());
				map.addControl(new YMaps.ToolBar());
				map.addControl(new YMaps.Zoom());
				map.addControl(new YMaps.MiniMap());
				map.addControl(new YMaps.ScaleLine());
				map.setCenter(new YMaps.GeoPoint(<?= $map_center ?>), 10);
				var placemark;
				YMaps.Events.observe(map, map.Events.DblClick, setCoordValue);
				<? if($arResult['HOLE']['ID']): ?>
					coordpoint = new YMaps.Placemark(new YMaps.GeoPoint(<?= $arResult['HOLE']['~COORDINATES_R'] ?>), { hideIcon: false, hasBalloon: true, draggable: false, style: 'default#violetPoint' });
					map.addOverlay(coordpoint);
					geocodeOnSetCoordValue();
					YMaps.Events.observe(coordpoint, coordpoint.Events.DragEnd, function (obj) {
						document.getElementById('COORDINATES').value = obj.getCoordPoint();
						geocodeOnSetCoordValue();
					});
				<? endif; ?>
				<? if($_POST['COORDINATES']): ?>
					coordpoint = new YMaps.Placemark(new YMaps.GeoPoint(<?= htmlspecialcharsEx($_POST['COORDINATES']) ?>), { hideIcon: false, hasBalloon: false, draggable: true, style: 'default#violetPoint' });
					map.addOverlay(coordpoint);
					geocodeOnSetCoordValue();
					YMaps.Events.observe(coordpoint, coordpoint.Events.DragEnd, function (obj) {
						document.getElementById('COORDINATES').value = obj.getCoordPoint();
						geocodeOnSetCoordValue();
					});
				<? endif; ?>
				YMaps.Events.observe(map, map.Events.MoveEnd, function() { GetPlacemarks(map); } );
				YMaps.Events.observe(map, map.Events.Move, function() { GetPlacemarks(map); } );
				YMaps.Events.observe(map, map.Events.Update, function() { GetPlacemarks(map); } );
				GetPlacemarks(map);
			</script>
			<input type="hidden" name="COORDINATES" id="COORDINATES" value="<?= $arResult['HOLE']['ID'] ? 1 : ($_POST['COORDINATES'] ? htmlspecialcharsEx($_POST['COORDINATES']) : '') ?>">
			<?
		}
		elseif($arResult['HOLE'])
		{
			?>
			<div id="ymapcontainer" class="ymapcontainer"></div>
			<img src="<?=SITE_TEMPLATE_PATH?>/images/map_shadow.jpg" class="mapShadow" alt="" />
			<script type="text/javascript">
				var map = new YMaps.Map(YMaps.jQuery("#ymapcontainer")[0]);
				map.enableScrollZoom();
				map.setCenter(new YMaps.GeoPoint(<?= $arResult['HOLE']['~COORDINATES_R'] ?>), 14);
				var placemark = new YMaps.Placemark(new YMaps.GeoPoint(<?= $arResult['HOLE']['~COORDINATES_R'] ?>), { hideIcon: false, hasBalloon: false });
				map.addOverlay(placemark);
			</script>
			<input id="COORDINATES" value="1" type="hidden">
			<div class="comment">
				<?= htmlspecialcharsEx($hole['COMMENT1']) ?>
			</div>
			<?
		}
		?>
		
		</div>
		<?
		if($arResult['HOLE']['pictures'] && !$arParams['FIX_ID'] && !$arParams['GIBDD_REPLY_ID'])
		{
			?>
			<div id="overshadow"><span class="command" onclick="document.getElementById('picts').style.display=document.getElementById('picts').style.display=='block'?'none':'block';">Можно удалить загруженные фотографии</span><div class="picts" id="picts"><?
			foreach($arResult['HOLE']['pictures']['medium']['fresh'] as &$picture)
			{
				$picture_id = explode('/', $picture);
				$picture_id = explode('.', $picture_id[sizeof($picture_id) - 1]);
				$picture_id = $picture_id[0];
				echo '<br><input type="checkbox" value="1" id="deletepict_'.$picture_id.'" name="deletepict_'.$picture_id.'"> <label for="deletepict_'.$picture_id.'">Удалить фотографию?</label><br><img src="'.$picture.'"><br><br>';
			}
			echo '</div></div>';
		}
		?>
		
		<? if($F['COMMENT2']): ?>
			<!-- камент -->
			<div class="f">
				<label for="comment"><?= $F['COMMENT2']['LABEL'] ?></label>
				<textarea name="COMMENT" id="comment"><?= $F['COMMENT2']['VALUE'] ?></textarea>
			</div>
		<? endif; ?>
	</div>
	<!-- /правая колоночка -->
	<div class="addSubmit">
		<div class="container">
			<p><?= $arResult['FORM']['LEGEND'] ?></p>
			<div class="btn">
				<a class="addFact" onclick="if(checkHoleForm()) document.getElementById('<?= $arResult['FORM']['ID'] ?>').submit();"><i class="text">Отправить</i><i class="arrow"></i></a>
			</div>
		</div>
	</div>
</form>