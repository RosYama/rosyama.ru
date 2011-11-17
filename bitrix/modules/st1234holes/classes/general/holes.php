<?php

/**
 * Интерфейс для работы с ямами.
 */

if(!CModule::IncludeModule('greensight_utils'))
{
	die(':(');
}

IncludeModuleLangFile(__FILE__);

class C1234Hole
{
	/**
	 * Добавление ямы.
	 * @param  array  $_fields значения полей ямы
	 * @param  array  $_params всякие параметры
	 * @param  string $error тут содержится текст ошибки
	 * @return id ямы или false
	 */
	public static function Add($_fields, $_params, &$error)
	{
		global $DB;
		$_fields['USER_ID'] = (int)$_fields['USER_ID'];
		foreach($_fields['FILES'] as $_file)
		{
			if(!$_file['error'])
			{
				$image = C1234Hole::imagecreatefromfile($_file['tmp_name'], &$_image_info);
				if(!$image)
				{
					$error = GetMessage('GREENSIGHT_ERROR_UNSUPPORTED_IMAGE_TYPE');
					return false;
				}
				imagedestroy($image);
			}
		}
		
		// определение, не рано ли постить яму
		$_params['min_delay_time'] = (int)$_params['min_delay_time'];
		if($_params['min_delay_time'])
		{
			$res = mysql_query("select `ID` from `b_holes` where `USER_ID` = '".$_fields['USER_ID']."' and `DATE_CREATED` > unix_timestamp() - ".$_params['min_delay_time']);
			$ar  = mysql_fetch_array($res);
			if($ar)
			{
				$error = GetMessage('GREENSIGHT_ERROR_RAPIDFIRE');
				return false;
			}
		}
		
		$DB->StartTransaction();
		$str_sql = "insert into `b_holes`
			(`USER_ID`, `LATITUDE`, `LONGITUDE`,
				`ADDRESS`, `STATE`, `DATE_CREATED`,
				`COMMENT1`, `COMMENT2`, `TYPE`,
				`ADR_SUBJECTRF`, `ADR_CITY`, `PREMODERATED`) values (
					'".$_fields['USER_ID']."',
					'".(float)$_fields['LATITUDE']."',
					'".(float)$_fields['LONGITUDE']."',
					'".mysql_escape_string(trim($_fields['ADDRESS']))."',
					'fresh',
					unix_timestamp(),
					'".mysql_escape_string(trim($_fields['COMMENT1']))."',
					'".mysql_escape_string(trim($_fields['COMMENT2']))."',
					'".mysql_escape_string($_fields['TYPE'])."',
					'".(int)($_fields['ADR_SUBJECTRF'])."',
					'".mysql_escape_string(trim($_fields['ADR_CITY']))."',
					'".($_fields['PREMODERATED'] ? '1' : '0')."')";
		if(!$DB->Query($str_sql))
		{
			$DB->Rollback();
			$error = GetMessage('GREENSIGHT_ERROR_DATABASE');
			return false;
		}
		$id = $DB->LastID();
		if(!$id)
		{
			$DB->Rollback();
			$error = GetMessage('GREENSIGHT_ERROR_DATABASE');
			return false;
		}
		if(!mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id))
		{
			$DB->Rollback();
			$error = GetMessage('GREENSIGHT_ERROR_CANNOT_CREATE_DIR');
			return false;
		}
		if(!mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/medium/'.$id))
		{
			$DB->Rollback();
			unlink($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id);
			$error = GetMessage('GREENSIGHT_ERROR_CANNOT_CREATE_DIR');
			return false;
		}
		if(!mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/small/'.$id))
		{
			$DB->Rollback();
			unlink($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id);
			unlink($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/medium/'.$id);
			$error = GetMessage('GREENSIGHT_ERROR_CANNOT_CREATE_DIR');
			return false;
		}
		$file_counter = 0;
		if(!$_params['big_sizex'])
		{
			$_params['big_sizex'] = 1024;
		}
		if(!$_params['big_sizey'])
		{
			$_params['big_sizey'] = 1024;
		}
		foreach($_fields['FILES'] as $_file)
		{
			if(!$_file['error'])
			{
				$image = C1234Hole::imagecreatefromfile($_file['tmp_name'], &$_image_info);
				if(!$image)
				{
					$DB->Rollback();
					$error = GetMessage('GREENSIGHT_ERROR_UNSUPPORTED_IMAGE_TYPE');
					return false;
				}
				$aspect = max($_image_info[0] / $_params['big_sizex'], $_image_info[1] / $_params['big_sizey']);
				if($aspect > 1)
				{
					$new_x    = floor($_image_info[0] / $aspect);
					$new_y    = floor($_image_info[1] / $aspect);
					$newimage = imagecreatetruecolor($new_x, $new_y);
					imagecopyresampled($newimage, $image, 0, 0, 0, 0, $new_x, $new_y, $_image_info[0], $_image_info[1]);
					imagejpeg($newimage, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id.'/'.$file_counter.'.jpg');
				}
				else
				{
					imagejpeg($image, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id.'/'.$file_counter.'.jpg');
				}
				$aspect   = max($_image_info[0] / $_params['medium_sizex'], $_image_info[1] / $_params['medium_sizey']);
				$new_x    = floor($_image_info[0] / $aspect);
				$new_y    = floor($_image_info[1] / $aspect);
				$newimage = imagecreatetruecolor($new_x, $new_y);
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $new_x, $new_y, $_image_info[0], $_image_info[1]);
				imagejpeg($newimage, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/medium/'.$id.'/'.$file_counter.'.jpg');
				imagedestroy($newimage);
				$aspect   = min($_image_info[0] / $_params['small_sizex'], $_image_info[1] / $_params['small_sizey']);
				$newimage = imagecreatetruecolor($_params['small_sizex'], $_params['small_sizey']);
				imagecopyresampled
				(
					$newimage,
					$image,
					0,
					0,
					$_image_info[0] > $_image_info[1] ? floor(($_image_info[0] - $aspect * $_params['small_sizex']) / 2) : 0,
					$_image_info[0] < $_image_info[1] ? floor(($_image_info[1] - $aspect * $_params['small_sizey']) / 2) : 0,
					$_params['small_sizex'],
					$_params['small_sizey'],
					ceil($aspect * $_params['small_sizex']),
					ceil($aspect * $_params['small_sizey'])
				);
				imagejpeg($newimage, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/small/'.$id.'/'.$file_counter.'.jpg');
				imagedestroy($newimage);
				imagedestroy($image);
				$file_counter++;
			}
		}
		$DB->Commit();
		CGreensightDBQueryCache::ClearAllCache();
		return $id;
	}
	
	/**
	 * Вычленить из адресной строки субъект РФ, город и прочее.
	 * @param  string $address
	 * @return array
	 */
	public static function Address($address)
	{
		// достанем из адреса субъект РФ и город
		$_address = explode(',', $address);
		do
		{
			$subject_rf = '';
			$city       = '';
			if
			(
				$_address[0] == 'Россия'
				|| $_address[0] == 'Российская Федерация'
				|| $_address[0] == 'Russia'
				|| $_address[0] == 'Russian Federation')
			{
				$_address = array_slice($_address, 1);
			}
			$_address[0] = trim($_address[0]);
			// города - субъекты РФ
			if($_address[0] == 'Москва' || $_address[0] == 'Санкт-Петербург')
			{
				$subject_rf  = CGreensightRFSubject::GetID($_address[0]);
				$city        = $_address[0];
				$_address[0] = '';
				$_address[1] = trim($_address[1]);
				// города-спутники
				if
				(
					$_address[1] == 'Зеленоград'
					|| strpos($_address[1], 'поселок') !== false
					|| strpos($_address[1], 'город')   !== false
					|| strpos($_address[1], 'деревня') !== false
					|| strpos($_address[1], 'село')    !== false
				)
				{
					$city        = $_address[1];
					$_address[1] = '';
					$address     = implode(', ', $_address);
					break;
				}
				$address = implode(', ', $_address);
				break;
			}
			// неизвестно что
			if(!$_address[1])
			{
				$subject_rf = '';
				$city       = '';
				$address    = implode(', ', $_address);
				break;
			}
			$subject_rf = trim($_address[0]);
			$subject_rf = CGreensightRFSubject::GetID($subject_rf);
			if(!$subject_rf)
			{
				// регион не определился
				$subject_rf = '';
				$city       = '';
				$address    = implode(', ', $_address);
				break;
			}
			$_address[0] = '';
			// район или город
			if(strpos($_address[1], 'район') !== false)
			{
				$_address[1] = '';
				// точка попала в город
				if($_address[2])
				{
					$city        = trim($_address[2]);
					$_address[2] = '';
					$address     = implode(', ', $_address);
				}
				// точка попала фиг знает куда
				else
				{
					$city       = '';
					$address    = implode(', ', $_address);
					break;
				}
			}
			else
			{
				$city        = trim($_address[1]);
				$_address[1] = '';
				$address     = implode(', ', $_address);
			}
		}
		while(false);
		$address = trim($address, ' ,');
		$address = str_replace('  ', ' ', $address);
		return array
		(
			'subject_rf' => $subject_rf,
			'city'       => $city,
			'address'    => $address
		);
	}
	
	/**
	 * Удаление ямы.
	 * @param int $id номер удаляемой ямы
	 * @return bool success
	 */
	public static function Delete($id)
	{
		global $DB;
		$id = (int)$id;
		if(!$id)
		{
			return false;
		}
		foreach(array('original', 'medium', 'small') as $dirname)
		{
			$dirname = $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/'.$dirname.'/'.$id;
			$dir = opendir($dirname);
			while($f = readdir($dir))
			{
				if($f != '.' && $f != '..')
				{
					unlink($dirname.'/'.$f);
				}
			}
			closedir($dir);
			rmdir($dirname);
		}
		CGreensightDBQueryCache::ClearAllCache();
		return $DB->Query("delete from `b_holes` where `ID` = '".$id."'");
	}
	
	/**
	 * Выбрать одну яму по номеру.
	 * @param int $id номер ямы
	 * @return array или false
	 */
	public static function GetById($id)
	{
		$result = C1234Hole::GetList(array(), array('ID' => (int)$id));
		if(sizeof($result))
		{
			return $result[(int)$id];
		}
		return false;
	}
	
	/**
	 * Узнать количество ям всего и количество ям исправленных.
	 * @return array('facts' => facts, 'fixed' => fixed, 'gibddre' => gibddre, ...)
	 */
	public static function GetCounts()
	{
		global $DB;
		$_result['facts'] = CGreensightDBQueryCache::QueryCached("select count(*) from `b_holes` where `PREMODERATED`");
		$_result['facts'] = array_values($_result['facts'][0]);
		$_result['facts'] = $_result['facts'][0];
		$_result['fixed'] = CGreensightDBQueryCache::QueryCached("select count(*) from `b_holes` where `STATE` = 'fixed' and `PREMODERATED`");
		$_result['fixed'] = array_values($_result['fixed'][0]);
		$_result['fixed'] = $_result['fixed'][0];
		$_result['gibddre'] = CGreensightDBQueryCache::QueryCached("select count(*) from `b_holes` where `STATE` = 'gibddre' and `PREMODERATED`");
		$_result['gibddre'] = array_values($_result['gibddre'][0]);
		$_result['gibddre'] = $_result['gibddre'][0];
		$_result['inprogress'] = CGreensightDBQueryCache::QueryCached("select count(*) from `b_holes` where `STATE` = 'inprogress' and `PREMODERATED`");
		$_result['inprogress'] = array_values($_result['inprogress'][0]);
		$_result['inprogress'] = $_result['inprogress'][0];
		return $_result;
	}
	
	/**
	 * Выбрать список ям в соответствии с указанным фильтром, упорядоченные указанным образом.
	 * @param array $sort         порядок сортировки ((|<|<=|>|>=|!|!=)имя-поля => (asc|desc))
	 * @param array $filter       фильтрация (имя-поля => значение)
	 * @param array $navparams    опции навигации (лимит и оффсет)
	 * @param int   &$pages_count возвращается число страниц
	 * @return array
	 */
	public static function GetList($sort = array(), $filter = array(), $navparams = array(), &$pages_count)
	{
		$sql_str = "select holes.*, U.`LOGIN` from `b_holes` holes
			join `b_user` U on (U.`ID` = holes.`USER_ID`".($filter['USER_LOGIN'] ? " and U.`LOGIN` = '".mysql_escape_string($filter['USER_LOGIN'])."'" : '').") where 1=1";
		$count_str = "select count(`id`) as C from `b_holes` where 1=1";
		
		// обработка фильтра
		foreach($filter as $field => &$value)
		{
			$operand = is_array($value) ? 'in' : '=';
			if($field == 'ADDRESS' || $field == 'ADR_CITY')
			{
				$operand = ' like ';
			}
			switch($field[0])
			{
				case '<':
				{
					$operand = '<';
					$field   = substr($field, 1);
					break;
				}
				case '>':
				{
					$operand = '>';
					$field   = substr($field, 1);
					break;
				}
				case '!':
				{
					$operand = is_array($value) ? 'not in' : '!=';
					$field   = substr($field, 1);
					break;
				}
				default: break;
			}
			switch($field[0])
			{
				case '=':
				{
					if($operand == '<' || $operand == '>')
					{
						$operand .= '=';
					}
					$field = substr($field, 1);
				}
				default: break;
			}
			if($value != false || $value === 0)
			{
				if(is_array($value))
				{
					foreach($value as &$v)
					{
						$v = mysql_escape_string($v);
					}
					$value = "('".implode("', '", $value)."')";
					$sql_str   .= " and holes.`".mysql_escape_string($field)."` ".$operand." ".$value;
					$count_str .= " and `".mysql_escape_string($field)."` ".$operand." ".$value;
				}
				elseif($operand == ' like ' && $field == 'ADDRESS')
				{
					$value = "'%".mysql_escape_string($value)."%'";
					$sql_str   .= " and holes.`".mysql_escape_string($field)."` ".$operand." ".$value;
					$count_str .= " and `".mysql_escape_string($field)."` ".$operand." ".$value;
				}
				// возможно, имеет смысл сделать псевдооператор likeb, который
				// тут по условию менять на like, чтоб не сравнивать с названием
				// каждого поля
				elseif($operand == ' like ' && $field == 'ADR_CITY')
				{
					$value = "'".mysql_escape_string($value)."%'";
					$sql_str   .= " and ltrim(holes.`".mysql_escape_string($field)."`) ".$operand." ".$value;
					$count_str .= " and ltrim(`".mysql_escape_string($field)."`) ".$operand." ".$value;
				}
				elseif($field == 'USER_LOGIN')
				{
					$value = "'".mysql_escape_string($value)."'";
					$sql_str   .= " and U.`LOGIN` ".$operand." ".$value;
					//$count_str .= " and `LOGIN` ".$operand." ".$value;
				}
				else
				{
					$value = "'".mysql_escape_string($value)."'";
					$sql_str   .= " and holes.`".mysql_escape_string($field)."` ".$operand." ".$value;
					$count_str .= " and `".mysql_escape_string($field)."` ".$operand." ".$value;
				}
				
			}
		}
		
		// обработка сортировки
		if(sizeof($sort))
		{
			$sql_str .= ' order by';
			foreach($sort as $field => $order)
			{
				if(strlen($field))
				{
					$sql_str .= ($field == 'LOGIN' ? ' U' : ' holes').".`".mysql_escape_string($field)."` ".(ToUpper($order) == 'ASC' ? 'asc' : 'desc').', ';
				}
			}
			$sql_str = substr($sql_str, 0, strlen($sql_str) - 2);
		}
		
		// обработка параметров навигации
		if($navparams['limit'] || $navparams['offset'])
		{
			$sql_str .= ' limit '.($navparams['offset'] ? (int)$navparams['offset'] : 0).', '.($navparams['limit'] ? (int)$navparams['limit'] : 10000);
		}
		
		// собственно запрос к бд и обработка результата
		global $DB;
		//print_r($filter);
		//echo $sql_str;
		$res = CGreensightDBQueryCache::QueryCached($sql_str);
		//$res = $DB->Query($sql_str);
		$_result = array();
		//while($ar = $res->Fetch())
		foreach($res as &$ar)
		{
			$ar['~DATE_CREATED'] = date($DB->DateFormatToPHP(CSite::GetDateFormat('SHORT')), $ar['DATE_CREATED']);
			if($ar['DATE_STATUS'])
			{
				$ar['~DATE_STATUS'] = date($DB->DateFormatToPHP(CSite::GetDateFormat('SHORT')), $ar['DATE_STATUS']);
			}
			if($ar['DATE_SENT'])
			{
				$ar['~DATE_SENT'] = date($DB->DateFormatToPHP(CSite::GetDateFormat('SHORT')), $ar['DATE_SENT']);
			}
			$ar['~COORDINATES'] = $ar['LATITUDE'].','.$ar['LONGITUDE'];
			$ar['~COORDINATES_R'] = $ar['LONGITUDE'].','.$ar['LATITUDE']; // в Битриксе координаты зачастую перепутаны
			$_result[$ar['ID']] = $ar;
		}
		$res = $DB->Query($count_str);
		$ar  = $res->Fetch();
		$pages_count = ceil($ar['C'] / $navparams['limit']);

		// картинки
		if(!$navparams['nopicts'])
		{
			foreach($_result as $k => &$v)
			{
				$dir = opendir($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$k);
				while($f = readdir($dir))
				{
					if($f != '.' && $f != '..')
					{
						if($f[0] == 'f')
						{
							$v['pictures']['original']['fixed'][] = '/upload/st1234/original/'.$k.'/'.$f;
							$v['pictures']['medium']['fixed'][]   = '/upload/st1234/medium/'.$k.'/'.$f;
							$v['pictures']['small']['fixed'][]    = '/upload/st1234/small/'.$k.'/'.$f;
						}
						elseif(substr($f, 0, 2) == 'gr')
						{
							$v['pictures']['original']['gibddreply'][] = '/upload/st1234/original/'.$k.'/'.$f;
							$v['pictures']['medium']['gibddreply'][]   = '/upload/st1234/medium/'.$k.'/'.$f;
							$v['pictures']['small']['gibddreply'][]    = '/upload/st1234/small/'.$k.'/'.$f;
						}
						else
						{
							$v['pictures']['original']['fresh'][] = '/upload/st1234/original/'.$k.'/'.$f;
							$v['pictures']['medium']['fresh'][]   = '/upload/st1234/medium/'.$k.'/'.$f;
							$v['pictures']['small']['fresh'][]    = '/upload/st1234/small/'.$k.'/'.$f;
						}
					}
				}
				closedir($f);
				sort($v['pictures']['small']['fresh']);
				sort($v['pictures']['medium']['fresh']);
				sort($v['pictures']['original']['fresh']);
				sort($v['pictures']['small']['gibddreply']);
				sort($v['pictures']['medium']['gibddreply']);
				sort($v['pictures']['original']['gibddreply']);
				sort($v['pictures']['small']['fixed']);
				sort($v['pictures']['medium']['fixed']);
				sort($v['pictures']['original']['fixed']);
			}
		}
		return $_result;
	}
	
	/**
	 * Создать картинку из файла.
	 * @param $file_name    string имя файла
	 * @param &$_image_info array  здесь возвращается всякая инфа о картинке
	 */
	public static function imagecreatefromfile($file_name, &$_image_info = array())
	{
		$_image_info = getimagesize($file_name, &$_image_additional_info);
		$_image_info['additional'] = $_image_additional_info;
		switch($_image_info['mime'])
		{
			case 'image/jpeg':
			case 'image/pjpg':
			{
				$operator = 'imagecreatefromjpeg';
				break;
			}
			case 'image/gif':
			{
				$operator = 'imagecreatefromgif';
				break;
			}
			case 'image/png':
			case 'image/x-png':
			{
				$operator = 'imagecreatefrompng';
				break;
			}
			default:
			{
				return false;
			}
		}
		return $operator($file_name);
	}
	
	/**
	 * Обновить данные ямы.
	 * @param  int    $id      айдишник обновляемой ямы
	 * @param  array  $_fields массив со значениями обновляемых полей
	 * @param  array $_params  массив с параметрами
	 * @param  string $error   тут возвращается текст ошибки
	 * @return bool   success
	 */
	public static function Update($id, $_fields, $_params, &$error)
	{
		$error = '';
		$id    = (int)$id;
		if(!$id)
		{
			$error = GetMessage('GREENSIGHT_ERROR_NOID');
			return false;
		}
		if(!sizeof($_fields))
		{
			// если ничего обновлять не надо, то ничего обновлять и не станем
			return true;
		}
		// проверим картинки
		foreach($_fields['FILES'] as $_file)
		{
			if(!$_file['error'])
			{
				$image = C1234Hole::imagecreatefromfile($_file['tmp_name'], &$_image_info);
				if(!$image)
				{
					$error = GetMessage('GREENSIGHT_ERROR_UNSUPPORTED_IMAGE_TYPE');
					return false;
				}
				imagedestroy($image);
			}
		}
		// если надо удалить файлы, удалим
		if(sizeof($_fields['DELETEFILES']))
		{
			foreach($_fields['DELETEFILES'] as &$f)
			{
				if(strlen($f))
				{
					unlink($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id.'/'.$f);
					unlink($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/medium/'.$id.'/'.$f);
					unlink($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/small/'.$id.'/'.$f);
				}
			}
		}
		$file_counter = 0;
		$all_files    = 0;
		$dir = opendir($_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id.'/');
		while($f = readdir($dir))
		{
			$f = explode('.', $f);
			$f = (int)preg_replace('/\D/', '', $f[0]);
			$file_counter = max($file_counter, $f);
			$all_files++;
		}
		$all_files -= 2; // это папки . и ..
		$file_counter++;
		if(!$_params['big_sizex'])
		{
			$_params['big_sizex'] = 1024;
		}
		if(!$_params['big_sizey'])
		{
			$_params['big_sizey'] = 1024;
		}
		foreach($_fields['FILES'] as $_file)
		{
			if(!$_file['error'])
			{
				$all_files++;
				if($all_files > 100)
				{
					break;
				}
				$image = C1234Hole::imagecreatefromfile($_file['tmp_name'], &$_image_info);
				if(!$image)
				{
					$error = GetMessage('GREENSIGHT_ERROR_UNSUPPORTED_IMAGE_TYPE');
					return false;
				}
				$aspect = max($_image_info[0] / $_params['big_sizex'], $_image_info[1] / $_params['big_sizey']);
				if($aspect > 1)
				{
					$new_x    = floor($_image_info[0] / $aspect);
					$new_y    = floor($_image_info[1] / $aspect);
					$newimage = imagecreatetruecolor($new_x, $new_y);
					imagecopyresampled($newimage, $image, 0, 0, 0, 0, $new_x, $new_y, $_image_info[0], $_image_info[1]);
					imagejpeg($newimage, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id.'/'.($_fields['STATE'] == 'fixed' ? 'f' : ($_fields['GIBDD_REPLY_RECEIVED'] ? 'gr' : '')).$file_counter.'.jpg');
				}
				else
				{
					imagejpeg($image, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/original/'.$id.'/'.($_fields['STATE'] == 'fixed' ? 'f' : ($_fields['GIBDD_REPLY_RECEIVED'] ? 'gr' : '')).$file_counter.'.jpg');
				}
				$aspect   = max($_image_info[0] / $_params['medium_sizex'], $_image_info[1] / $_params['medium_sizey']);
				$new_x    = floor($_image_info[0] / $aspect);
				$new_y    = floor($_image_info[1] / $aspect);
				$newimage = imagecreatetruecolor($new_x, $new_y);
				imagecopyresampled($newimage, $image, 0, 0, 0, 0, $new_x, $new_y, $_image_info[0], $_image_info[1]);
				imagejpeg($newimage, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/medium/'.$id.'/'.($_fields['STATE'] == 'fixed' ? 'f' : ($_fields['GIBDD_REPLY_RECEIVED'] ? 'gr' : '')).$file_counter.'.jpg');
				imagedestroy($newimage);
				$aspect   = min($_image_info[0] / $_params['small_sizex'], $_image_info[1] / $_params['small_sizey']);
				$newimage = imagecreatetruecolor($_params['small_sizex'], $_params['small_sizey']);
				imagecopyresampled
				(
					$newimage,
					$image,
					0,
					0,
					$_image_info[0] > $_image_info[1] ? floor(($_image_info[0] - $aspect * $_params['small_sizex']) / 2) : 0,
					$_image_info[0] < $_image_info[1] ? floor(($_image_info[1] - $aspect * $_params['small_sizey']) / 2) : 0,
					$_params['small_sizex'],
					$_params['small_sizey'],
					ceil($aspect * $_params['small_sizex']),
					ceil($aspect * $_params['small_sizey'])
				);
				imagejpeg($newimage, $_SERVER['DOCUMENT_ROOT'].'/upload/st1234/small/'.$id.'/'.($_fields['STATE'] == 'fixed' ? 'f' : ($_fields['GIBDD_REPLY_RECEIVED'] ? 'gr' : '')).$file_counter.'.jpg');
				imagedestroy($newimage);
				imagedestroy($image);
				$file_counter++;
			}
		}
		unset($_fields['FILES']);
		unset($_fields['DELETEFILES']);
		$sql_str = "update `b_holes` set";
		foreach($_fields as $k => $v)
		{
			$sql_str .= " `".mysql_escape_string($k)."` = '".mysql_escape_string($v)."',";
		}
		$sql_str = substr($sql_str, 0, strlen($sql_str) - 1)." where `ID` = '".$id."'";
		global $DB;
		CGreensightDBQueryCache::ClearAllCache();
		return $DB->Query($sql_str) ? true : false;
	}
}

?>