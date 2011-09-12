<?php

/**
 * Инструментарий для кэширования запросов к БД.
 */

class CGreensightDBQueryCache
{
	public static function ClearAllCache()
	{
		$dirname = $_SERVER['DOCUMENT_ROOT'].'/bitrix/managed_cache/BANKA';
		$d = opendir($dirname);
		if($d)
		{
			while($f = readdir($d))
			{
				if($f != '.' && $f != '..')
				{
					unlink($dirname.'/'.$f);
				}
			}
		}
	}
	
	public static function QueryCached($qstr, $cache_max_lifetime = 3600, $DB = false)
	{
		if($DB === false)
		{
			global $DB;
		}
		// проверим, есть ли папочка
		$dirname = $_SERVER['DOCUMENT_ROOT'].'/bitrix/managed_cache/BANKA';
		$d = opendir($dirname);
		if(!$d)
		{
			if(!mkdir($dirname))
			{
				return CGreensightDBQueryCache::QueryFetched($qstr, $DB);
			}
		}
		else
		{
			closedir($d);
		}
		// название файла кэша
		$filename = $dirname.'/'.md5($qstr);
		if(file_exists($filename))
		{
			// если файл кэша существует...
			if(filemtime($filename) > time() - $cache_max_lifetime)
			{
				// и он довольно свеженький...
				$_result = file_get_contents($filename);
				eval('$_result = '.$_result.';');
				return $_result;
			}
		}
		// создаём (или обновляем) файл кэша и возвращаем результат
		$_result = CGreensightDBQueryCache::QueryFetched($qstr, $DB);
		file_put_contents($filename, var_export($_result, true));
		return $_result;
	}
	
	public static function QueryFetched($qstr, $DB = false)
	{
		if($DB === false)
		{
			global $DB;
		}
		$_result = array();
		$res = $DB->Query($qstr);
		while($ar = $res->Fetch())
		{
			$_result[] = $ar;
		}
		return $_result;
	}
}

?>