<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
set_time_limit(0);
CModule::IncludeModule('iblock');
CModule::IncludeModule('st1234holes');
$res = CIBlock::GetList(array(), array('CODE' => 'PROSECUTORS'));
$iBlockTypeID = $res->Fetch();
$iBlockTypeID = $iBlockTypeID['ID'];
$cibe = new CIBlockElement();

$raw_html = file_get_contents('http://genproc.gov.ru/structure/subjects/');
preg_match_all('`<select([\s\S]+)</select>`U', $raw_html, $_matches);
preg_match_all('`<option value="([\d]+)"[\s\S]*>([\s\S]+)</option>`U', $_matches[0][0], $_matches, PREG_SET_ORDER);

//$_matches = array ( 0 => array ( 0 => '', 1 => '110', 2 => 'Центральный федеральный округ', ), 1 => array ( 0 => '', 1 => '111', 2 => 'Северо-Западный федеральный округ', ), 2 => array ( 0 => '', 1 => '112', 2 => 'Южный федеральный округ', ), 3 => array ( 0 => '', 1 => '241', 2 => 'Северо-Кавказский федеральный округ', ), 4 => array ( 0 => '', 1 => '113', 2 => 'Приволжский федеральный округ', ), 5 => array ( 0 => '', 1 => '114', 2 => 'Уральский федеральный округ', ), 6 => array ( 0 => '', 1 => '115', 2 => 'Сибирский федеральный округ', ), 7 => array ( 0 => '', 1 => '116', 2 => 'Дальневосточный федеральный округ', ), 8 => array ( 0 => '', 1 => '242', 2 => 'Центральный аппарат', ), );

foreach($_matches as &$set)
{
	$raw_html = file_get_contents('http://genproc.gov.ru/structure/subjects/district-'.$set[1].'/');
	if(!$raw_html)
	{
		echo $set[1].' - fail<br>';
		continue;
	}
	$raw_html = substr($raw_html, strpos($raw_html, '<dl class="institutions">'));
	$raw_html = explode('<div>', $raw_html);
	foreach($raw_html as &$office)
	{
		
		
		
		$office = explode('</a>', $office);
		if($office[1])
		{
			$office[0] = strip_tags($office[0]);
			$subject = explode("\n", $office[0]);
			$itemname=$subject[2];
			$subject = (int)CGreensightRFSubject::GetID($subject[1]);
			
			$office[0] = trim(str_replace("\n", ' ', str_replace("\t", ' ', $office[0])));
			$itemname = trim(str_replace("\n", '', str_replace("\t", '', $itemname)));
			$office[1] = trim(str_replace("\t", ' ', strip_tags($office[1], '<br>')));
			$office[2] = '';
			$office[3] = '';
			$office[4] = '';
			
			$arValues = array
			(
				'IBLOCK_ID'       => $iBlockTypeID,
				'NAME'            => $office[0],
				'PREVIEW_TEXT'    => $office[1],
				'PREVIEW_TEXT_TYPE'=>'html',
				'SORT'=>$subject*10,
				'PROPERTY_VALUES' => array('SUBJECT_ID' => $subject, 'GIBDD_NAME'  => $itemname,)
			);
			
			if($subject)
			{
				$arFilter = array('PROPERTY_SUBJECT_ID' => $subject, 'IBLOCK_ID' => $iBlockTypeID,);
			}
			else
			{
				$arFilter = array('NAME' => $office[0], 'IBLOCK_ID' => $iBlockTypeID,);
			}
			$res = $cibe->GetList(array(), $arFilter);
			$ar  = $res->Fetch();
			if($ar['ID'])
			{
				$cibe->Update($ar['ID'], $arValues);
			}
			else
			{
				$cibe->Add($arValues);
				echo $cibe->LAST_ERROR;
			}
		}
	}
	echo $set[1].' - ok<br>';
}


/*

//echo htmlspecialchars(print_r($raw_html, 1));

echo '<pre>';

print_r($raw_html);
*/

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');

?>