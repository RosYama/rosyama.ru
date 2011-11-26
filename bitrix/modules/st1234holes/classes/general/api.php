<?php

class C1234HoleApi
{
	// типы дефектов
	public static $_allowed_types    = array('badroad', 'holeonroad', 'hatch', 'rails', 'holeinyard');
	public static $_deprecated_types = array('crossing', 'nomarking', 'policeman', 'fence', 'light');
	
	/**
	 * Запилить!
	 * @param string $mode    режим вызова
	 * @param int    $hole_id номер дефекта
	 */
	public static function Execute($mode, $hole_id)
	{
		global $USER;
		if($mode == 'pdf')
		{
			ob_start();
		}
		
		// path
		$_path = explode('?', ltrim($_SERVER['REQUEST_URI'], '/'));
		$_path[0] = explode('/', trim($_path[0], ' /'));
		
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		// стандартный заголовок ответа
		{
?><st1234reply>
	<requesttime><?= $_SERVER['REQUEST_TIME'] ?></requesttime>
	<requestmethod><?= $_SERVER['REQUEST_METHOD'] ?></requestmethod>
	<replytime><?= time() ?></replytime>
<?
		}
		
		// обработка вызова в зависимости от режима
		switch($mode)
		{
			case 'add':
			{
				// добавление ямы
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				// проверка адреса
				$_POST['address'] = trim($_POST['address']);
				if(!strlen($_POST['address']))
				{
					echo C1234HoleApiXML::Error('NO_ADDRESS');
					break;
				}
				$address    = C1234Hole::Address($_POST['address']);
				$subject_rf = $address['subject_rf'];
				$city       = $address['city'];
				$address    = $address['address'];
				// ворнинги, если надо
				if(!$subject_rf)
				{
					echo C1234HoleApiXML::Warning('CANNOT_REALISE_SUBJECTRF');
				}
				if(!$city)
				{
					echo C1234HoleApiXML::Warning('CANNOT_REALISE_CITY');
				}
				// координаты
				$longitude = false;
				$latitude  = false;
				if(isset($_POST['longitude']))
				{
					$longitude = (float)$_POST['longitude'];
				}
				if(isset($_POST['latitude']))
				{
					$latitude = (float)$_POST['latitude'];
				}
				if(!$latitude || !$longitude && isset($_POST['coordinates']))
				{
					list($latitude, $longitude) = explode(',', $_POST['coordinates']);
					$latitude  = (float)$latitude;
					$longitude = (float)$longitude;
				}
				elseif(!$latitude || !$longitude && isset($_POST['coordinatesr']))
				{
					list($longitude, $latitude) = explode(',', $_POST['coordinatesr']);
					$latitude  = (float)$latitude;
					$longitude = (float)$longitude;
				}
				if(!$latitude)
				{
					echo C1234HoleApiXML::Error('LATITUDE_NOT_SET');
					break;
				}
				if(!$longitude)
				{
					echo C1234HoleApiXML::Error('LONGITUDE_NOT_SET');
					break;
				}
				// типы дефектов
				if(in_array($_POST['type'], C1234HoleApi::$_deprecated_types))
				{
					echo C1234HoleApiXML::Error('DEPRECATED_TYPE');
					break;
				}
				if(!in_array($_POST['type'], C1234HoleApi::$_allowed_types))
				{
					echo C1234HoleApiXML::Error('INCORRECT_TYPE');
					break;
				}
				$files_count = 0;
				foreach($_FILES as $file)
				{
					if($file['error'] != 4)
					{
						if
						(
							$file['type']    != 'image/png' 
							&& $file['type'] != 'image/x-png' 
							&& $file['type'] != 'image/jpeg' 
							&& $file['type'] != 'image/pjpeg' 
							&& $file['type'] != 'image/gif' 
							&& $file['type']
						)
						{
							echo C1234HoleApiXML::Error('UNKNOWN_MIME_TYPE');
							break 2;
						}
						if($file['error'] == 1)
						{
							echo C1234HoleApiXML::Error('TOO_BIG_FILE');
							break 2;
						}
						if($file['error'] == 3)
						{
							echo C1234HoleApiXML::Error('PARTIALLY_UPLOADED_FILE');
							break 2;
						}
						if($file['error'] != 0)
						{
							echo C1234HoleApiXML::Error('CANNOT_UPLOAD_FILE');
							break 2;
						}
						$files_count++;
					}
				}
				if(!$files_count)
				{
					echo C1234HoleApiXML::Error('NO_FILES');
					break;
				}
				if($files_count > ini_get('max_file_uploads'))
				{
					echo C1234HoleApiXML::Error('TOO_MANY_FILES');
					break;
				}
				if($files_count > 10)
				{
					echo C1234HoleApiXML::Warning('FILES_DROPPED');
					$_FILES = array_slice($_FILES, 10);
				}
				// настройки по-умолчанию
				$arParams = array
				(
					'BIG_SIZEX'      => 1024,
					'BIG_SIZEY'      => 1024,
					'MEDIUM_SIZEX'   => 600,
					'MEDIUM_SIZEY'   => 450,
					'SMALL_SIZEX'    => 240,
					'SMALL_SIZEY'    => 160,
					'PREMODERATED'   => 0,
					'MIN_DELAY_TIME' => 60
				);
				// раздобудем настройки из компонента
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/index.php');
				preg_match('/(\'|\")PREMODERATION\1 => (\"|\')(Y|N|)\2/', $raw, $_match);
				if($_match[3] == 'Y')
				{
					$arParams['PREMODERATED'] = 0;
				}
				preg_match('/(\'|\")MIN_DELAY_TIME\1 => (\"|\')\d\2/', $raw, $_match);
				if(isset($_match[3]))
				{
					$arParams['MIN_DELAY_TIME'] = $_match[3] * 60;
				}
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/personal/add/index.php');
				foreach($arParams as $k => $v)
				{
					preg_match('/(\'|\")'.$k.'\1 => (\"|\')[\d]+\2/', $raw, $_match);
					if($_match[3])
					{
						$arParams[$k] = (int)$_match[3];
					}
				}
				$inserted_id = C1234Hole::Add
				(
					array
					(
						'USER_ID'       => $USER->GetID(),
						'LATITUDE'      => $latitude,
						'LONGITUDE'     => $longitude,
						'ADDRESS'       => $city.($address && $city ? ', ' : ' ').$address,
						'COMMENT1'      => $_POST['comment'],
						'COMMENT2'      => '',
						'TYPE'          => $_POST['type'],
						'FILES'         => $_FILES,
						'ADR_SUBJECTRF' => $subject_rf,
						'ADR_CITY'      => $city,
						'PREMODERATED'  => $arParams['PREMODERATED']
					),
					array
					(
						'big_sizex'      => $arParams['BIG_SIZEX'],
						'big_sizey'      => $arParams['BIG_SIZEY'],
						'medium_sizex'   => $arParams['MEDIUM_SIZEX'],
						'medium_sizey'   => $arParams['MEDIUM_SIZEY'],
						'small_sizex'    => $arParams['SMALL_SIZEX'],
						'small_sizey'    => $arParams['SMALL_SIZEY'],
						'min_delay_time' => $arParams['MIN_DELAY_TIME']
					),
					&$error
				);
				if(!$inserted_id)
				{
					echo "\t".'<error code="CANNOT_ADD_DEFECT">'.$error."</error>\n";
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				echo "\t".'<callresult result="1" inserteddefectid="'.$inserted_id.'">ok</callresult>'."\n";
				break;
			}
			case 'authorize':
			{
				// авторизация
				if($USER->IsAuthorized())
				{
					$USER->Logout();
				}
				$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
				if($auth_result === true)
				{
					echo C1234HoleApiXML::UserAuthParams();
				}
				else
				{
					echo C1234HoleApiXML::Error('WRONG_CREDENTIALS');
				}
				break;
			}
			case 'checkauth':
			{
				// проверка авторизованности
				$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
				if($auth_result === true)
				{
					echo "\t".'<checkauthresult result="1">ok</checkauthresult>'."\n";
				}
				else
				{
					echo "\t".'<checkauthresult result="0">fail</checkauthresult>'."\n";
				}
				break;
			}
			case 'delete':
			{
				// удаление ямы
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'fresh')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if(C1234Hole::Delete($hole_id))
				{
					echo "\t".'<callresult result="1">ok</callresult>'."\n";
				}
				else
				{
					echo "\t".'<error code="CANNOT_DELETE_DEFECT">'.$error.'</error>'."\n"
						."\t".'<callresult result="0">fail</callresult>'."\n";
				}
				break;
			}
			case 'exit':
			{
				// разлогиниться
				$USER->Logout();
				echo C1234HoleApiXML::ProcedureResult();
				break;
			}
			case 'geocode':
			{
				// предварительная авторизация
				if($_POST['passwordhash'])
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
				}
				else
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
				}
				if($auth_result !== true)
				{
					echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
					break;
				}
				if(!strlen($_POST['geocode']))
				{
					echo C1234HoleApiXML::Error('GEOCODE_EMPTY_REQUEST');
					break;
				}
				require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php');
				$c = curl_init('http://geocode-maps.yandex.ru/1.x/?format=xml&geocode='.urlencode($_POST['geocode']).'&key='.CIBlockPropertyMapYandex::_GetMapKey('yandex', $_SERVER['SERVER_NAME']));
				ob_start();
				curl_exec($c);
				$out = explode("\n", ob_get_clean());
				$cinfo = curl_getinfo($c);
				unset($out[0]);
				curl_close($c);
				if
				(
					$cinfo['http_code'] != 200
					|| !sizeof($out)
					|| substr($cinfo['content_type'], 0, 8) != 'text/xml'
					|| !$cinfo['size_download']
				)
				{
					echo C1234HoleApiXML::Error('GEOCODE_ERROR');
					break;
				}
				echo "\t".'<geocode>'."\n";
				foreach($out as $str)
				{
					echo "\t\t".str_replace('  ', "\t", $str)."\n";
				}
				echo "\t".'</geocode>'."\n";
				break;
			}
			case 'getgibddhead':
			{
				// узнать ФИО начальника ГИБДД
				// предварительная авторизация
				if($_POST['passwordhash'])
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
				}
				else
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
				}
				if($auth_result !== true)
				{
					echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
					break;
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'] || $hole['USER_ID'] != $USER->GetID())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					break;
				}
				if(!CModule::IncludeModule('iblock'))
				{
					echo C1234HoleApiXML::Error('INTERNAL');
					break;
				}
				if(!$hole['ADR_SUBJECTRF'])
				{
					echo C1234HoleApiXML::Warning('NO_SUBJECTRF_ID');
				}
				$res = CIBlockElement::GetList(array(), array('IBLOCK_CODE' => 'GIBDD_HEADS', 'PROPERTY_SUBJECT_ID' => $hole['ADR_SUBJECTRF']), array('PROPERTY_FIO', 'PROPERTY_POST', 'PROPERTY_POST_DATIVE', 'PROPERTY_FIO_DATIVE', 'PROPERTY_POST'));
				$arElement = $res->Fetch();
				$arElement['GIBDD'] = explode(' ', $arElement['PROPERTY_POST_VALUE']);
				$arElement['GIBDD'] = array_slice($arElement['GIBDD'], 1);
				if(ToUpper($arElement['GIBDD'][0]) == 'УПРАВЛЕНИЯ')
				{
					$arElement['GIBDD'][0] = 'УПРАВЛЕНИЕ';
				}
				$arElement['GIBDD'] = implode(' ', $arElement['GIBDD']);
				echo "\t".'<gibddhead subjectid="'.$hole['ADR_SUBJECTRF'].'">'."\n";
				echo "\t\t".'<nominative post="'.htmlspecialchars($arElement['PROPERTY_POST_VALUE']).'" gibdd="'.htmlspecialchars($arElement['GIBDD']).'">'.htmlspecialchars($arElement['PROPERTY_FIO_VALUE']).'</nominative>'."\n";
				echo "\t\t".'<dative post="'.htmlspecialchars($arElement['PROPERTY_POST_DATIVE_VALUE']).'">'.htmlspecialchars($arElement['PROPERTY_FIO_DATIVE_VALUE']).'</dative>'."\n";
				echo "\t".'</gibddhead>'."\n";
				break;
			}
			case 'getregions':
			{
				// список регионов
				echo C1234HoleApiXML::GetRegions();
				break;
			}
			case 'getupdmethods':
			{
				// получить список возможных методов обновления дефекта
				if($hole_id)
				{
					// предварительная авторизация
					if($_POST['passwordhash'])
					{
						$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
					$hole = C1234Hole::GetById($hole_id);
					if(!$hole['ID'])
					{
						echo C1234HoleApiXML::Error('NOT_FOUND');
						break;
					}
					if($hole['USER_ID'] != $USER->GetID())
					{
						echo C1234HoleApiXML::Error('NOT_FOUND');
						break;
					}
				}
				if($hole['STATE'] == 'fresh' || !$hole_id)
				{
?>
	<state id="fresh">
		<method name="update">
			<field>address</field>
			<field>latitude</field>
			<field>longitude</field>
			<field>coordinates</field>
			<field>coordinatesr</field>
			<field>comment</field>
			<field>type</field>
			<field>files</field>
			<field>deletefiles</field>
		</method>
		<method name="set_inprogress"></method>
		<method name="set_fixed">
			<field>files</field>
			<field>comment</field>
		</method>
	</state>
<?
				}
				if($hole['STATE'] == 'inprogress' || !$hole_id)
				{
?>
	<state id="inprogress">
		<method name="revoke"></method>
		<method name="set_replied">
			<field>files</field>
			<field>comment</field>
		</method>
		<method name="set_fixed">
			<field>files</field>
			<field>comment</field>
		</method>
	</state>
<?
				}
				if($hole['STATE'] == 'fixed' || !$hole_id)
				{
					echo "\t".'<state id="fixed">';
					if(!$hole && !sizeof($hole['pictures']['fixed']))
					{
						echo "\n\t\t".'<method name="set_inprogress"></method>'."\n";
					}
					echo "\t".'</state>'."\n";
				}
				if($hole['STATE'] == 'achtung' || !$hole_id)
				{
?>
	<state id="achtung">
		<method name="set_fixed">
			<field>files</field>
			<field>comment</field>
		</method>
		<method name="to_prosecutor"></method>
		<method name="set_replied">
			<field>files</field>
			<field>comment</field>
		</method>
	</state>
<?
				}
				if($hole['STATE'] == 'prosecutor' || !$hole_id)
				{
?>
	<state id="prosecutor">
		<method name="revoke_p"></method>
		<method name="set_fixed">
			<field>files</field>
			<field>comment</field>
		</method>
	</state>
<?
				}
				if($hole['STATE'] == 'gibddre' || !$hole_id)
				{
?>
	<state id="gibddre">
		<method name="set_fixed">
			<field>files</field>
			<field>comment</field>
		</method>
		<method name="set_replied">
			<field>files</field>
			<field>comment</field>
		</method>
	</state>
<?
				}
				break;
			}
			case 'hole-cart':
			{
				// карточка ямы
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/index.php');
				preg_match('/(\'|\")PREMODERATION\1[\s]*=>[\s]*(\"|\')(Y|N)\2/', $raw, $_match);
				$bPremoderation = $_match[3] == 'Y';
				$hole = C1234Hole::GetById($hole_id);
				if($hole && (!$bPremoderation || ($bPremoderation && $hole['PREMODERATED'])))
				{
					echo C1234HoleApiXML::Hole($hole, 0);
				}
				else
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
				}
				break;
			}
			case 'holes-list':
			{
				// список ям
				// получение настроек компонента списка ям
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/index.php');
				preg_match('/(\'|\")HOLES_PER_PAGE\1[\s]*=>[\s]*(\"|\')([\d]+)\2/', $raw, $_match);
				$default_limit = $_match[3];
				if(!$default_limit)
				{
					$default_limit = 30;
				}
				if($default_limit > 2000)
				{
					$default_limit = 2000;
				}
				preg_match('/(\'|\")PREMODERATION\1[\s]*=>[\s]*(\"|\')(Y|N)\2/', $raw, $_match);
				$bPremoderation = $_match[3] == 'Y';
				// сортировка
				$arSort = array('ID' => 'desc');
				// фильтр
				$arFilter = array();
				if(isset($_REQUEST['filter_rf_subject_id']))
				{
					$arFilter['ADR_SUBJECTRF'] = (int)$_REQUEST['filter_rf_subject_id'];
				}
				if($_REQUEST['filter_city'])
				{
					$arFilter['ADR_CITY'] = htmlspecialchars(str_replace('%', '', $_REQUEST['filter_city']));
				}
				if($_REQUEST['filter_type'])
				{
					$arFilter['TYPE'] = htmlspecialchars($_REQUEST['filter_type']);
				}
				if($_REQUEST['filter_status'])
				{
					$arFilter['STATE'] = htmlspecialchars($_REQUEST['filter_status']);
				}
				if($bPremoderation)
				{
					$arFilter['PREMODERATED'] = 1;
				}
				// параметры навигации
				$arNavParams = array();
				if($_REQUEST['limit'])
				{
					$arNavParams['limit'] = (int)$_REQUEST['limit'];
				}
				if(!$arNavParams['limit'])
				{
					$arNavParams['limit'] = $default_limit;
				}
				if($_REQUEST['offset'])
				{
					$arNavParams['offset'] = (int)$_REQUEST['offset'];
				}
				if(!$arNavParams['offset'])
				{
					$arNavParams['offset'] = (int)$_REQUEST['page'] * $default_limit;
				}
				if($_REQUEST['page'])
				{
					$arNavParams['limit']  = $default_limit;
					$arNavParams['offset'] = (int)$_REQUEST['page'] * $default_limit;
				}
				// выборка ям
				$_holes = C1234Hole::GetList($arSort, $arFilter, $arNavParams, &$pages_count);
				// вывод результатов
				$_users = array();
				{
?>
	<sort><? if(sizeof($arSort)) { echo "\n\t"; } foreach($arSort as $k => $v): ?>
	<item code="<?= $k ?>"><?= $v ?></item>
	<? endforeach; ?></sort>
	<filter><? if(sizeof($arFilter)) { echo "\n\t"; } foreach($arFilter as $k => $v): ?>
	<item code="<?= $k ?>"><?= $v ?></item>
	<? endforeach; ?></filter>
	<navigation><? if(sizeof($arNavParams)) { echo "\n\t"; } foreach($arNavParams as $k => $v): ?>
	<item code="<?= $k ?>"><?= $v ?></item>
	<? endforeach; ?></navigation>
	<defectslist><? if(sizeof($_holes)) { echo "\n\t"; } foreach($_holes as $hole):
	echo C1234HoleApiXML::Hole($hole, 1);
	endforeach; ?></defectslist>
<?
				}
				break;
			}
			case 'pdf':
			{
				// создание и выгрузка пдф
				// предварительная авторизация
				if($_POST['passwordhash'])
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
				}
				else
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
				}
				if($auth_result !== true)
				{
					ob_end_flush();
					echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
					break;
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'] || $hole['USER_ID'] != $USER->GetID())
				{
					ob_end_flush();
					echo C1234HoleApiXML::Error('NOT_FOUND');
					break;
				}
				$date3 = $hole['DATE_STATUS'];
				$date2 = $hole['STATE'] == 'gibddre' || $hole['STATE'] == 'achtung' ? $hole['DATE_SENT'] : time();
				if($hole['STATE'] == 'gibddre' && $_path[0][2] == 'pdf_prosecutor')
				{
					$state = 'prosecutor2';
				}
				elseif($hole['STATE'] == 'achtung' && $_path[0][2] == 'pdf_prosecutor')
				{
					$state = 'prosecutor2';
				}
				elseif(($hole['STATE'] == 'fresh' || $hole['STATE'] == 'inprogress') && $_path[0][2] == 'pdf_gibdd')
				{
					$state = $hole['TYPE'];
				}
				else
				{
					ob_end_flush();
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					break;
				}
				header_remove('Content-Type');
				$_images = array();
				foreach($hole['pictures']['original']['fresh'] as $src)
				{
					$_images[] = $_SERVER['DOCUMENT_ROOT'].$src;
				}
				ob_end_clean();
				$PDF = new pdf1234();
				$PDF->getpdf
				(
					$state,
					array
					(
						'chief'       => iconv('utf-8', 'windows-1251', $_POST['to']),
						'fio'         => iconv('utf-8', 'windows-1251', $_POST['from']),
						'address'     => iconv('utf-8', 'windows-1251', $_POST['postaddress']),
						'date1.day'   => date('d', $hole['DATE_CREATED']),
						'date1.month' => date('m', $hole['DATE_CREATED']),
						'date1.year'  => date('Y', $hole['DATE_CREATED']),
						'street'      => iconv('utf-8', 'windows-1251', $_POST['holeaddress']),
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
					),
					$_images
				);
				die();
				break;
			}
			case 'personal-hole-cart':
			{
				// карточка своей ямы
				// предварительная авторизация
				if($_POST['passwordhash'])
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
				}
				else
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
				}
				if($auth_result !== true)
				{
					echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
					break;
				}
				$hole = C1234Hole::GetById((int)$hole_id);
				if($hole['USER_ID'] != $USER->GetID())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
				}
				else
				{
					echo C1234HoleApiXML::Hole($hole, 0);
				}
				break;
			}
			case 'personal-holes-list':
			{
				// список своих ям
				// предварительная авторизация
				if($_POST['passwordhash'])
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
				}
				else
				{
					$auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
				}
				if($auth_result !== true)
				{
					echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
					break;
				}
				// получение настроек компонента списка ям
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/index.php');
				preg_match('/(\'|\")HOLES_PER_PAGE\1[\s]*=>[\s]*(\"|\')([\d]+)\2/', $raw, $_match);
				$default_limit = $_match[3];
				if(!$default_limit)
				{
					$default_limit = 30;
				}
				if($default_limit > 2000)
				{
					$default_limit = 2000;
				}
				// сортировка
				$arSort = array('ID' => 'desc');
				// фильтр
				$arFilter = array('USER_ID' => $USER->GetID());
				if(isset($_REQUEST['filter_rf_subject_id']))
				{
					$arFilter['ADR_SUBJECTRF'] = (int)$_REQUEST['filter_rf_subject_id'];
				}
				if($_REQUEST['filter_city'])
				{
					$arFilter['ADR_CITY'] = htmlspecialchars(str_replace('%', '', $_REQUEST['filter_city']));
				}
				if($_REQUEST['filter_type'])
				{
					$arFilter['TYPE'] = htmlspecialchars($_REQUEST['filter_type']);
				}
				if($_REQUEST['filter_status'])
				{
					$arFilter['STATE'] = htmlspecialchars($_REQUEST['filter_status']);
				}
				// параметры навигации
				$arNavParams = array();
				if($_REQUEST['limit'])
				{
					$arNavParams['limit'] = (int)$_REQUEST['limit'];
				}
				if(!$arNavParams['limit'])
				{
					$arNavParams['limit'] = $default_limit;
				}
				if($_REQUEST['offset'])
				{
					$arNavParams['offset'] = (int)$_REQUEST['offset'];
				}
				if(!$arNavParams['offset'])
				{
					$arNavParams['offset'] = (int)$_REQUEST['page'] * $default_limit;
				}
				if($_REQUEST['page'])
				{
					$arNavParams['limit']  = $default_limit;
					$arNavParams['offset'] = (int)$_REQUEST['page'] * $default_limit;
				}
				// выборка ям
				$_holes = C1234Hole::GetList($arSort, $arFilter, $arNavParams, &$pages_count);
				// вывод результатов
				$_users = array();
				{
?>
	<sort><? if(sizeof($arSort)) { echo "\n\t"; } foreach($arSort as $k => $v): ?>
	<item code="<?= $k ?>"><?= $v ?></item>
	<? endforeach; ?></sort>
	<filter><? if(sizeof($arFilter)) { echo "\n\t"; } foreach($arFilter as $k => $v): ?>
	<item code="<?= $k ?>"><?= $v ?></item>
	<? endforeach; ?></filter>
	<navigation><? if(sizeof($arNavParams)) { echo "\n\t"; } foreach($arNavParams as $k => $v): ?>
	<item code="<?= $k ?>"><?= $v ?></item>
	<? endforeach; ?></navigation>
	<defectslist><? if(sizeof($_holes)) { echo "\n\t"; } foreach($_holes as $hole):
	echo C1234HoleApiXML::Hole($hole, 1);
	endforeach; ?></defectslist>
<?
				}
				break;
			}
			case 'update-common':
			{
				// обычное обновление ямы
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'fresh')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_fields = array('COMMENT1' => trim($_POST['comment']));
				$_params = array
				(
					'big_sizex'      => 1024,
					'big_sizey'      => 1024,
					'medium_sizex'   => 600,
					'medium_sizey'   => 450,
					'small_sizex'    => 240,
					'small_sizey'    => 160
				);
				// проверка адреса
				$_POST['address'] = trim($_POST['address']);
				if(strpos($_POST['address'], $hole['ADDRESS']) === 0)
				{
					// уродливая подпорка
					// если адрес совпадает с тем, или больше чем, что уже есть,
					// меняем только адрес
					$_fields['ADR_SUBJECTRF'] = $hole['ADR_SUBJECTRF'];
					$_fields['ADR_CITY']      = $hole['ADR_CITY'];
					$_fields['ADDRESS']       = $_POST['address'];
				}
				elseif(strlen($_POST['address']))
				{
					$address                  = C1234Hole::Address($_POST['address']);
					$_fields['ADR_SUBJECTRF'] = $address['subject_rf'];
					$_fields['ADR_CITY']      = $address['city'];
					$_fields['ADDRESS']       = $address['city'].(strlen($address['address']) && strlen($address['city'])? ', ' : '').$address['address'];
					// ворнинги, если надо
					if(!$_fields['ADR_SUBJECTRF'])
					{
						echo C1234HoleApiXML::Warning('CANNOT_REALISE_SUBJECTRF');
					}
					if(!$_fields['ADR_CITY'])
					{
						echo C1234HoleApiXML::Warning('CANNOT_REALISE_CITY');
					}
				}
				// координаты
				$longitude = false;
				$latitude  = false;
				if(isset($_POST['longitude']))
				{
					$longitude = (float)$_POST['longitude'];
				}
				if(isset($_POST['latitude']))
				{
					$latitude = (float)$_POST['latitude'];
				}
				if(!$latitude || !$longitude && isset($_POST['coordinates']))
				{
					list($latitude, $longitude) = explode(',', $_POST['coordinates']);
					$latitude  = (float)$latitude;
					$longitude = (float)$longitude;
				}
				elseif(!$latitude || !$longitude && isset($_POST['coordinatesr']))
				{
					list($longitude, $latitude) = explode(',', $_POST['coordinatesr']);
					$latitude  = (float)$latitude;
					$longitude = (float)$longitude;
				}
				if($latitude)
				{
					$_fields['LATITUDE'] = $latitude;
				}
				if($longitude)
				{
					$_fields['LONGITUDE'] = $longitude;
				}
				// типы дефектов
				if(in_array($_POST['type'], C1234HoleApi::$_deprecated_types))
				{
					echo C1234HoleApiXML::Error('DEPRECATED_TYPE');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if(!in_array($_POST['type'], C1234HoleApi::$_allowed_types))
				{
					echo C1234HoleApiXML::Error('INCORRECT_TYPE');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_fields['TYPE'] = $_POST['type'];
				// разберёмся с файлами
				$files_count = 0;
				foreach($_FILES as $file)
				{
					if($file['error'] != 4)
					{
						if
						(
							$file['type']    != 'image/png' 
							&& $file['type'] != 'image/x-png' 
							&& $file['type'] != 'image/jpeg' 
							&& $file['type'] != 'image/pjpeg' 
							&& $file['type'] != 'image/gif' 
							&& $file['type']
						)
						{
							echo C1234HoleApiXML::Error('UNKNOWN_MIME_TYPE');
							break 2;
						}
						if($file['error'] == 1)
						{
							echo C1234HoleApiXML::Error('TOO_BIG_FILE');
							break 2;
						}
						if($file['error'] == 3)
						{
							echo C1234HoleApiXML::Error('PARTIALLY_UPLOADED_FILE');
							break 2;
						}
						if($file['error'] != 0)
						{
							echo C1234HoleApiXML::Error('CANNOT_UPLOAD_FILE');
							break 2;
						}
						$files_count++;
					}
				}
				if($files_count > ini_get('max_file_uploads'))
				{
					echo C1234HoleApiXML::Error('TOO_MANY_FILES');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				// разберёмся с удаляемыми файлами
				if(!is_array($_POST['deletefiles']))
				{
					$_fields['DELETEFILES'] = explode(',', $_POST['deletefiles']);
				}
				foreach($_fields['DELETEFILES'] as &$deletefile)
				{
					$deletefile = trim($deletefile);
				}
				if
				(
					sizeof($hole['pictures']['original']['fresh'])
					+ sizeof($hole['pictures']['original']['gibddre'])
					+ sizeof($hole['pictures']['original']['fixed'])
					- sizeof($_fields['DELETEFILES'])
					+ $files_count > 100
				)
				{
					echo C1234HoleApiXML::Warning('FILES_LIMIT_REACHED');
					echo C1234HoleApiXML::Warning('FILES_DROPPED');
					$slice = 100 - sizeof($hole['pictures']['original']['fresh'])
						- sizeof($hole['pictures']['original']['gibddre'])
						- sizeof($hole['pictures']['original']['fixed'])
						+ sizeof($_fields['DELETEFILES']);
					if($slice > 0)
					{
						$_fields['FILES'] = array_slice($_FILES, max(10, $slice));
					}
				}
				elseif($files_count > 10)
				{
					echo C1234HoleApiXML::Warning('FILES_DROPPED');
					$_fields['FILES'] = array_slice($_FILES, 10);
				}
				else
				{
					$_fields['FILES'] = $_FILES;
				}
				// раздобудем настройки из компонента
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/personal/add/index.php');
				foreach($arParams as $k => $v)
				{
					preg_match('/(\'|\")'.$k.'\1 => (\"|\')[\d]+\2/', $raw, $_match);
					if($_match[3])
					{
						$_params[ToLower($k)] = (int)$_match[3];
					}
				}
				// теперь можно и обновить
				$mode = 'update';
				break;
			}
			case 'update-revoke':
			{
				// отозвать заявление из ГИБДД
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'inprogress')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_params = array();
				$_fields = array
				(
					'DATE_STATUS' => time(),
					'STATE'       => 'fresh'
				);
				$mode = 'update';
				break;
			}
			case 'update-revokep':
			{
				// отозвать заявление из прокуратуры
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'prosecutor')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_params = array();
				$_fields = array
				(
					'DATE_STATUS'          => time(),
					'DATE_SENT_PROSECUTOR' => 0,
					'STATE'                => 'achtung'
				);
				$mode = 'update';
				break;
			}
			case 'update-setfixed':
			{
				// пометка ямы как исправленной
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] == 'fixed')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_fields = array
				(
					'STATE'       => 'fixed',
					'DATE_STATUS' => time(),
					'COMMENT2'    => trim($_POST['comment'])
				);
				$_params = array
				(
					'big_sizex'      => 1024,
					'big_sizey'      => 1024,
					'medium_sizex'   => 600,
					'medium_sizey'   => 450,
					'small_sizex'    => 240,
					'small_sizey'    => 160
				);
				// разберёмся с файлами
				$files_count = 0;
				foreach($_FILES as $file)
				{
					if($file['error'] != 4)
					{
						if
						(
							$file['type']    != 'image/png' 
							&& $file['type'] != 'image/x-png' 
							&& $file['type'] != 'image/jpeg' 
							&& $file['type'] != 'image/pjpeg' 
							&& $file['type'] != 'image/gif' 
							&& $file['type']
						)
						{
							echo C1234HoleApiXML::Error('UNKNOWN_MIME_TYPE');
							break 2;
						}
						if($file['error'] == 1)
						{
							echo C1234HoleApiXML::Error('TOO_BIG_FILE');
							break 2;
						}
						if($file['error'] == 3)
						{
							echo C1234HoleApiXML::Error('PARTIALLY_UPLOADED_FILE');
							break 2;
						}
						if($file['error'] != 0)
						{
							echo C1234HoleApiXML::Error('CANNOT_UPLOAD_FILE');
							break 2;
						}
						$files_count++;
					}
				}
				if($files_count > ini_get('max_file_uploads'))
				{
					echo C1234HoleApiXML::Error('TOO_MANY_FILES');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($files_count > 10)
				{
					echo C1234HoleApiXML::Warning('FILES_DROPPED');
					$_fields['FILES'] = array_slice($_FILES, 10);
				}
				else
				{
					$_fields['FILES'] = $_FILES;
				}
				// раздобудем настройки из компонента
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/personal/add/index.php');
				foreach($arParams as $k => $v)
				{
					preg_match('/(\'|\")'.$k.'\1 => (\"|\')[\d]+\2/', $raw, $_match);
					if($_match[3])
					{
						$_params[ToLower($k)] = (int)$_match[3];
					}
				}
				// теперь можно и обновить
				$mode = 'update';
				break;
			}
			case 'update-setinprogress':
			{
				// поставить яме статус "в процессе"
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'fresh' && !($hole['STATE'] == 'fixed' && !sizeof($hole['pictures']['original']['fixed'])))
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_params = array();
				$_fields = array
				(
					'DATE_STATUS' => time(),
					'STATE'       => 'inprogress'
				);
				if($hole['STATE'] == 'fresh')
				{
					$_fields['DATE_SENT'] = time();
				}
				else
				{
					if($hole['DATE_SENT'] < time() - 37 * 86400)
					{
						$_fields['STATE'] = 'achtung';
					}
					if($hole['GIBDD_REPLY_RECEIVED'])
					{
						$_fields['STATE'] = 'gibddre';
					}
					if($hole['DATE_SENT_PROSECUTOR'])
					{
						$_fields['STATE'] = 'prosecutor';
					}
					if(!$hole['DATE_SENT'])
					{
						$_fields['STATE'] = 'fresh';
					}
				}
				$mode = 'update';
				break;
			}
			case 'update-setreplied':
			{
				// поставить яму в статус "получен ответ из гибдд"
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'inprogress' && $hole['STATE'] != 'gibddre' && $hole['STATE'] != 'achtung')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_fields = array
				(
					'STATE'                => 'gibddre',
					'DATE_STATUS'          => time(),
					'GIBDD_REPLY_RECEIVED' => 1,
					'COMMENT_GIBDD_REPLY'  => trim($_POST['comment'])
				);
				$_params = array
				(
					'big_sizex'      => 1024,
					'big_sizey'      => 1024,
					'medium_sizex'   => 600,
					'medium_sizey'   => 450,
					'small_sizex'    => 240,
					'small_sizey'    => 160
				);
				// разберёмся с файлами
				$files_count = 0;
				foreach($_FILES as $file)
				{
					if($file['error'] != 4)
					{
						if
						(
							$file['type']    != 'image/png' 
							&& $file['type'] != 'image/x-png' 
							&& $file['type'] != 'image/jpeg' 
							&& $file['type'] != 'image/pjpeg' 
							&& $file['type'] != 'image/gif' 
							&& $file['type']
						)
						{
							echo C1234HoleApiXML::Error('UNKNOWN_MIME_TYPE');
							break 2;
						}
						if($file['error'] == 1)
						{
							echo C1234HoleApiXML::Error('TOO_BIG_FILE');
							break 2;
						}
						if($file['error'] == 3)
						{
							echo C1234HoleApiXML::Error('PARTIALLY_UPLOADED_FILE');
							break 2;
						}
						if($file['error'] != 0)
						{
							echo C1234HoleApiXML::Error('CANNOT_UPLOAD_FILE');
							break 2;
						}
						$files_count++;
					}
				}
				if($files_count > ini_get('max_file_uploads'))
				{
					echo C1234HoleApiXML::Error('TOO_MANY_FILES');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($files_count > 10)
				{
					echo C1234HoleApiXML::Warning('FILES_DROPPED');
					$_fields['FILES'] = array_slice($_FILES, 10);
				}
				else
				{
					$_fields['FILES'] = $_FILES;
				}
				// раздобудем настройки из компонента
				$raw = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/personal/add/index.php');
				foreach($arParams as $k => $v)
				{
					preg_match('/(\'|\")'.$k.'\1 => (\"|\')[\d]+\2/', $raw, $_match);
					if($_match[3])
					{
						$_params[ToLower($k)] = (int)$_match[3];
					}
				}
				// теперь можно и обновить
				$mode = 'update';
				break;
			}
			case 'update-toprosecutor':
			{
				// поменять статус на "жалоба в прокуратуру подана"
				// предварительная авторизация
				if(!$USER->GetID())
				{
					if($_POST['passwordhash'])
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['passwordhash'], 'N', 'N');
					}
					else
					{
						$auth_result = $auth_result = $USER->Login($_POST['login'], $_POST['password'], 'N', 'Y');
					}
					if($auth_result !== true)
					{
						echo C1234HoleApiXML::Error('AUTHORIZATION_REQUIRED');
						break;
					}
				}
				$hole = C1234Hole::GetById($hole_id);
				if(!$hole['ID'])
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['USER_ID'] != $USER->GetID() && !$USER->IsAdmin())
				{
					echo C1234HoleApiXML::Error('NOT_FOUND');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				if($hole['STATE'] != 'achtung')
				{
					echo C1234HoleApiXML::Error('UNAPPROPRIATE_METHOD');
					echo "\t".'<callresult result="0">fail</callresult>'."\n";
					break;
				}
				$_params = array();
				$_fields = array
				(
					'DATE_STATUS'          => time(),
					'DATE_SENT_PROSECUTOR' => time(),
					'STATE'                => 'prosecutor'
				);
				$mode = 'update';
				break;
			}
			case 'uplparams':
			{
				// предельные параметры загружаемых файлов
				echo "\t".'<maxpostsize>'.ini_get('post_max_size').'</maxpostsize>'."\n";
				echo "\t".'<maxfilesize>'.ini_get('upload_max_filesize').'</maxfilesize>'."\n";
				echo "\t".'<maxfilescount>10</maxfilescount>'."\n";
				break;
			}
			default:
			{
				echo C1234HoleApiXML::Error('NOT_IMPLEMENTED');
				break;
			}
		}
		if($mode == 'update')
		{
			// единая для всех режимов измнения ямы процедура собственно изменения
			if(C1234Hole::Update($hole_id, $_fields, $_params, &$error))
			{
				echo "\t".'<callresult result="1">ok</callresult>'."\n";
			}
			else
			{
				echo "\t".'<error code="CANNOT_UPDATE_DEFECT">'.$error.'</error>'."\n"
					."\t".'<callresult result="0">fail</callresult>'."\n";
			}
		}
		
		// стандартный конец ответа
		echo "</st1234reply>";
		
		return;
	}
}

?>