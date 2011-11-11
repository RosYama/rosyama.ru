<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="news-list-mainpage">
<?foreach($arResult["ITEMS"] as $arItem):?>	
	<div class="news-item">		
		<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
			<p  class="date"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></p>
		<?endif?>	
		
		<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
			<p><?echo $arItem["PREVIEW_TEXT"];?></p>
		<?endif;?>
		
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
			<div style="clear:both"></div>
		<?endif?>
		
		<a class="show" href="<?echo $arItem["DETAIL_PAGE_URL"]?>">>></a>
	</div>
<?endforeach;?>

<a href="/news/" class="news-all">Все новости</a>
<div style="clear:both"></div>

</div>
