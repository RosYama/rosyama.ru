<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if (!empty($arResult)):?>
	<ul class="menu">
	<?
	$i=0;
	foreach($arResult as $arItem):
		if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
			continue;
	?>
		<? if($arItem["SELECTED"]): ?>
			<li class="<? if($i==0): ?>first <? elseif($i==count($arResult)-1&&$APPLICATION->GetCurPage()!="/"&&$APPLICATION->GetCurPage()!="/personal/add.php"):?>last <?endif;?>selected"><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?><i></i><b></b></a></li>
		<? else: ?>
			<li<? if($i==0): ?> class="first"<? elseif($i==count($arResult)-1&&$APPLICATION->GetCurPage()!="/"&&$APPLICATION->GetCurPage()!="/personal/add.php" && $APPLICATION->GetCurPage()!="/about/"&&!(defined("ERROR_404"))):?> class="last"<?endif;?>><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?><i></i><b></b></a></li>
		<?
		endif;
	$i++;
	endforeach;
	?>
	<?if($APPLICATION->GetCurPage() != "/" && $APPLICATION->GetCurPage() != "/personal/add.php" && $APPLICATION->GetCurPage()!="/about/" && !(defined("ERROR_404"))):?>
		<li class="add"><a href="/personal/add.php">Добавить<i></i><b></b></a></li>
	<?endif;?>
	</ul>
<?endif?>