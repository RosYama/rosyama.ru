<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!$arResult["NavShowAlways"])
{
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
		return;
}

//echo "<pre>"; print_r($arResult);echo "</pre>";

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");

?>
<div class="pagination">


<?if ($arResult["NavPageCount"]>1):?>
<?if ($arResult["NavPageNomer"] >= $arResult["nPageWindow"]):?>
			<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1"><?=GetMessage("nav_begin")?></a>
		<?endif?>
<?if($arResult["bDescPageNumbering"] === true):?>

	

	<?if ($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
		
			<?if($arResult["bSavePage"]):?>
				
				
				<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>">&larr;</a>
				
			<?else:?>
				
			
				<?if ($arResult["NavPageCount"] == ($arResult["NavPageNomer"]+1) ):?>
					<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">&larr;</a>
				
				<?else:?>
					<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>">&larr;</a>
					
				<?endif?>
			<?endif?>
		<?else:?>
			&larr;
		<?endif?>

		<?while($arResult["nStartPage"] >= $arResult["nEndPage"]):?>
			<?$NavRecordGroupPrint = $arResult["NavPageCount"] - $arResult["nStartPage"] + 1;?>

			<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
				<b><?=$NavRecordGroupPrint?></b>
			<?elseif($arResult["nStartPage"] == $arResult["NavPageCount"] && $arResult["bSavePage"] == false):?>
				<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=$NavRecordGroupPrint?></a>
			<?else:?>
				<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$NavRecordGroupPrint?></a>
			<?endif?>

			<?$arResult["nStartPage"]--?>
		<?endwhile?>


		<?if ($arResult["NavPageNomer"] > 1):?>
			<a class="arrow" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>">&rarr;</a>
		
			
		<?else:?>
			&rarr;
		<?endif?>

	<?else:?>



		<?if ($arResult["NavPageNomer"] > 1):?>

			<?if($arResult["bSavePage"]):?>
				<a class="arrow" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=1">&larr;</a>
				
				<a class="arrow" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>">&larr;</a>
				
			<?else:?>
				
				
				<?if ($arResult["NavPageNomer"] > 2):?>
					<a class="arrow" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]-1)?>">&larr;</a>
				<?else:?>
					<a  class="arrow"  href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>">&larr;</a>
				<?endif?>
				
			<?endif?>

		<?else:?>
			
		<?endif?>

		<?while($arResult["nStartPage"] <= $arResult["nEndPage"]):?>

			<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
				<b><?=$arResult["nStartPage"]?></b>
			<?elseif($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false):?>
				<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=$arResult["nStartPage"]?></a>
			<?else:?>
				<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$arResult["nStartPage"]?></a>
			<?endif?>
			<?$arResult["nStartPage"]++?>
		<?endwhile?>
		

		<?if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
			<a class="arrow" href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>">&rarr;</a>&nbsp;
			
		<?else:?>
			&rarr;
		<?endif?>

	<?endif?>
	
	<?if ($arResult["NavPageNomer"] < $arResult["nPageWindow"]):?>
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageCount"]?>"><?=GetMessage("nav_end")?></a>
	<?endif?>

<?if ($arResult["bShowAll"]):?>
<noindex>
	<?if ($arResult["NavShowAll"]):?>
		&nbsp;&nbsp;<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>SHOWALL_<?=$arResult["NavNum"]?>=0" rel="nofollow"><?=GetMessage("nav_paged")?></a>
	<?else:?>
		&nbsp;&nbsp;<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>SHOWALL_<?=$arResult["NavNum"]?>=1" rel="nofollow"><?=GetMessage("nav_all")?></a>
	<?endif?>
</noindex>
<?endif?>
<?endif?>
</div>
