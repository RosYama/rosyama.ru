<?php

/**
 * Файл экспорта структуры инфоблоков административной чавсти модуля greensight_utils
 */

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);
$APPLICATION->SetTitle(GetMessage('GREENSIGHT_IBLOCK_EXPORT_TITLE'));
if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

echo GetMessage('GREENSIGHT_IBLOCK_EXPORT_PATH');

// какая-то херня, скопированная из файла iblock_type_admin.php из модуля инфоблоков
// лишнее вырезал на фиг, своё добавил
$sTableID = "tbl_iblock_type";
$oSort    = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin   = new CAdminList($sTableID, $oSort);

// тут происходит экспорт
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CIBlockType::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	
	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		switch($_REQUEST['action'])
		{
			case 'export':
			{
				$_result = array();
				// инфоблоки
				$rsIBlock = CIBlock::GetList(false, array('TYPE' => $ID));
				while($arIBlock = $rsIBlock->Fetch())
				{
					// свойства инфоблоков
					$rsIBProp = CIBlockProperty::GetList(false, array('IBLOCK_ID' => $arIBlock['ID']));
					while($arIBProp = $rsIBProp->Fetch())
					{
						// если свойство типа список, надо узнать его значения
						if($arIBProp['PROPERTY_TYPE'] == 'L')
						{
							$rsIBPropEnum = CIBlockPropertyEnum::GetList(false, array('PROPERTY_ID' => $arIBProp['ID']));
							while($arIBPropEnum = $rsIBPropEnum->Fetch())
							{
								$arIBProp['list_values'][] = $arIBPropEnum;
							}
						}
						// добавим свойство в массив со свойствами инфоблока
						$arIBlock['properties'][] = $arIBProp;
					}
					// добавить готовый массив инфоблока в массив с результатами
					$code = $arIBlock['CODE'] ? $arIBlock['CODE'] : sizeof($_result);
					$_result[$code] = $arIBlock;
				}
				if(sizeof($_result))
				{
					$f = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/backup/iblock_export_'.$ID.'_'.date('YmdHis').'.txt.1', 'w');
					fputs($f, '# IBlock structure export '.date('Y-m-d H:i:s').' '.COption::GetOptionString('main', 'server_name').' ('.COption::GetOptionString('main', 'site_name').') '."\n\n".var_export($_result, 1));
					fclose($f);
				}
			}
		}
	}
}
// конец действий, начало вывода
$rsData = CIBlockType::GetList(Array($by => $order));
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("IBLOCK_TYPE_ADMIN_NAV")));
$lAdmin->AddHeaders(array(
	array("id"=>"ID",   "content" => "ID",   "default" => true),
	array("id"=>"NAME", "content" => "NAME", "default" => true)
));
while($arRes = $rsData->NavNext(true, "f_"))
{
	$ibtypelang = CIBlockType::GetByIDLang($f_ID, LANGUAGE_ID);
	$row =& $lAdmin->AddRow($f_ID, $arRes, '', '');
	$row->AddViewField("NAME", $ibtypelang["NAME"]);
}
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"export" => GetMessage('GREENSIGHT_IBLOCK_EXPORT_EXPORT')
));
$lAdmin->CheckListMode();
$lAdmin->DisplayList();

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>