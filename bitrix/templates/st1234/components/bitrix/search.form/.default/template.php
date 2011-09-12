<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<form action="<?=$arResult["FORM_ACTION"]?>">
	<?if($arParams["USE_SUGGEST"] === "Y"):?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:search.suggest.input",
			"",
			array(
				"NAME" => "q",
				"VALUE" => "",
				"INPUT_SIZE" => 15,
				"DROPDOWN_SIZE" => 10,
			),
			$component, array("HIDE_ICONS" => "Y")
		);?>
	<?else:?>
		<input type="image" name="s" src="<?=SITE_TEMPLATE_PATH?>/images/search_btn.gif" class="btn" /><input type="text" class="textInput inactive" name="q"  value="Поиск по адресу" />
	<script type="text/javascript">
		$(document).ready(function(){
			var startSearchWidth=$('.search').width();
			var startSearchInputWidth=$('.search .textInput').width();
			var time=300;
			
			<?if($APPLICATION->GetCurPage() != "/" && $APPLICATION->GetCurPage() != "/personal/add.php" && $APPLICATION->GetCurPage()!="/about/" && !(defined("ERROR_404"))):?>
				var searchWidth=317;
				var searchInputWidth=searchWidth-30;
				if ($.browser.msie && $.browser.version == 9) {
					searchWidth+=1;
					}
			<?else:?>
				var searchWidth=420;
				var	searchInputWidth=searchWidth-30;
				
			<?endif?>
			<?
				global $USER;
				if (!$USER->IsAuthorized()):?>
				searchInputWidth-=40;
				searchWidth-=40;
			<?endif?>
				if ($.browser.msie && $.browser.version == 9) {
					searchInputWidth+=5;
					searchWidth+=5;
					}
				$('.search .textInput').click(function(){
					if ($(this).val()=='Поиск по адресу')
					{
						$(this).val('').removeClass('inactive');
					}
					$('.search').animate({width:searchWidth},time);
					$('.search .textInput').animate({width:searchInputWidth},time);
				})
				$('.search .textInput').blur(function(){
					
					if ($(this).val()=='')
					{
						$(this).val('Поиск по адресу').addClass('inactive');
					}
					$('.search').animate({width:startSearchWidth},time);
					$('.search .textInput').animate({width:startSearchInputWidth},time);
				})
			})
	</script>
	<?endif;?>
</form>

