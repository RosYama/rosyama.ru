<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule('greensight_utils') || !CModule::IncludeModule('st1234holes'))
{
	die('');
}
$arResult = array();
$arResult['FORM_ACTION'] = SITE_TEMPLATE_ID == 'print' ? '?print=Y' : '/';
$arResult['TYPE'] = array();
foreach(C1234HoleApi::$_allowed_types as $type)
{
	$arResult['TYPE'][$type] = GetMessage('FORM_HOLE_TYPE_'.$type);
}
$arResult['STATUS'] = array
(
	'fresh'      => GetMessage('FORM_HOLE_STATE_fresh'),
	'inprogress' => GetMessage('FORM_HOLE_STATE_inprogress'),
	'fixed'      => GetMessage('FORM_HOLE_STATE_fixed'),
	'achtung'    => GetMessage('FORM_HOLE_STATE_achtung'),
	'prosecutor' => GetMessage('FORM_HOLE_STATE_prosecutor')
);

$arResult['FILTER']['premoderated']     = $_REQUEST['filter_premoderated'] == 'on'? 'checked' : '';
$arResult['FILTER']['rf_subject_id']    = (int)($_REQUEST['filter_rf_subject_id']);
$arResult['FILTER']['rf_subject']       = $_REQUEST['filter_rf_subject'] ? htmlspecialcharsEx($_REQUEST['filter_rf_subject']) : ($arResult['FILTER']['rf_subject_id'] ? CGreensightRFSubject::$_RF_SUBJECTS_FULL[$arResult['FILTER']['rf_subject_id']] : GetMessage('HOLES_FILTER_RF_SUBJECT'));
$arResult['FILTER']['city']             = $_REQUEST['filter_city'] ? htmlspecialcharsEx($_REQUEST['filter_city']) : GetMessage('HOLES_FILTER_CITY');
$arResult['FILTER']['rf_subject_class'] = $_REQUEST['filter_rf_subject'] || $_REQUEST['filter_rf_subject_id'] ? '' : 'disabled';
$arResult['FILTER']['city_class']       = $_REQUEST['filter_city'] ? '' : 'disabled';
$arResult['FILTER']['type']             = htmlspecialcharsEx($_REQUEST['filter_type']);
$arResult['FILTER']['status']           = htmlspecialcharsEx($_REQUEST['filter_status']);
$arResult['SHOW_RESET_LINK']            = $_REQUEST['filter_rf_subject'] || $_REQUEST['filter_rf_subject_id'] || $_REQUEST['filter_city'];


$this->IncludeComponentTemplate();

?>