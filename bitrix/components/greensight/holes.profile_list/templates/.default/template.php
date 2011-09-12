<? if(sizeof($arResult['HOLES']['FRESH'])): ?>
	<h2><?= GetMessage('FRESH_HOLES') ?></h2>
	<ul class="holes_list">
	<?
	foreach($arResult['HOLES']['FRESH'] as $elem):
	?>
		<li>
			<a href="/<?= $elem['ID'] ?>/" class="photo"><img src="<?= $elem['STATE'] == 'fixed' && $elem['pictures']['small']['fixed'][0] ? $elem['pictures']['small']['fixed'][0] : $elem['pictures']['small']['fresh'][0] ?>" /></a>
			<div class="properties">
				<div class="service">
					<?= htmlspecialcharsEx($elem['ADDRESS']) ?><i></i>
				</div>
				<div class="social">
					<img src="/images/st1234/<?= $elem['TYPE'] ?>.png" title="<?= GetMessage('HOLE_TYPE_'.$elem['TYPE']) ?>">
					<?= GetMessage('HOLE_STATE_'.$elem['STATE']) ?>
					<? if($elem['WAIT_DAYS']): ?>
						<?= $elem['WAIT_DAYS'] ?>
					<? endif; ?>
					<? if($elem['PAST_DAYS']): ?>
						<?= $elem['PAST_DAYS'] ?>
					<? endif; ?>
				</div>
			</div>
		</li>
	<?
	endforeach;
	?>
</ul>
<? endif; ?>

<? if(sizeof($arResult['HOLES']['INPROGRESS'])): ?>
	<h2><?= GetMessage('INPROGRESS_HOLES') ?></h2>
	<ul class="holes_list">
	<?
	foreach($arResult['HOLES']['INPROGRESS'] as $elem):
	?>
		<li>
			<a href="/<?= $elem['ID'] ?>/" class="photo"><img src="<?= $elem['STATE'] == 'fixed' && $elem['pictures']['small']['fixed'][0] ? $elem['pictures']['small']['fixed'][0] : $elem['pictures']['small']['fresh'][0] ?>" /></a>
			<div class="properties">
				<div class="service">
					<?= htmlspecialcharsEx($elem['ADDRESS']) ?><i></i>
				</div>
				<div class="social">
					<img src="/images/st1234/<?= $elem['TYPE'] ?>.png" title="<?= GetMessage('HOLE_TYPE_'.$elem['TYPE']) ?>">
					<?= GetMessage('HOLE_STATE_'.$elem['STATE']) ?>
					<? if($elem['WAIT_DAYS']): ?>
						<?= $elem['WAIT_DAYS'] ?>
					<? endif; ?>
					<? if($elem['PAST_DAYS']): ?>
						<?= $elem['PAST_DAYS'] ?>
					<? endif; ?>
				</div>
			</div>
		</li>
	<?
	endforeach;
	?>
	</ul>
<? endif; ?>

<? if(sizeof($arResult['HOLES']['FIXED'])): ?>
	<h2><?= GetMessage('FIXED_HOLES') ?></h2>
	<ul class="holes_list">
	<?
	foreach($arResult['HOLES']['FIXED'] as $elem):
	?>
		<li>
			<a href="/<?= $elem['ID'] ?>/" class="photo"><img src="<?= $elem['STATE'] == 'fixed' && $elem['pictures']['small']['fixed'][0] ? $elem['pictures']['small']['fixed'][0] : $elem['pictures']['small']['fresh'][0] ?>" /></a>
			<div class="properties">
				<div class="service">
					<?= htmlspecialcharsEx($elem['ADDRESS']) ?><i></i>
				</div>
				<div class="social">
					<img src="/images/st1234/<?= $elem['TYPE'] ?>.png" title="<?= GetMessage('HOLE_TYPE_'.$elem['TYPE']) ?>">
					<?= GetMessage('HOLE_STATE_'.$elem['STATE']) ?>
					<? if($elem['WAIT_DAYS']): ?>
						<?= $elem['WAIT_DAYS'] ?>
					<? endif; ?>
					<? if($elem['PAST_DAYS']): ?>
						<?= $elem['PAST_DAYS'] ?>
					<? endif; ?>
				</div>
			</div>
		</li>
	<?
	endforeach;
	?>
	</ul>
<? endif; ?>