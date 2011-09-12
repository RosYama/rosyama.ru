<?
if($USER->IsAdmin())
{
	?>
	<script type="text/javascript">
	var bAjaxInProgress = false;
	
	function ShowDelForm(obj, id)
	{
		var delform = document.getElementById('delform');
		if(delform)
		{
			$(delform).css('top', $(obj).offset().top);
			$(delform).css('left', $(obj).offset().left - 50);
			document.getElementById('del_id_input').value = parseInt(id);
			$(delform).fadeIn();
		}
	}
	function setPM_OK(id)
	{
		if(bAjaxInProgress)
		{
			return false;
		}
		bAjaxInProgress = true;
		jQuery.get
		(
			'/personal/edit.php',
			{
				PREMODERATE_ID: parseInt(id),
				ajax: 1,
			},
			function(data)
			{
				bAjaxInProgress = false;
				if(data == 'ok')
				{
					$('#premoderate_' + id).fadeOut();
				}
			}
		);
	}
	
	</script>
	
	<?if($_REQUEST['filter_premoderated'] && count($arResult['HOLES']) > 0):?>
	
	<?foreach($arResult['HOLES'] as $date => $_date){
		foreach($_date as $elem){
			 $all_elements .= strlen($all_elements) > 0 ? ','.$elem['ID'] : $elem['ID'];
		}
	}?>
	
	<script type="text/javascript">
	var bAjaxInProgress2 = false
	function set_all_right()
	{
		if(bAjaxInProgress2)
		{
			return false
		}
		bAjaxInProgress2 = true
		jQuery.get
		(
			'/personal/edit.php',
			{
				PREMODERATE_ALL: <?='"'.$all_elements.'"'?>,
				ajax: 1,
			},
			function(data)
			{
				bAjaxInProgress2 = false
				if(data == 'ok')
				{
					id = new Array(<?=$all_elements?>)
					for(var key in id)
					{
						$('#premoderate_' + id[key]).fadeOut()
					}
				}
			}
		)
	}
	
	function delete_all()
	{
		jQuery.get
		(
			'/personal/edit.php',
			{
				DELETE_ALL: <?='"'.$all_elements.'"'?>,
				ajax: 1,
			},
			function(data)
			{
				if(data == 'ok'){
					window.location = '<?=$arResult['magic_url']?>'
				}
			}
		)
	}
	
	
	$(document).ready(function()
	{
		$('#all_right').click(
			function(){
				set_all_right()
			}
		)
		
		$('#all_wrong').click(
			function(){
				if(confirm("Удалить все дефекты на текущей странице?")){
					delete_all()
				}
			}
		)
	})
	</script>
	<?endif;?>
	
	<div id="delform">
		<div align="right"><span onclick="$('#delform').fadeOut()">&times;</span></div>
		<form action="/personal/edit.php" method="get">
			<input type="hidden" name="DEL_ID" id="del_id_input">
			<input type="hidden" name="magic_url" value="<?=htmlspecialchars($arResult['magic_url'])?>" />
			<input type="checkbox" name="banuser" value="1" id="banuser_input"> <label for="banuser_input">Забанить автора?</label><br>
			<input type="submit" value="Удалить">
		</form>
	</div>
	<?
}
?>

<?$holeCount = 0?>
<ul class="holes_list">
<?foreach($arResult['HOLES'] as $date => $_date):?>
	<?foreach($_date as $elem): ?>
	<?$holeCount++?>
		<li<?if($holeCount%3==0):?> class="noMargin"<?endif;?>>
			<a href="/<?= $elem['ID'] ?>/" class="photo"><img src="<?= $elem['STATE'] == 'fixed' && $elem['pictures']['small']['fixed'][0] ? $elem['pictures']['small']['fixed'][0] : $elem['pictures']['small']['fresh'][0] ?>" /></a>
			<? if($USER->IsAdmin()): ?>
				<? if($arParams['PREMODERATION'] == 'Y' && !$elem['PREMODERATED']): ?>
					<div class="premoderate" id="premoderate_<?= $elem['ID'] ?>"><img src="/images/st1234/iconpm.gif" onclick="setPM_OK('<?= $elem['ID'] ?>');" title="Показывать этот дефект всем"></div>
				<? endif; ?>
				<div class="del"><a title="Удалить дефект" href="#" onclick="ShowDelForm(this, '<?= $elem['ID'] ?>'); return false;"><img src="/images/st1234/icondel.gif"></a></div>
			<? endif; ?>
			<div class="properties">
				<p class="date"><?= $date?></p>
				<div class="service"><?= $elem['ADDRESS'] ?><i></i></div>
				<div class="social">
					<img src="/images/st1234/<?= $elem['TYPE'] ?>.png" title="<?= GetMessage('HOLE_TYPE_'.$elem['TYPE']) ?>">
					<span class="status_span state_<?= $elem['STATE'] ?>">&bull;</span>
					<span class="status_text"><?= GetMessage('HOLE_STATE_'.$elem['STATE']) ?></span>
					<? if($elem['WAIT_DAYS']): ?>
						<span class="status_days"><i><?= $elem['WAIT_DAYS'] ?></i></span>
					<? endif; ?>
					<? if($elem['PAST_DAYS']): ?>
						<span class="status_days"><i><?= $elem['PAST_DAYS'] ?></i></span>
					<? endif; ?>
				</div>
			</div>
		</li>
	<?endforeach;?>
<?endforeach;?>
</ul>

<? if(!sizeof($arResult['HOLES'])): ?>
	<?= GetMessage('NOHOLES'); ?>
<? endif;?>

<?if($USER->IsAdmin() == true && $_REQUEST['filter_premoderated'] && count($arResult['HOLES']) > 0):?>
	<input type="button" id="all_right" value="Разрешить все дефекты" />
	<input type="button" id="all_wrong" value="Удалить все дефекты" />
<?endif;?>

<? if($arResult['PAGES_COUNT'] > 1): ?>
	<?/*<div class="pagination">
		<? for($i = max(0, $arResult['PAGE'] - 10); $i < $arResult['PAGE']; $i++): ?>
			<a href="?p=<?= $i ?>"><?= $i + 1 ?></a>
		<? endfor; ?>
		<?= $arResult['PAGE'] + 1?>
		<? for($i = $arResult['PAGE'] + 1; $i < min($arResult['PAGES_COUNT'], $arResult['PAGE'] + 11); $i++): ?>
			<a href="?p=<?= $i ?>"><?= $i + 1 ?></a>
		<? endfor; ?>
	</div>*/?>
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