<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


$arParams['LIMIT'] = (int) $arParams['LIMIT'];

/**
*склонение дат, принимает дату в определенном формате, возвращает слово правильном склонении
*@params int $num, char $format
*@return string $end
**/
function declination($num, $format)
{
	$last = substr($num, strlen($num)-1);
	if((substr($num, strlen($num)-2) >= 11 && substr($num, strlen($num)-2) <= 19)
		|| $last == 0 
		|| ($last >= 5 && $last <= 9))
	{
		switch($format)
		{
			case 'Y': $end = GetMessage('YEAR1'); break;
			case 'm': $end = GetMessage('MONTH1'); break;
			case 'd': $end = GetMessage('DAY1'); break;
			case 'H': $end = GetMessage('HOUR1'); break;
			case 'i': $end = GetMessage('MINUTE1'); break;
			case 's': $end = GetMessage('SECOND1'); break;
		}
	}
	elseif($last >= 2 && $last <= 4)
	{
		switch($format)
		{
			case 'Y': $end = GetMessage('YEAR2'); break;
			case 'm': $end = GetMessage('MONTH2'); break;
			case 'd': $end = GetMessage('DAY2'); break;
			case 'H': $end = GetMessage('HOUR2'); break;
			case 'i': $end = GetMessage('MINUTE2'); break;
			case 's': $end = GetMessage('SECOND2'); break;
		}
	}
	elseif($last == 1)
	{
		switch($format)
		{
			case 'Y': $end = GetMessage('YEAR3'); break;
			case 'm': $end = GetMessage('MONTH3'); break;
			case 'd': $end = GetMessage('DAY3'); break;
			case 'H': $end = GetMessage('HOUR3'); break;
			case 'i': $end = GetMessage('MINUTE3'); break;
			case 's': $end = GetMessage('SECOND3'); break;
		}
	}
	return ' '.$end;
}

global $DB;

$limit_sql = !empty($arParams['LIMIT']) ? ' limit '.$arParams['LIMIT'] : '';
//по городам
$sql['geography'][] = 'select count(*) as counts, adr_city from b_holes where adr_city!="" and premoderated=1 group by trim(adr_city) order by counts desc'.$limit_sql;
$sql['geography'][] = 'select count(*) as counts, adr_city from b_holes where state="fixed" and adr_city!="" and premoderated=1 group by trim(adr_city) order by counts desc'.$limit_sql;

// по статусам
$sql['state'][] = 'select count(*) as counts, state as state_to_filter from b_holes where premoderated=1 group by state_to_filter order by counts desc';
$sql['state'][] = 'select avg(DATE_STATUS-DATE_SENT) as time from b_holes where state="fixed" and premoderated=1';

// по пользователям
$sql['user'][] = 'select count(*) as counts, b_user.login, b_user.name, b_user.last_name from b_holes join b_user on b_holes.user_id=b_user.id where b_holes.premoderated=1 group by b_holes.user_id order by counts desc'.$limit_sql;
$sql['user'][] = 'select count(*) as counts, b_user.login, b_user.name, b_user.last_name from b_holes join b_user on b_holes.user_id=b_user.id where b_holes.state="fixed" and b_holes.premoderated=1 group by b_holes.user_id order by counts desc'.$limit_sql;

// Запросы из массива sql
foreach($sql as $key=>$val)
{
	foreach($val as $sk=>$ssql)
	{
		$res = $DB->Query($ssql);
		while($ar = $res->Fetch())
		{
			$arResult[$key][$sk][] = $ar;
		}
	}
}


$ru = array(
	'fresh'      => GetMessage('STATE1'),
	'achtung'    => GetMessage('STATE2'),
	'inprogress' => GetMessage('STATE3'),
	'fixed'      => GetMessage('STATE4'),
	'prosecutor' => GetMessage('STATE5'),
	'gibddre'    => GetMessage('STATE6')
);
foreach($arResult['state'][0] as $k=>$ar){
	$arResult['state'][0][$k]['state'] = strtr($ar['state_to_filter'], $ru);
}


$num = date('Y', $arResult['state'][1][0]['time'])-1970;
$tmp = $num != 0 ? $num.declination($num, 'Y').', ' : '';

$num = gmdate('m', $arResult['state'][1][0]['time'])-1;
$tmp .= $num != 0 ? $num.declination($num, 'm').', ' : '';

$num = gmdate('d', $arResult['state'][1][0]['time'])-1;
$tmp .= $num != 0 ? $num.declination($num, 'd').', ' : '';

$num = gmdate('H', $arResult['state'][1][0]['time']);
$tmp .= $num != 0 ? $num.declination($num, 'H').', ' : '';

$num = gmdate('i', $arResult['state'][1][0]['time']);
$tmp .= $num != 0 ? $num.declination($num, 'i').', ' : '';

$num = gmdate('s', $arResult['state'][1][0]['time'])-1;
$tmp .= $num != 0 ? $num.declination($num, 's').', ' : '';

$tmp = substr($tmp, 0, strlen($tmp) - 2);
$arResult['state'][1][0]['time'] = $tmp;

for($i = 0; $i < 2; $i++){
	foreach($arResult['user'][$i] as $k=>$v)
	{
		$arResult['user'][$i][$k]['user'] = (!empty($v['name']) && !empty($v['last_name'])) ? $v['last_name'].' '.$v['name'] : $v['login'];
	}
}

$this->IncludeComponentTemplate();
?>