<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
set_time_limit(0);

// склонятор
function sklonyator($str)
{
	$nanoyandex_reply = file_get_contents('http://export.yandex.ru/inflect.xml?name='.urlencode($str));
	$pos = strpos($nanoyandex_reply, '<inflection case="3">');
	if($pos === false)
	{
		return $str;
	}
	$nanoyandex_reply = substr($nanoyandex_reply, $pos);
	$nanoyandex_reply = substr($nanoyandex_reply, 21, strpos($nanoyandex_reply, '</inflection>') - 21); // 21 = strlen('<inflection case="3">')
	return trim($nanoyandex_reply, "\n\t ");
}

/* if(COption::GetOptionInt('main', 'gibdd_reference_filled') > time() - 86400 * 6)
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
	die();
}*/

// 1) достать список регионов
$text = file_get_contents('http://www.gibdd.ru/regions/');
$text = substr($text, strpos($text, '<table cellpadding="0" cellspacing="0" width="730">'));
$text = substr($text, 0, strpos($text, '</table>'));
$text = explode('<tr>', $text);
$i = 0;
$_regions = array();
foreach($text as &$item)
{
	if($i > 2)
	{
		$item = explode('<td', $item);
		preg_match('/\<a[\s\S]*href=(\"|\')([\s\S]*)\1[\s\S]*\>([\s\S]*)\<\/a\>/U', $item[1], $_m);
		$region_id = substr($_m[2], 14);
		$_regions[$region_id] = array
		(
			'id'   => $region_id,
			'name' => $_m[3],
			'href' => $_m[2]
		);
	}
	$i++;
}

// 2) сопоставить всем регионам субъект РФ
CModule::IncludeModule('st1234holes');
foreach($_regions as &$r)
{
	foreach(CGreensightRFSubject::$_RF_SUBJECTS_FULL as $k => &$s)
	{

		
		if(strtolower($s) == strtolower($r['name']))
		{
			$r['subject_id']   = $k;
			$r['subject_name'] = $s;
			continue;
		}
		else
		{
			$name = explode(' ', $r['name']);
			$sname = explode(' ', strtolower($s));
			foreach($name as $part)
			{
				$part = strtolower($part);
				if
				(
					$part != ''
					&& $part != 'Республика' // на промышленном сервере strtolower на срабатывает на слове "республика"
					&& $part != 'республика'
					&& $part != 'край'
					&& $part != 'область'
					&& $part != 'автономная'
					&& $part != 'округ'
					&& $part != 'автономный'
				)
				{
					//if(stripos($s, $part) !== false)
					if(in_array($part, $sname))
					{
						$r['subject_id']   = $k;
						$r['subject_name'] = $s;
						continue;
					}
				}
			}
		}
	}
	foreach($_regions as $rr)
	{
		if($rr['id'] != $r['id'] && $rr['subject_id'] == $r['subject_id'])
		{
			echo 'коллизия '.$r['name'].'-'.$rr['name'].'<br>';
			die();
		}
	}
	if(!$r['subject_id'])
	{
		echo 'нет ид '.$r['name'].'<br>';
		die();
	}
}

// 3) для каждого региона достать его главу и контакты
CModule::IncludeModule('greensight_utils');
foreach($_regions as &$r)
{
	if(!$r['subject_id'] || !$r['href'])
	{
		echo 'нет ссылки или ид субъекта '.$r['name'].'<br>';
		die();
	}
	
	
	$text = file_get_contents('http://www.gibdd.ru'.$r['href']);
	$text = substr($text, strpos($text, '<p class="bold" style="padding-bottom:15px;">'));
	$text = substr($text, 0, strpos($text, '</div>'));
	$text = explode('<p class="bold">', $text);
	
	$r['gibbd_name']=strip_tags(trim($text[0]));
	
	$text[0] = str_replace('УПРАВЛЕНИЕ', 'УПРАВЛЕНИЯ', strip_tags($text[0]));
	$text[1] = explode('</p>', $text[1]);
	$text[1][0] = str_replace(':', '', strip_tags($text[1][0]));
	$text[1][1] = str_replace(':', '', strip_tags($text[1][1]));
	
	$r['gibbd_name_dative']=$text[0];
	$r['gibbd_head_post']     = trim($text[1][0]);
	$r['gibbd_head_fio']      = trim($text[1][1]);
	$r['gibbd_head_post_gen'] = trim($text[1][0].'у '.$text[0]);
	$r['gibbd_head_fio_gen']  = sklonyator($text[1][1]);
	$r['gibdd_contacts'] = '';
	$contact_fiels=Array('','','addr', 'tel_degurn', 'tel_dover','url');
	for($i = 2; $i < 6; $i++)
	{
		$r['gibdd_contacts'] .= strip_tags(trim($text[$i])).'<br>';
		$text[$i] = explode('</p>', $text[$i]);
		$text[$i][0] = str_replace(':', '', strip_tags($text[$i][0]));
		$text[$i][1] = str_replace(':', '', strip_tags($text[$i][1]));
		$r['gibdd_'.$contact_fiels[$i]]=trim($text[$i][1]);
		
	}
}

// 4) занести всё в инфоблоки
CModule::IncludeModule('iblock');
$res    = CIBlock::GetList(array(), array('CODE' => 'GIBDD_HEADS'));
$iblock = $res->Fetch();
$cibe   = new CIBlockElement();
foreach($_regions as &$r)
{

	$res = $cibe->GetList(array(), array('IBLOCK_ID' => $iblock['ID'], 'PROPERTY_SUBJECT_ID' => $r['subject_id']));
	$ar  = $res->Fetch();
	//else $ar['id']=0;
	//echo $ar['ID'].'-!!!<br/>';
	
	if(!$ar['ID'])
	{
		$cibe->Add
		(
			array
			(
				'IBLOCK_ID'       => $iblock['ID'],
				'NAME'            => $r['subject_id'].' '.$r['subject_name'].' ('.$r['id'].')',
				'PROPERTY_VALUES' => array
				(
					'SUBJECT_ID'  => $r['subject_id'],
					'POST'        => $r['gibbd_head_post'],
					'FIO'         => $r['gibbd_head_fio'],
					'GIBDD_NAME'  => $r['gibbd_name'],
					'CONTACTS'    => $r['gibdd_contacts'],
					'ADDRESS'     => $r['gibdd_addr'],
					'TEL_DEGURN'  => $r['gibdd_tel_degurn'],
					'TEL_DOVER'   => $r['gibdd_tel_dover'],
					'URL'    	  => $r['gibdd_url'],					
					'POST_DATIVE' => $r['gibbd_head_post_gen'],
					'FIO_DATIVE'  => $r['gibbd_head_fio_gen']
				)
			)
		);
	}
	else
	{
		$cibe->Update
		(
			$ar['ID'],
			array
			(
				'NAME'            => $r['subject_id'].' '.$r['subject_name'].' ('.$r['id'].')',
				'PROPERTY_VALUES' => array
				(
					'SUBJECT_ID'  => $r['subject_id'],
					'POST'        => $r['gibbd_head_post'],
					'FIO'         => $r['gibbd_head_fio'],
					'GIBDD_NAME'  => $r['gibbd_name'],
					'CONTACTS'    => $r['gibdd_contacts'],
					'ADDRESS'     => $r['gibdd_addr'],
					'TEL_DEGURN'  => $r['gibdd_tel_degurn'],
					'TEL_DOVER'   => $r['gibdd_tel_dover'],
					'URL'    	  => $r['gibdd_url'],					
					'POST_DATIVE' => $r['gibbd_head_post_gen'],
					'FIO_DATIVE'  => $r['gibbd_head_fio_gen']
				)
			)
		);
	}
	unset ($ar);
}

//COption::SetOptionInt('main', 'gibdd_reference_filled', time());

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');

?>