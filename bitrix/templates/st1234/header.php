<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html>
<html>
<head>
<?$APPLICATION->ShowHead()?>
<title><?$APPLICATION->ShowTitle()?></title>
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<script src="/js/scripts.js" type="text/javascript" charset="utf-8"></script>
<!--[if lte IE 7]><link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/ie.css" type="text/css" /><![endif]-->
<!-- Put this script tag to the <head> of your page -->
<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?22"></script>
<script  src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript">VK.init({apiId: 2232074, onlyWidgets: true});</script>
<script type="text/javascript">
					$(document).ready(function(){
						if ($('.name  a').width()>$('.auth .name').width())
							{
								$('.grad').show()
							}
					})
				</script>
</head>
<body>
<?$APPLICATION->ShowPanel();?>
<!--
<div id="headsquare">
	<?$APPLICATION->IncludeFile(
		$APPLICATION->GetTemplatePath("include_areas/highannounce.php"),
		Array(),
		Array("MODE"=>"html")
	);?>
</div>
-->
<div class="wrap">
	<div class="navigation">
		<div class="container">
			<?$APPLICATION->IncludeComponent("bitrix:menu", "main_menu", array(
				"ROOT_MENU_TYPE" => "top",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "0",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "N",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
				),
				false
			);?>
			<div class="search">
				<?$APPLICATION->IncludeComponent(
					"bitrix:search.form",
					"",
					Array(
						"USE_SUGGEST" => "N",
						"PAGE" => "#SITE_DIR#map"
					),
					false
				);?>
			</div>
			<div class="auth">
				<? if($USER->GetID()): ?>
					<a href="/?logout=yes" title="Выйти"><img src="<?=SITE_TEMPLATE_PATH?>/images/logout.png" alt="Выйти" /></a>
					<div class="name">
						<p><a href="/personal/holes.php"><?= strlen($USER->GetFullName()) ? $USER->GetFullName() : $USER->GetLogin() ?> </a></p><span class="grad"></span>
					</div>
				<? else: ?>
					<a href="/personal/holes.php" class="profileBtn">Войти</a>
				<? endif; ?>
				<?if($APPLICATION->GetCurPage() != "/" && $APPLICATION->GetCurPage() != "/personal/add.php" && $APPLICATION->GetCurPage()!="/about/" && !(defined("ERROR_404"))):?>
					<style type="text/css">
						.auth .name
						{
							width: 150px !important;
						}
						
					</style>
					
				<?endif?>
				
				<i></i>
			</div>
		</div>
	</div>
	<div class="head">
		<div class="container">
			<div class="lCol">
			<? $curpage=$APPLICATION->GetCurPage()?>
				<?if($curpage!="/" || $_GET || $_POST):?>
					<a href="/" class="logo" title="На главную"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png"  alt="РосЯма" /></a>
				<?else:?>
					<h1 class="logo"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="РосЯма" /></h1>
				<?endif;?>
				<?if($curpage=="/" || $curpage=="/about/" || $curpage=="/about/112/"  || defined("ERROR_404")):?>
					<div class="btn">
						<a href="/personal/add.php" class="addFact"><i class="text">Добавить</i><i class="arrow"></i></a>
					</div>
				<?endif;?>
			</div>
			<?if($curpage=="/" || $curpage=="/about/" || $curpage=="/about/112/" || $curpage=="/news/" || $curpage=="/faq/" ||defined("ERROR_404")):?>
			<div class="rCol">
				<?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath("include_areas/main_head_text.php"),
					Array(),
					Array("MODE"=>"html")
				);?>
			</div>
		</div>
	</div>
	<div class="mainCols">
			<?elseif($curpage == '/personal/add.php'):?>
			<div class="rCol">
				<?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath("include_areas/add_form_head_text.php"),
					Array(),
					Array("MODE"=>"html")
				);?>
			</div>
		</div>
	</div>
	<div class="mainCols">
			<?elseif(substr($curpage, 0, 10) == '/personal/'):?>
			<div class="rCol">
				<?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath("include_areas/personal_data.php"),
					Array(),
					Array("MODE"=>"html")
				);?>
			</div>
		</div>
	</div>
	<div class="mainCols">
			<?elseif(substr($curpage, 0, 10) == '/map/'):?>
			<div class="rCol">
				<?$APPLICATION->IncludeFile(
					$APPLICATION->GetTemplatePath("include_areas/map_data.php"),
					Array(),
					Array("MODE"=>"html")
				);?>
			</div>
		</div>
	</div>
	<div class="mainCols">
			<?endif;?>