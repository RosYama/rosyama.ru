<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Справочник ГИБДД");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Статья 12.34");

if ($_GET["ID"]) $subject=$_GET["ID"];
else  $subject=0;

?> 
<h1><?= $APPLICATION->ShowTitle(); ?></h1>
</div></div>
<br clear="all" />
<div class="mainCols">
<?
//Не нашел как по свойству выцепить элемент придется сделать так:
CModule::IncludeModule('iblock');
$arElement=Array();
$res = CIBlockElement::GetList(array(), array('IBLOCK_CODE' => 'GIBDD_HEADS', 'PROPERTY_SUBJECT_ID' => $subject), array('ID'));
if ($res) $arElement = $res->Fetch();
else $arElement['ID']=0;
$APPLICATION->IncludeComponent("bitrix:news.detail", "sprav", array(
	"IBLOCK_TYPE" => "REFERENCE",
	"ELEMENT_ID" => $arElement['ID'],
	"ELEMENT_CODE" => "",
	"CHECK_DATES" => "Y",
	"FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"PROPERTY_CODE" => array(
		0 => "FIO",
		1 => "POST",
		2 => "ADDRESS",
		3 => "TEL_DEGURN",
		4 => "TEL_DOVER",
		5 => "URL",
		6 => "",	
	),
	"IBLOCK_URL" => "/sprav/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "Y",
	"META_KEYWORDS" => "-",
	"META_DESCRIPTION" => "-",
	"BROWSER_TITLE" => "NAME",
	"SET_TITLE" => "Y",
	"SET_STATUS_404" => "N",
	"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
	"ADD_SECTIONS_CHAIN" => "Y",
	"ACTIVE_DATE_FORMAT" => "d.m.Y",
	"USE_PERMISSIONS" => "N",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "Страница",
	"PAGER_TEMPLATE" => "",
	"PAGER_SHOW_ALL" => "Y",
	"DISPLAY_DATE" => "Y",
	"DISPLAY_NAME" => "Y",
	"DISPLAY_PICTURE" => "Y",
	"DISPLAY_PREVIEW_TEXT" => "Y",
	"USE_SHARE" => "N",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
<br/><br/>
<?php 
//Не нашел как по свойству выцепить элемент придется сделать так:
$arElement=Array();
CModule::IncludeModule('iblock');
$res = CIBlockElement::GetList(array(), array('IBLOCK_CODE' => 'PROSECUTORS', 'PROPERTY_SUBJECT_ID' => $subject), array('ID'));
if ($res) $arElement = $res->Fetch();
else $arElement['ID']=0;
if ($arElement['ID']>0) {
$APPLICATION->IncludeComponent("bitrix:news.detail", "sprav", array(
	"IBLOCK_TYPE" => "REFERENCE",
	"ELEMENT_ID" => $arElement['ID'],
	"ELEMENT_CODE" => "",
	"CHECK_DATES" => "Y",
	"FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"PROPERTY_CODE" => array(
		0 => "GIBDD_NAME",
		1 => "",
	),
	"IBLOCK_URL" => "/sprav/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "Y",

	"SET_STATUS_404" => "N",
	
	"ACTIVE_DATE_FORMAT" => "d.m.Y",	
	"USE_PERMISSIONS" => "N",
	"DISPLAY_NAME" => "Y",
	"DISPLAY_PICTURE" => "Y",
	"DISPLAY_PREVIEW_TEXT" => "Y",
	"USE_SHARE" => "N",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);
}
?>


  
 </div>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>