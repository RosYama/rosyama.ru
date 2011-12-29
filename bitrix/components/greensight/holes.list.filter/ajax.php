<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule('greensight_utils') || !CModule::IncludeModule('st1234holes'))
{
	die();
}
while(ob_get_level()) ob_end_clean();

$arResult = array();
switch(ToLower($_REQUEST['ajax']))
{
	case 'getrfsubj':
	{
		foreach(CGreensightRFSubject::$_RF_SUBJECTS_FULL as $k => $subj)
		{
			if(stripos($subj, $_REQUEST['rfsubj']) !== false)
			{
				$text = str_ireplace(ToLower($_REQUEST['rfsubj']), '<span>'.ToLower($_REQUEST['rfsubj']).'</span>', $subj);
				$text = str_replace(CGreensightUtils::CapitalizeFirst($_REQUEST['rfsubj']), '<span>'.CGreensightUtils::CapitalizeFirst($_REQUEST['rfsubj']).'</span>', $text);
				$arResult['ITEMS'][] = array('id' => $k, 'text' => $text, 'onclick' => "onRFSubjClick('".$k."', '".$subj."')");
			}
		}
		break;
	}
	case 'getcity':
	{
		if(!strlen($_REQUEST['city']))
		{
			return;
		}
		global $DB;
		$_REQUEST['city'] = str_replace('%', '', $_REQUEST['city']);
		$arFilter = array('ADR_CITY' => $_REQUEST['city']);
		$_REQUEST['rfsubjid'] = (int)$_REQUEST['rfsubjid'];
		if($_REQUEST['rfsubjid'])
		{
			$arFilter['ADR_SUBJECTRF'] = $_REQUEST['rfsubjid'];
		}
		$res = C1234Hole::GetList(array(), $arFilter, array('offset' => 0, 'limit' => 100, 'nopicts' => true));
		$arResult = array();
		foreach($res as $hole)
		{
			$hole['ADR_CITY'] = trim($hole['ADR_CITY']);
			$text = str_ireplace($_REQUEST['city'], '<span>'.$_REQUEST['city'].'</span>', $hole['ADR_CITY']);
			$text = str_replace(CGreensightUtils::CapitalizeFirst($_REQUEST['city']), '<span>'.CGreensightUtils::CapitalizeFirst($_REQUEST['city']).'</span>', $text);
			$arResult['ITEMS'][$hole['ADR_CITY']] = array('id' => $hole['ADR_CITY'], 'text' => $text, 'onclick' => "onCityClick('".$hole['ADR_CITY']."');");
		}
		break;
	}
}

$this->IncludeComponentTemplate('ajaxlist');

?>
