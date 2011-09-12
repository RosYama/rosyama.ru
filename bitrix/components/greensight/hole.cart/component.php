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
if(!CModule::IncludeModule('iblock'))
{
	die();
}
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php');

global $USER;
$ID = (int)$arParams['ID'];
if(!$ID)
{
	return;
}

$arResult['HOLE'] = C1234Hole::GetById($ID);
$arResult['HOLE']['~DATE_CREATED'] = CGreensightUtils::timestamp2human($arResult['HOLE']['DATE_CREATED']);
if($arResult['HOLE']['DATE_SENT'])
{
	$arResult['HOLE']['~DATE_SENT'] = CGreensightUtils::timestamp2human($arResult['HOLE']['DATE_SENT']);
}
if($arResult['HOLE']['DATE_STATUS'])
{
	$arResult['HOLE']['~DATE_STATUS'] = CGreensightUtils::timestamp2human($arResult['HOLE']['DATE_STATUS']);
}
$arResult['HOLE']['COMMENT1'] = nl2br(htmlspecialcharsEx($arResult['HOLE']['COMMENT1']));
$arResult['HOLE']['COMMENT2'] = nl2br(htmlspecialcharsEx($arResult['HOLE']['COMMENT2']));
if($arResult['HOLE']['STATE'] == 'inprogress' && $arResult['HOLE']['DATE_SENT'] && !$arResult['HOLE']['GIBDD_REPLY_RECEIVED'])
{
	$arResult['HOLE']['WAIT_DAYS'] = 38 - ceil((time() - $arResult['HOLE']['DATE_SENT']) / 86400);
	$last_digit = (int)substr($arResult['HOLE']['WAIT_DAYS'], strlen($arResult['HOLE']['WAIT_DAYS']) - 1);
	if(substr($arResult['HOLE']['WAIT_DAYS'], strlen($arResult['HOLE']['WAIT_DAYS']) - 2, 1) == '1')
	{
		$arResult['HOLE']['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
	}
	elseif($last_digit > 4 || !$last_digit)
	{
		$arResult['HOLE']['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
	}
	elseif($last_digit > 1)
	{
		$arResult['HOLE']['WAIT_DAYS'] .= ' '.GetMessage('DAYS2');
	}
	else
	{
		$arResult['HOLE']['WAIT_DAYS'] .= ' '.GetMessage('DAY');
	}
}
elseif($arResult['HOLE']['STATE'] == 'achtung' && $arResult['HOLE']['DATE_SENT'])
{
	$arResult['HOLE']['PAST_DAYS'] = ceil((time() - $arResult['HOLE']['DATE_SENT']) / 86400) - 37;
	$last_digit = (int)substr($arResult['HOLE']['PAST_DAYS'], strlen($arResult['HOLE']['PAST_DAYS']) - 1);
	if(substr($arResult['HOLE']['PAST_DAYS'], strlen($arResult['HOLE']['PAST_DAYS']) - 2, 1) == '1')
	{
		$arResult['HOLE']['PAST_DAYS'] .= ' '.GetMessage('DAYS5');
	}
	elseif($last_digit > 4 || !$last_digit)
	{
		$arResult['HOLE']['PAST_DAYS'] .= ' '.GetMessage('DAYS5');
	}
	elseif($last_digit > 1)
	{
		$arResult['HOLE']['PAST_DAYS'] .= ' '.GetMessage('DAYS2');
	}
	else
	{
		$arResult['HOLE']['PAST_DAYS'] .= ' '.GetMessage('DAY');
	}
}
elseif($arResult['HOLE']['STATE'] == 'fixed')
{
	$arResult['allow_cancel_fix'] = sizeof($arResult['HOLE']['pictures']['original']['fixed']) ? false : true;
}
$arResult['PROSECUTOR_FORM_TO'] = CGreensightRFSubject::$_RF_SUBJECTS_GENITIVE[$arResult['HOLE']['ADR_SUBJECTRF']];
$arResult['AUTHOR'] = CUser::GetByID($arResult['HOLE']['USER_ID']);
$arResult['AUTHOR'] = $arResult['AUTHOR']->Fetch();
if($arResult['AUTHOR']['PERSONAL_PHOTO'])
{
	$arResult['AUTHOR']['PERSONAL_PHOTO'] = CFile::GetById($arResult['AUTHOR']['PERSONAL_PHOTO']);
	$arResult['AUTHOR']['PERSONAL_PHOTO'] = $arResult['AUTHOR']['PERSONAL_PHOTO']->Fetch();
}
$arResult['YANDEX_MAP_KEY'] = CIBlockPropertyMapYandex::_GetMapKey('yandex', $_SERVER['SERVER_NAME']);

// начальник и отделение ГИБДД, а также прокуратура
if($arResult['HOLE']['ADR_SUBJECTRF'])
{
	$res = CIBlockElement::GetList(array(), array('IBLOCK_CODE' => 'GIBDD_HEADS', 'PROPERTY_SUBJECT_ID' => $arResult['HOLE']['ADR_SUBJECTRF']), array('PROPERTY_POST_DATIVE', 'PROPERTY_FIO_DATIVE', 'PROPERTY_POST'));
	$ar  = $res->Fetch();
	$arResult['PDF_FORM_TO'] = $ar['PROPERTY_POST_DATIVE_VALUE'].' '.$ar['PROPERTY_FIO_DATIVE_VALUE'];
	$arResult['PROSECUTOR_GIBDD'] = explode(' ', $ar['PROPERTY_POST_VALUE']);
	$arResult['PROSECUTOR_GIBDD'] = array_slice($arResult['PROSECUTOR_GIBDD'], 1);
	if(ToUpper($arResult['PROSECUTOR_GIBDD'][0]) == 'УПРАВЛЕНИЯ')
	{
		$arResult['PROSECUTOR_GIBDD'][0] = 'УПРАВЛЕНИЕ';
	}
	$arResult['PROSECUTOR_GIBDD'] = implode(' ', $arResult['PROSECUTOR_GIBDD']);
	$res = CIBlockElement::GetList(array(), array('IBLOCK_CODE' => 'PROSECUTORS', 'PROPERTY_SUBJECT_ID' => $arResult['HOLE']['ADR_SUBJECTRF']));
	$arResult['PROSECUTOR_DATA'] = $res->Fetch();
}

if(isset($_GET['pdf']))
{
	ob_end_clean();
	ob_end_clean();
	ob_end_clean();
	ob_end_clean();
	
	$_images = array();
	$date3 = isset($_POST['application_data'])   ? strtotime($_POST['application_data']) : time();
	$date2 = $_POST['form_type'] == 'prosecutor' ? $arResult['HOLE']['DATE_SENT']        : time();
	$_data = array
	(
		'chief'       => iconv('utf-8', 'windows-1251', $_POST['to']),
		'fio'         => iconv('utf-8', 'windows-1251', $_POST['from']),
		'address'     => iconv('utf-8', 'windows-1251', $_POST['postaddress']),
		'date1.day'   => date('d', $arResult['HOLE']['DATE_CREATED']),
		'date1.month' => date('m', $arResult['HOLE']['DATE_CREATED']),
		'date1.year'  => date('Y', $arResult['HOLE']['DATE_CREATED']),
		'street'      => iconv('utf-8', 'windows-1251', $_POST['address']),
		'date2.day'   => date('d', $date2),
		'date2.month' => date('m', $date2),
		'date2.year'  => date('Y', $date2),
		'signature'   => iconv('utf-8', 'windows-1251', $_POST['signature']),
		'reason'      => iconv('utf-8', 'windows-1251', $_POST['comment']),
		'date3.day'   => date('d', $date3),
		'date3.month' => date('m', $date3),
		'date3.year'  => date('Y', $date3),
		'gibdd'       => iconv('utf-8', 'windows-1251', $_POST['gibdd']),
		'gibdd_reply' => iconv('utf-8', 'windows-1251', $_POST['gibdd_reply'])
	);
	header_remove('Content-Type');
	
	if(isset($_REQUEST['html']))
	{
		foreach($arResult['HOLE']['pictures']['original']['fresh'] as $src)
		{
			$_images[] = $src;
		}
		header('Content-Type: text/html; charset=utf8', true);
		$HT = new html1234();
		$HT->gethtml
		(
			$_POST['form_type'] ? $_POST['form_type'] : $arResult['HOLE']['TYPE'],
			$_data,
			$_images
		);
	}
	else
	{
		foreach($arResult['HOLE']['pictures']['original']['fresh'] as $src)
		{
			$_images[] = $_SERVER['DOCUMENT_ROOT'].$src;
		}
		header('Content-Type: application/pdf; charset=cp1251', true);
		$PDF = new pdf1234();
		$PDF->getpdf
		(
			$_POST['form_type'] ? $_POST['form_type'] : $arResult['HOLE']['TYPE'],
			$_data,
			$_images
		);
	}
	die();
}

$this->IncludeComponentTemplate();

?>