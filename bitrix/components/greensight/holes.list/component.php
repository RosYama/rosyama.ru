<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('st1234holes'))
{
	die();
}
if(!CModule::IncludeModule('greensight_utils'))
{
	die();
}

if(isset($_REQUEST['ajax']))
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/greensight/holes.list.filter/ajax.php');
	die();
}

if(isset($_GET['ID']))
{
	$APPLICATION->IncludeComponent('greensight:hole.cart', '', array('ID' => $_GET['ID'], 'PREMODERATION' => $arParams['PREMODERATION']));
	return;
}

$arParams['HOLES_PER_PAGE'] = $arParams['HOLES_PER_PAGE'] ? (int)$arParams['HOLES_PER_PAGE'] : 200;
$arResult['PAGE']           = (int)$_GET['p'];
$arResult['HOLES']          = array();

// фильтр
$arFilter = array();
$arResult['PAGINATOR_REQUEST_STR'] = '';
if($_REQUEST['filter_rf_subject_id'])
{
	$arFilter['ADR_SUBJECTRF'] = (int)$_REQUEST['filter_rf_subject_id'];
	$arResult['PAGINATOR_REQUEST_STR'] .= '&filter_rf_subject_id='.(int)$_REQUEST['filter_rf_subject_id'];
}
if($_REQUEST['filter_city'])
{
	$arFilter['ADR_CITY'] = htmlspecialcharsEx(str_replace('%', '', $_REQUEST['filter_city']));
	$arResult['PAGINATOR_REQUEST_STR'] .= '&filter_city='.htmlspecialcharsEx(str_replace('%', '', $_REQUEST['filter_city']));
}
if($_REQUEST['filter_type'])
{
	$arFilter['TYPE'] = htmlspecialcharsEx($_REQUEST['filter_type']);
	$arResult['PAGINATOR_REQUEST_STR'] .= '&filter_type='.htmlspecialcharsEx($_REQUEST['filter_type']);
}
if($_REQUEST['filter_status'])
{
	$arFilter['STATE'] = htmlspecialcharsEx($_REQUEST['filter_status']);
	$arResult['PAGINATOR_REQUEST_STR'] .= '&filter_status='.htmlspecialcharsEx($_REQUEST['filter_status']);
}

// премодерация
if($arParams['PREMODERATION'] == 'Y' && !$USER->IsAdmin())
{
	$arFilter['PREMODERATED'] = 1;
}

//если администратор поставил галочку "непроверенные дефекты"
if($_REQUEST['filter_premoderated'] == 'on' && $USER->IsAdmin() == true)
{
	$arFilter['PREMODERATED'] = 0;
	$arResult['PAGINATOR_REQUEST_STR'] .= '&filter_premoderated='.htmlspecialcharsEx($_REQUEST['filter_premoderated']);
}

$_holes = C1234Hole::GetList
(
	array
	(
		'ID' => 'desc'
	),
	$arFilter,
	array
	(
		'offset' => $arParams['HOLES_PER_PAGE'] * $arResult['PAGE'],
		'limit'  => $arParams['HOLES_PER_PAGE']
	),
	&$arResult['PAGES_COUNT']
);
foreach($_holes as &$hole)
{
	$hole['ADDRESS']  = htmlspecialcharsEx($hole['ADDRESS']);
	$hole['COMMENT1'] = htmlspecialcharsEx($hole['COMMENT1']);
	$hole['COMMENT2'] = htmlspecialcharsEx($hole['COMMENT2']);
	if($hole['STATE'] == 'inprogress' && $hole['DATE_SENT'] && !$hole['STATE'] != 'gibddre')
	{
		$hole['WAIT_DAYS'] = 38 - ceil((time() - $hole['DATE_SENT']) / 86400);
		$hole['WAIT_DAYS'] = GetMessage('WAIT').' '.(string)$hole['WAIT_DAYS'];
		$last_digit = (int)substr($hole['WAIT_DAYS'], strlen($hole['WAIT_DAYS']) - 1);
		if(substr($hole['WAIT_DAYS'], strlen($hole['WAIT_DAYS']) - 2, 1) == '1')
		{
			$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
		}
		elseif($last_digit > 4 || !$last_digit)
		{
			$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
		}
		elseif($last_digit > 1)
		{
			$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS2');
		}
		else
		{
			$hole['WAIT_DAYS'] .= ' '.GetMessage('DAY');
		}
	}
	elseif($hole['STATE'] == 'achtung' && $hole['DATE_SENT'])
	{
		$hole['PAST_DAYS'] = GetMessage('PAST');
	}
	$arResult['HOLES'][CGreensightUtils::timestamp2human($hole['DATE_CREATED'])][] = $hole;
}

if($USER->IsAdmin())
{
	$magic_url = '/';
	if(!empty($_REQUEST['filter_rf_subject_id']))
	{
		$sym = strlen($magic_url) == 1 ? '?' : '&';
		$magic_url .= $sym.'filter_rf_subject_id='.$_REQUEST['filter_rf_subject_id'];
	}
	if(!empty($_REQUEST['filter_rf_subject']))
	{
		$sym = strlen($magic_url) == 1 ? '?' : '&';
		$magic_url .= $sym.'filter_rf_subject='.$_REQUEST['filter_rf_subject'];
	}
	if(!empty($_REQUEST['filter_status']))
	{
		$sym = strlen($magic_url) == 1 ? '?' : '&';
		$magic_url .= $sym.'filter_status='.$_REQUEST['filter_status'];
	}
	if(!empty($_REQUEST['filter_type']))
	{
		$sym = strlen($magic_url) == 1 ? '?' : '&';
		$magic_url .= $sym.'filter_type='.$_REQUEST['filter_type'];
	}
	if(!empty($_REQUEST['filter_city']))
	{
		$sym = strlen($magic_url) == 1 ? '?' : '&';
		$magic_url .= $sym.'filter_city='.$_REQUEST['filter_city'];
	}
	if(!empty($_REQUEST['filter_premoderated']))
	{
		$sym = strlen($magic_url) == 1 ? '?' : '&';
		$magic_url .= $sym.'filter_premoderated='.$_REQUEST['filter_premoderated'];
	}
	$arResult['magic_url'] = $magic_url;
}

$this->IncludeComponentTemplate();

?>