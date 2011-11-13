<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<h2><?=GetMessage('PIT_SITY')?>:</h2>
<div class="stats">
	<?foreach($arResult['geography'][0] as $ar){?>
		<a href="/?filter_city=<?=htmlspecialchars(trim($ar['adr_city']))?>"><?=htmlspecialchars(trim($ar['adr_city']))?></a>&nbsp; &mdash;  <?=$ar['counts']?><br>
	<?}?>
</div>

<h2><?=GetMessage('FIXED_PIT_SITY')?>:</h2>
<div class="stats">
<?foreach($arResult['geography'][1] as $ar){?>
	<a href="/?filter_city=<?=htmlspecialchars(trim($ar['adr_city']))?>&filter_status=fixed"><?=htmlspecialchars(trim($ar['adr_city']))?></a>&nbsp; &mdash;  <?=$ar['counts']?><br>
<?}?>
</div>

<h2><?=GetMessage('PIT_STATE')?>:</h2>
<div class="stats">
<?foreach($arResult['state'][0] as $ar){?>
	<a href="/?filter_status=<?=$ar['state_to_filter']?>"><?=$ar['state']?></a>&nbsp; &mdash;  <?=$ar['counts']?><br>
<?}?>
</div>

<h2><?=GetMEssage('AGV_TIME')?>:</h2>
<div class="stats">
<?foreach($arResult['state'][1] as $ar){?>
	<?=$ar['time']?><br>
<?}?>
</div>

<h2><?=GetMessage('PIT_PEOPLES')?>:</h2>
<div class="stats">
<?foreach($arResult['user'][0] as $ar){?>
	<?=htmlspecialchars($ar['user'])?> &nbsp; &mdash;  <?=$ar['counts']?><br>
<?}?>
</div>

<h2><?=GetMessage('FIXED_PIT_PEOPLES')?>:</h2>
<div class="stats">
<?foreach($arResult['user'][1] as $ar){?>
	<?=htmlspecialchars($ar['user'])?> &nbsp; &mdash;  <?=$ar['counts']?><br>
<?}?>
</div>