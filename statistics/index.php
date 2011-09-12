<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Статистика");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Статистика");?>
<div class="rCol">
	<h1><?= $APPLICATION->ShowTitle(); ?></h1>
</div>
</div></div>
<div class="mainCols">
<div class="lCol">
<?include($_SERVER['DOCUMENT_ROOT'].'/'.SITE_TEMPLATE_PATH.'/include_areas/social.php');?>
</div>
<div class="rCol">

<?$APPLICATION->IncludeComponent('greensight:statistics', false, array('LIMIT' => 10));?>
</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>