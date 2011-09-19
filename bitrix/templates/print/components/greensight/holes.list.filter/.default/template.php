<?
if(isset($_GET['ID'])) return;
?>
<script>
		var init = <?=( isset($_GET['f_vision']) ? 1 : 0) ?>;
		window.onload = function()
		{
				document.getElementById('initial_link').onclick = function()
				{
						document.getElementById('filter').style.display = init == 0 ? 'block' : 'none'
						document.getElementById('initial_link').innerHTML = (init == 0 ? 'Скрыть' : 'Показать') + ' фильтр'
						init = init == 0 ? 1 : 0;
						return false
				}
		}
</script>

<a href="/?print=Y<?=(!isset($_GET['f_vision']) ? '&f_vision': '')?>" id="initial_link">
		<? echo !isset($_GET['f_vision']) ? 'Показать': 'Скрыть';?>
		   фильтр
</a>
<div class="lCol">
		
<div class="filter" id="filter" <? if(!isset($_GET['f_vision'])):?> style="display: none;"<? endif;?>>
		<script type="text/javascript" src="/bitrix/js/jquery-1.5.2.min.js"></script>
		<form action="<?= $arResult['FORM_ACTION'] ?>" method="post" id="filter_form" onsubmit="onFilterFormSubmit()">
			<p>
				<input type="hidden" name="filter_rf_subject_id" id="filter_rf_subject_id" value="<?= $arResult['FILTER']['rf_subject_id'] ?>">
				<input onclick="onRFSubjectClick(this)" onblur="onRFSubjectBlur(this)" class="<?= $arResult['FILTER']['rf_subject_class'] ?>" type="text" name="filter_rf_subject" id="filter_rf_subject" value="<?= $arResult['FILTER']['rf_subject'] ?>" onkeyup="onFilterRFSKeyUp(this)">
			</p>
			<div id="filter_rf_subject_tip" class="filter_roller"></div>
			<p>
				<input onclick="onFilterCityClick(this)" onblur="onFilterCityBlur(this)" class="<?= $arResult['FILTER']['city_class'] ?>" type="text" name="filter_city" id="filter_city" value="<?= $arResult['FILTER']['city'] ?>" onkeyup="onFilterCityKeyUp(this)">
			</p>
			<div id="filter_city_tip" class="filter_roller"></div>
			<p>
			<select name="filter_type">
				<option value=""<?= $arResult['FILTER']['type'] ? '' : ' selected' ?>><?= GetMessage('HOLES_FILTER_TYPE') ?></option>
				<? foreach($arResult['TYPE'] as $k => $v): ?>
					<option value="<?= $k ?>"<?= $arResult['FILTER']['type'] == $k ? ' selected' : '' ?>><?= $v ?></option>
				<? endforeach; ?>
			</select>
			</p>
			<p>
			<select name="filter_status">
				<option value=""<?= $arResult['FILTER']['status'] ? '' : ' selected' ?>><?= GetMessage('HOLES_FILTER_STATUS') ?></option>
				<? foreach($arResult['STATUS'] as $k => $v): ?>
					<option value="<?= $k ?>"<?= $arResult['FILTER']['status'] == $k ? ' selected' : '' ?>><?= $v ?></option>
				<? endforeach; ?>
			</select>
			</p>
			<?if($USER->IsAdmin()){?>
				<p>
					<input name="filter_premoderated" class="filter_checkbox" id="f_checkbox" type="checkbox" <?=$arResult['FILTER']['premoderated'];?>/><label for="f_checkbox"><?=GetMessage('HOLES_FILTER_PREMODERATED')?></label>
				</p>
			<?}?>
			<span class="filterBtn" onclick="onFilterFormSubmit(); document.getElementById('filter_form').submit();">
				<i class="text"><?= GetMessage('HOLES_FILTER_SUBMIT') ?></i>
				<i class="arrow"></i>
			</span>
			<br>
			<? if($arResult['SHOW_RESET_LINK']): ?><span class="reset" onclick="document.location='/';"><?= GetMessage('HOLES_FILTER_RESET') ?></span><? endif; ?>
		</form>
</div>
	
</div>