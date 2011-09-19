<?php

/**
 * Всякие вспомогательные штуки.
 */

IncludeModuleLangFile(__FILE__);

class CGreensightUtils
{
	/**
	 * Переводит таймстамп в русскочеловеческий понятный формат даты.
	 * @param  int $timestamp timestamp
	 * @return string
	 */
	public static function timestamp2human($timestamp)
	{
		return preg_replace('/^0/', '', date('d', $timestamp)).' '.GetMessage('GREENSIGH_RUMONTH_'.date('n', $timestamp)).' '.date('Y', $timestamp);
	}
	
	/**
	 * Делает первую букву каждого слова заглавной.
	 * @param  string $string
	 * @return string
	 */
	public static function CapitalizeFirst($string)
	{
		$string = explode(' ', $string);
		foreach($string as &$s)
		{
			$s = ToUpper(substr($s, 0, 1)).ToLower(substr($s, 1));
		}
		return implode(' ', $string);
	}
	
	/**
	 * Заменить значение параметра в строке (или добавить, если нет).
	 * @param  string $param  имя параметра, который менять
	 * @param  string $value  новое значение параметра
	 * @param  string $string исходная строка, если не задана, берётся $_SERVER['REQUEST_URI']
	 * @return string
	 */
	public static function RequestStrReplace($param, $value, $string = false)
	{
		if($string === false)
		{
			$string = $_SERVER['REQUEST_URI'];
		}
		$string    = explode('?', $string);
		$string[1] = explode('&', $string[1]);
		$bReplaced = false;
		$paramstrl = strlen($param) + 1;
		foreach($string[1] as &$v)
		{
			$vv = substr($v, 0, $paramstrl);
			if($vv == $param.'=')
			{
				$v = $vv.$value;
				$bReplaced = true;
			}
		}
		if(!$bReplaced)
		{
			$string[1][] = $param.'='.$value;
		}
		$string[1] = implode('&', $string[1]);
		return implode('?', $string);
	}
}

?>