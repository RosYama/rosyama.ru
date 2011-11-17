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

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php');
global $USER;

$mode = 'add';

// определение id
if($arParams['SENT_ID'])
{
	// этой яме надо поставить статус "отнёс заявление в гаи"
	$hole_id = (int)$arParams['SENT_ID'];
	$mode    = 'update-setinprogress';
}
elseif($arParams['FIX_ID'])
{
	// этой яме надо поставить статус "исправлено"
	$hole_id = (int)$arParams['FIX_ID'];
	$mode    = 'update-setfixed';
}
elseif($arParams['DELETE_ID'])
{
	// эту яму надо удалить
	$hole_id = (int)$arParams['DELETE_ID'];
	$mode    = 'delete';
}
elseif($arParams['CANCEL_ID'])
{
	// этой яме надо отменить факт отправки в гибдд
	$hole_id = (int)$arParams['CANCEL_ID'];
	$mode    = 'update-revoke';
}
elseif($arParams['REFIX_ID'])
{
	// этой яме надо отменить факт исправления
	$hole_id = (int)$arParams['REFIX_ID'];
	$mode    = 'update-setinprogress';
}
elseif($arParams['GIBDD_REPLY_ID'])
{
	// этой яме надо поставить статус "получен ответ из ГИБДД"
	$hole_id = (int)$arParams['GIBDD_REPLY_ID'];
	$mode    = 'update-setreplied';
}
elseif($arParams['PROSECUTOR_ID'])
{
	// этой яме надо поставить статус "жалоба на ГИБДД в прокуратуре"
	$hole_id = (int)$arParams['PROSECUTOR_ID'];
	$mode    = 'update-toprosecutor';
}
elseif($arParams['REPROSECUTOR_ID'])
{
	// этой яме надо отменить статус "жалоба на ГИБДД в прокуратуре"
	$hole_id = (int)$arParams['REPROSECUTOR_ID'];
	$mode    = 'update-revokep';
}
elseif($arParams['PREMODERATE_ID'] && $USER->IsAdmin())
{
	// этой яме надо поставить отметку, что она допущена
	$hole_id = (int)$arParams['PREMODERATE_ID'];
	// такого режима нет $mode = 'update-setpremoderated';
}
elseif($arParams['PREMODERATE_ALL'] && $USER->IsAdmin())
{
	$hole_id = -1;
	$id      = explode(',', $arParams['PREMODERATE_ALL']);
	// такого режима нет $mode    = 'update-setpremoderated';
	foreach($id as $key => $val)
	{
		$id[$key] = (int)$val;
	}
}
elseif($arParams['DELETE_ALL'] && $USER->IsAdmin())
{
	$hole_id = -1;
	$id      = explode(',', $arParams['DELETE_ALL']);
	$mode    = 'delete';
	foreach($id as $key => $val)
	{
		$id[$key] = (int)$val;
	}
}
// это должно быть в самом конце
elseif($arParams['ID'])
{
	$hole_id = (int)$arParams['ID'];
	$mode    = 'update-common';
}
elseif($_POST['ID'])
{
	$hole_id = (int)$_POST['ID'];
	$mode    = 'update-common';
}

// если hole_id есть, то, значит, редактирование или удаление ямы
if($hole_id > 0)
{
	$arElement = C1234Hole::GetById($hole_id);
}
// проверка доступа
if($hole_id && $arElement['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
{
	ShowError(GetMessage('ACCESS_DENIED'));
	return;
}
// координаты точек и центра карты
if(!$hole_id)
{
	if($_SERVER['HTTP_X_FORWARDED_FOR'])
	{
		$forwarded_for = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$forwarded_for = $forwarded_for[0];
	}
	$arResult['map_center'] = CGreensightGeoip::GetCoordinatesByIP($forwarded_for ? $forwarded_for : $_SERVER['REMOTE_ADDR']);
	if(!$arResult['map_center'])
	{
		$map_center = strlen($arResult['HOLE']['~COORDINATES_R'] > 2) ? $arResult['HOLE']['~COORDINATES_R'] : false;
		if(!$map_center && $arResult['PLACEMARKS'])
		{
			$map_center = array();
			foreach($arResult['PLACEMARKS'] as &$p)
			{
				$map_center[0] += $p['LON'];
				$map_center[1] += $p['LAT'];
			}
			$map_center = ($map_center[0] / sizeof($arResult['PLACEMARKS'])).','.($map_center[1] / sizeof($arResult['PLACEMARKS']));
		}
		$arResult['map_center'] = $map_center;
	}
	else
	{
		$arResult['map_center'] = $arResult['map_center']['longitude'].','.$arResult['map_center']['latitude'];
	}
	if($_REQUEST['coord'])
	{
		$arElement['~COORDINATES'] = $_REQUEST['coord'];
	}
}
if(!$arResult['map_center'] && $arElement)
{
	$arResult['map_center'] = $arElement['LONGITUDE'].','.$arElement['LATITUDE'];
}
if(!$arResult['map_center'])
{
	// если неизвестно, где пользователь, покажем центр Москвы
	$arResult['map_center'] = '37.609218,55.753559';
}
if(!$hole_id)
{
	$arResult['~map_center'] = explode(',', $arResult['map_center']);
	$_holes = C1234Hole::GetList
	(
		array(),
		array
		(
			'!ID'        => $arResult['HOLE']['ID'],
			'>LATITUDE'  => $arResult['~map_center'][1] - 5,
			'<LATITUDE'  => $arResult['~map_center'][1] + 5,
			'>LONGITUDE' => $arResult['~map_center'][0] - 5,
			'<LONGITUDE' => $arResult['~map_center'][0] + 5
		)
	);
	$f = 0;
	foreach($_holes as &$hole)
	{
		$arResult['PLACEMARKS'][$f]["TYPE"]        = $hole['TYPE'];
		$arResult['PLACEMARKS'][$f]["LON"]         = $hole["LONGITUDE"];
		$arResult['PLACEMARKS'][$f]["LAT"]         = $hole["LATITUDE"];
		$arResult['PLACEMARKS'][$f]["TEXT"]        = $hole["TYPE"];
		$arResult['PLACEMARKS'][$f]["DESCRIPTION"] = $hole["COMMENT1"];
		$arResult['PLACEMARKS'][$f]["STATE"]       = $hole["STATE"];
		$arResult['PLACEMARKS'][$f]["ID"]          = $hole["ID"];
		$f++;
	}
}
$arElement['~COORDINATES_R'] = explode(',', $arElement['~COORDINATES']);
$arElement['~COORDINATES_R'] = $arElement['~COORDINATES_R'][1].','.$arElement['~COORDINATES_R'][0];

// добавление или редактирование ямы
if($_POST)
{
	// трансляция полей поста для обратной совместимости
    // с кустарными приложениями. после открытия апи это можно будет
    // убрать
    if(isset($_POST['COMMENT'])     && !isset($_POST['comment']))     $_POST['comment']     = $_POST['COMMENT'];
    if(isset($_POST['ADDRESS'])     && !isset($_POST['address']))     $_POST['address']     = $_POST['ADDRESS'];
    if(isset($_POST['TYPE'])        && !isset($_POST['type']))        $_POST['type']        = $_POST['TYPE'];
    if(isset($_POST['COORDINATES']) && !isset($_POST['coordinates'])) $_POST['coordinates'] = implode(',', array_reverse(explode(',', $_POST['COORDINATES'])));
	do
	{
		$_POST['ID'] = (int)$_POST['ID'];
		if($_POST['ID'])
		{
			if($_POST['FIX_ID'])
			{
				// пометка ямы как исправленной
				ob_start();
				C1234HoleApi::Execute($mode, $_POST['ID']);
				$xml = simplexml_load_string(ob_get_clean());
				if($xml->callresult == 'ok')
				{
					LocalRedirect('/'.$_POST['ID'].'/');
					die();
				}
				else
				{
					foreach($xml->error as $error)
					{
						$arResult['ERROR_STR'] .= $error.'<br/>';
					}
					$arParams['FIX_ID'] = $_POST['ID'];
				}
			}
			elseif($_POST['GIBDD_REPLY_ID'])
			{
				$bFilesAdded = false;
				foreach($_FILES as $f)
				{
					if($f['tmp_name'] && !$f['error'])
					{
						$bFilesAdded = true;
					}
				}
				if(!$bFilesAdded)
				{
					$arResult['ERROR_STR']      = GetMessage('HOLE_ERROR_GIDBB_REPLY_SCAN_REQUIRED');
					$arParams['GIBDD_REPLY_ID'] = $hole_id;
				}
				else
				{
					// пометка ямы как "пришёл ответ из ГИБДД"
					ob_start();
					C1234HoleApi::Execute($mode, $_POST['ID']);
					$xml = simplexml_load_string(ob_get_clean());
					if($xml->callresult == 'ok')
					{
						LocalRedirect('/'.$_POST['ID'].'/');
						die();
					}
					else
					{
						foreach($xml->error as $error)
						{
							$arResult['ERROR_STR'] .= $error.'<br/>';
						}
						$arParams['GIBDD_REPLY_ID'] = $_POST['ID'];
					}
				}
			}
			else
			{
				if($arElement['STATE'] == 'fresh')
				{
					// редактирование ямы
					$_deleteimages = array();
					foreach($arElement['pictures']['original']['fresh'] as &$picture)
					{
						$picture_id = explode('/', $picture);
						$picture_id = explode('.', $picture_id[sizeof($picture_id) - 1]);
						$picture_id = $picture_id[0];
						if($_POST['deletepict_'.$picture_id])
						{
							$_deleteimages[] = $picture_id.'.jpg';
						}
					}
					$_addimages = array();
					foreach($_FILES as &$f)
					{
						if($f['tmp_name'])
						{
							$_addimages[] = $f;
						}
					}
					if
					(
						sizeof($_deleteimages) > (sizeof($_addimages) + sizeof($arElement['pictures']['original']['fresh'])) ||
						(sizeof($_deleteimages) == sizeof($arElement['pictures']['original']['fresh']) && !sizeof($_addimages))
					)
					{
						$arResult['ERROR_STR'] = GetMessage('EDIT_HOLE_FORM_ERROR_DELETE_MORE_THEN_ADD_FILES');
					}
					else
					{
						if($_POST['coordinates'] == '1')
						{
							$_POST['coordinates'] = $arElement['LATITUDE'].','.$arElement['LONGITUDE'];
						}
						ob_start();
						C1234HoleApi::Execute($mode, $_POST['ID']);
						$xml = simplexml_load_string(ob_get_clean());
						if($xml->callresult == 'ok')
						{
							LocalRedirect('/'.$_POST['ID'].'/');
							die();
						}
						else
						{
							foreach($xml->error as $error)
							{
								$arResult['ERROR_STR'] .= $error.'<br/>';
							}
							$arParams['ID'] = $_POST['ID'];
						}
					}
				}
			}
		}
		else
		{
			// добавление ямы
			$bFileAdded = false;
			foreach($_FILES as $f)
			{
				if($f['tmp_name'])
				{
					$bFileAdded = true;
					break;
				}
			}
			if($bFileAdded)
			{
				ob_start();
				C1234HoleApi::Execute($mode, 0);
				$xml     = simplexml_load_string(ob_get_clean());
				$hole_id = (string)$xml->callresult->attributes()->inserteddefectid;
				$hole_id = (int)$hole_id;
				if($xml->callresult == 'ok' && $hole_id)
				{
					LocalRedirect('/'.$hole_id.'/');
					die();
				}
				else
				{
					foreach($xml->error as $error)
					{
						$arResult['ERROR_STR'] .= $error.'<br/>';
					}
				}
			}
			else
			{
				$arResult['ERROR_STR'] = GetMessage('ADD_HOLE_FORM_ERROR_NO_FILES');
			}
		}
	}
	while(false);
}

// обработка некоторых вариантов обновления дефекта
if
(
	$arParams['SENT_ID']
	|| $arParams['CANCEL_ID']
	|| $arParams['REFIX_ID']
	|| $arParams['PROSECUTOR_ID']
	|| $arParams['REPROSECUTOR_ID']
)
{
	ob_start();
	C1234HoleApi::Execute($mode, $hole_id);
	$xml = simplexml_load_string(ob_get_clean());
	if($xml->callresult == 'ok')
	{
		LocalRedirect('/'.$hole_id.'/');
		die();
	}
	else
	{
		foreach($xml->error as $error)
		{
			$arResult['ERROR_STR'] .= $error.'<br/>';
		}
	}
}
elseif($arParams['DELETE_ID'])
{
	// эту яму надо удалить
	if($USER->IsAdmin() && $_GET['banuser'] && $arElement['USER_ID'] != 1)
	{
		// а её автора - забанить
		$u = new CUser();
		$u->Update($arElement['USER_ID'], array('ACTIVE' => 'N'));
	}
	if($arElement['STATE'] == 'fresh' || $USER->IsAdmin())
	{
		C1234Hole::Delete($hole_id);
	}
	$url = isset($_GET['magic_url']) ? $_GET['magic_url'] : '/';
	LocalRedirect(htmlspecialchars($url));
	die();
}
elseif($arParams['DELETE_ALL'])
{
	if($USER->IsAdmin())
	{
		foreach($id as $val)
		{
			C1234Hole::Delete($val);
		}
	}
	if($_GET['ajax'])
	{
		ob_end_clean();
		echo 'ok';
		die();
	}
}
elseif($arParams['PREMODERATE_ID'])
{
	// этой яме ставим отметку, что она допущена
	if(!C1234Hole::Update(
		$hole_id,
		array
		(
			'PREMODERATED' => 1
		),
		false,
		&$error
	))
	{
		if($_GET['ajax'])
		{
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			echo $error;
			die();
		}
		else
		{
			$arResult['ERROR_STR'] = $error;
		}
	}
	else
	{
		if($_GET['ajax'])
		{
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			echo 'ok';
			die();
		}
		else
		{
			LocalRedirect('/'.$hole_id.'/');
			die();
		}
	}
}
elseif($arParams['PREMODERATE_ALL'])
{
	foreach($id as $key => $val)
	{
		if(!C1234Hole::Update(
			$val,
			array
			(
				'PREMODERATED' => 1
			),
			false,
			&$error
		))
		{
			$arResult['ERROR_STR'] = $error;
			break;
		}
	}
	
	if(!$arResult['ERROR_STR'])
	{
		if($_GET['ajax'])
		{
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			ob_end_clean();
			echo 'ok';
			die();
		}
		else
		{
			LocalRedirect('/');
			die();
		}
	}
}

$arResult['FORM'] = array
(
	'ID'      => 'holeform',
	'METHOD'  => 'post',
	'ENCTYPE' => 'multipart/form-data',
	'LEGEND'  => $hole_id ? GetMessage('EDIT_HOLE_FORM') : GetMessage('ADD_HOLE_FORM'),
	'FIELDS'  => array
	(
		'ID' => array
		(
			'ID'    => 'ID',
			'NAME'  => 'ID',
			'TYPE'  => 'hidden',
			'VALUE' => $hole_id ? $arElement['ID'] : 0
		)
	)
);
$arResult['FORM']['ACTION'] = explode('/', $_SERVER['SCRIPT_NAME']);
$arResult['FORM']['ACTION'] = $arResult['FORM']['ACTION'][sizeof($arResult['FORM']['ACTION']) - 1];
$arResult['FORM']['FIELDS']['PHOTOS'] = array
(
	'ID'       => 'PHOTOS',
	'NAME'     => 'PHOTOS',
	'TYPE'     => 'multifile',
	'LABEL'    => $arParams['FIX_ID'] ? GetMessage('FORM_HOLE_FIX_PHOTOS') : ($hole_id ? GetMessage('FORM_HOLE_PHOTOS_ADD') : GetMessage('FORM_HOLE_PHOTOS')),
	'REQUIRED' => !$hole_id
);
// это вот определение чего можно показывать, а чего нельзя, тоже неплохо бы сделать через API
if(!$arParams['FIX_ID'] && !$arParams['GIBDD_REPLY_ID'])
{
	$arResult['FORM']['FIELDS']['COORDINATES'] = array
	(
		'ID'    => 'coordinates',
		'NAME'  => 'coordinates',
		'TYPE'  => 'yandexmap',
		'VALUE' => htmlspecialcharsEx($hole_id ? $arElement['~COORDINATES'] : $_POST['coordinates'])
	);
	$arResult['FORM']['FIELDS']['TYPE'] = array
	(
		'ID'    => 'TYPE',
		'NAME'  => 'type',
		'TYPE'  => 'select',
		'LABEL' => GetMessage('FORM_HOLE_TYPE'),
		'VALUE' => $arElement['TYPE'] ? $arElement['TYPE'] : ($_POST['type'] ? htmlspecialcharsEx($_POST['type']) : 'holeonroad'),
		'ITEMS' => array()
	);
	foreach(C1234HoleApi::$_allowed_types as $type)
	{
		$arResult['FORM']['FIELDS']['TYPE']['ITEMS'][$type] = GetMessage('FORM_HOLE_TYPE_'.$type);
	}
	$arResult['FORM']['FIELDS']['ADDRESS'] = array
	(
		'ID'    => 'ADDRESS',
		'NAME'  => 'address',
		'TYPE'  => 'textarea',
		'LABEL' => GetMessage('FORM_HOLE_ADDRESS'),
		'VALUE' => htmlspecialcharsEx($hole_id ? $arElement['ADDRESS'] : $_POST['address'])
	);
	$arResult['FORM']['FIELDS']['COMMENT1'] = array
	(
		'ID'    => 'COMMENT1',
		'NAME'  => 'comment',
		'TYPE'  => 'textarea',
		'LABEL' => GetMessage('FORM_HOLE_COMMENT'),
		'VALUE' => htmlspecialcharsEx($hole_id ? $arElement['COMMENT1'] : $_POST['comment'])
	);
}
elseif($arParams['FIX_ID'])
{
	$arResult['FORM']['FIELDS']['FIX_ID'] = array
	(
		'ID'    => 'FIX_ID',
		'NAME'  => 'FIX_ID',
		'TYPE'  => 'hidden',
		'VALUE' => $arParams['FIX_ID']
	);
	$arResult['FORM']['FIELDS']['COMMENT2'] = array
	(
		'ID'    => 'COMMENT2',
		'NAME'  => 'comment',
		'TYPE'  => 'textarea',
		'LABEL' => GetMessage('FORM_HOLE_COMMENT'),
		'VALUE' => htmlspecialcharsEx($_POST['comment'])
	);
}
elseif($arParams['GIBDD_REPLY_ID'])
{
	$arResult['FORM']['FIELDS']['GIBDD_REPLY_ID'] = array
	(
		'ID'    => 'GIBDD_REPLY_ID',
		'NAME'  => 'GIBDD_REPLY_ID',
		'TYPE'  => 'hidden',
		'VALUE' => $arParams['GIBDD_REPLY_ID']
	);
	$arResult['FORM']['FIELDS']['PHOTOS'] = array
	(
		'ID'       => 'PHOTOS',
		'NAME'     => 'PHOTOS',
		'TYPE'     => 'multifile',
		'LABEL'    => GetMessage('FORM_HOLE_GIBDD_REPLY_PHOTOS'),
		'REQUIRED' => true
	);
	$arResult['FORM']['FIELDS']['COMMENT2'] = array
	(
		'ID'    => 'COMMENT2',
		'NAME'  => 'comment',
		'TYPE'  => 'textarea',
		'LABEL' => GetMessage('FORM_HOLE_COMMENT2'),
		'VALUE' => htmlspecialcharsEx($_POST['comment'] ? $_POST['comment'] : $arElement['COMMENT_GIBDD_REPLY'])
	);
}
$arResult['FORM']['FIELDS']['SUBMIT'] = array
(
	'TYPE'  => 'submit',
	'VALUE' => $hole_id ? GetMessage('FORM_HOLE_SUBMIT_EDIT') : GetMessage('FORM_HOLE_SUBMIT_ADD')
);
$arResult['HOLE'] = $arElement;
$arResult['YANDEX_MAP_KEY'] = CIBlockPropertyMapYandex::_GetMapKey('yandex', $_SERVER['SERVER_NAME']);

// маленький штришок
if($arParams['FIX_ID'])
{
	$APPLICATION->SetTitle(GetMessage('HOLE_SET_FIXED'));
}
$arResult['upload_max_filesize'] = ini_get('upload_max_filesize');
if(!$arResult['upload_max_filesize'])
{
	$arResult['upload_max_filesize'] = '1&nbsp;'.GetMessage('MB');
}
else
{
	$arResult['upload_max_filesize'] = substr($arResult['upload_max_filesize'], 0, strlen($arResult['upload_max_filesize']) - 1).'&nbsp;'.GetMessage(ToUpper(substr($arResult['upload_max_filesize'], strlen($arResult['upload_max_filesize']) - 1)).'B');
}
$arResult['post_max_size'] = ini_get('post_max_size');
if(!$arResult['post_max_size'])
{
	$arResult['post_max_size'] = '8&nbsp;'.GetMessage('MB');
}
else
{
	$arResult['post_max_size'] = substr($arResult['post_max_size'], 0, strlen($arResult['post_max_size']) - 1).'&nbsp;'.GetMessage(ToUpper(substr($arResult['post_max_size'], strlen($arResult['post_max_size']) - 1)).'B');
}

$this->IncludeComponentTemplate();

?>