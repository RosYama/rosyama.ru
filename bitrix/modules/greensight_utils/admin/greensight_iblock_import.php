<?php

/**
 * Файл импорта структуры инфоблоков административной чавсти модуля greensight_utils
 */

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);
$APPLICATION->SetTitle(GetMessage('GREENSIGHT_IBLOCK_IMPORT_TITLE'));
if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($_POST)
{
	do
	{
		$DB->StartTransaction();
		// создание инфоблока, если надо
		if($_POST['iblock_type'] == '0')
		{
			$cibt = new CIBlockType();
			if(
				$cibt->Add(array
				(
					'ID'               => $_POST['newiblocktype_id'],
					'SECTIONS'         => 'Y',
					'EDIT_FILE_BEFORE' => '',
					'EDIT_FILE_AFTER'  => '',
					'IN_RSS'           => 'N',
					'SORT'             => 500,
					'LANG'             => array
					(
						'en' => array
						(
							'NAME'         => $_POST['newiblocktype_id'],
							'SECTION_NAME' => 'Sections',
							'ELEMENT_NAME' => 'Elements'
						),
						'ru' => array
						(
							'NAME'         => $_POST['newiblocktype_name'],
							'SECTION_NAME' => 'Секции',
							'ELEMENT_NAME' => 'Элементы'
						)
					)
				))
			)
			{
				$iBlockTypeID = $_POST['newiblocktype_id'];
			}
			else
			{
				$DB->Rollback();
				ShowError(GetMessage('GREENSIGHT_IBLOCK_IMPORT_ERROR_TYPE_CREATE'));
				break;
			}
		}
		
		// выбрать нужный тип инфоблока
		if(!$iBlockTypeID)
		{
			$rsIBlockType = CIBlockType::GetByID($_POST['iblock_type']);
			$iBlockTypeID = $rsIBlockType->Fetch();
			$iBlockTypeID = $iBlockTypeID['ID'];
		}
		if(!$iBlockTypeID)
		{
			$DB->Rollback();
			ShowError(GetMessage('GREENSIGHT_IBLOCK_IMPORT_ERROR_TYPE_SELECT'));
			break;
		}
		
		// загрузка файла в переменную
		$data = explode("\n", file_get_contents($_FILES['import_file']['tmp_name']));
		if($data[0][0] == '#')
		{
			unset($data[0]);
		}
		if(!strlen($data[1]))
		{
			unset($data[1]);
		}
		$data = implode("\n", $data);
		eval('$data = '.$data.';');
		if(!is_array($data) || !sizeof($data))
		{
			ShowError(GetMessage('GREENSIGHT_IBLOCK_IMPORT_ERROR_EVAL'));
			break;
		}
		
		// импорт структуры
		$cib  = new CIBlock();
		$cibp = new CIBlockProperty();
		foreach($data as &$_iblock_data)
		{
			$rsIBlock = $cib->GetList(false, array('CODE' => $_iblock_data['CODE'], 'TYPE' => $iBlockTypeID));
			$arIBlock = $rsIBlock->Fetch();
			if($arIBlock['ID'])
			{
				if($_POST['overwrite'] == '1')
				{
					// если инфоблоки перезаписываем, удалим имеющийся
					$cib->Delete($arIBlock['ID']);
				}
				else
				{
					// если инфблоки не перезаписываем, то пропускаем этот
					continue;
				}
			}
			
			// создание инфоблоков
			if(!$cib->Add(array
				(
					'SITE_ID'           => array($_iblock_data['LID']),
					'CODE'              => $_iblock_data['CODE'],
					'EXTERNAL_ID'       => $_iblock_data['EXTERNAL_ID'],
					'XML_ID'            => $_iblock_data['XML_ID'],
					'IBLOCK_TYPE_ID'    => $iBlockTypeID,
					'NAME'              => $_iblock_data['NAME'],
					'ACTIVE'            => $_iblock_data['ACTIVE'],
					'SORT'              => $_iblock_data['SORT'],
					'LIST_PAGE_URL'     => $_iblock_data['LIST_PAGE_URL'],
					'SECTION_PAGE_URL'  => $_iblock_data['SECTION_PAGE_URL'],
					'DETAIL_PAGE_URL'   => $_iblock_data['DETAIL_PAGE_URL'],
					'PICTURE'           => $_iblock_data['PICTURE'],
					'DESCRIPTION'       => $_iblock_data['DESCRIPTION'],
					'DESCRIPTION_TYPE'  => $_iblock_data['DESCRIPTION_TYPE'],
					'RSS_ACTIVE'        => $_iblock_data['RSS_ACTIVE'],
					'RSS_TTL'           => $_iblock_data['RSS_TTL'],
					'RSS_FILE_ACTIVE'   => $_iblock_data['RSS_FILE_ACTIVE'],
					'RSS_FILE_LIMIT'    => $_iblock_data['RSS_FILE_LIMIT'],
					'RSS_FILE_DAYS'     => $_iblock_data['RSS_FILE_DAYS'],
					'RSS_YANDEX_ACTIVE' => $_iblock_data['RSS_YANDEX_ACTIVE'],
					'INDEX_ELEMENT'     => $_iblock_data['INDEX_ELEMENT'],
					'INDEX_SECTION'     => $_iblock_data['INDEX_SECTION'],
					'WORKFLOW'          => $_iblock_data['WORKFLOW'],
					'SECTION_CHOOSER'   => $_iblock_data['SECTION_CHOOSER'],
					'VERSION'           => $_iblock_data['VERSION'],
					'EDIT_FILE_BEFORE'  => $_iblock_data['EDIT_FILE_BEFORE'],
					'EDIT_FILE_AFTER'   => $_iblock_data['EDIT_FILE_AFTER']
				))
			)
			{
				$DB->Rollback();
				ShowError(GetMessage('GREENSIGHT_IBLOCK_IMPORT_ERROR_IBLOCK_CREATE'));
				break 2;
			}
			$rsIBlock = $cib->GetList(false, array('CODE' => $_iblock_data['CODE'], 'TYPE' => $iBlockTypeID));
			$arIBlock = $rsIBlock->Fetch();
			
			// добавление свойств
			if(sizeof($_iblock_data['properties']))
			{
				foreach($_iblock_data['properties'] as &$_iblock_property)
				{
					$_property = array
					(
						'CODE'             => $_iblock_property['CODE'],
						'XML_ID'           => $_iblock_property['XML_ID'],
						'IBLOCK_ID'        => $arIBlock['ID'],
						'NAME'             => $_iblock_property['NAME'],
						'ACTIVE'           => $_iblock_property['ACTIVE'],
						'SORT'             => $_iblock_property['SORT'],
						'PROPERTY_TYPE'    => $_iblock_property['PROPERTY_TYPE'],
						'MULTIPLE'         => $_iblock_property['MULTIPLE'],
						'DEFAULT_VALUE'    => $_iblock_property['DEFAULT_VALUE'],
						'ROW_COUNT'        => $_iblock_property['ROW_COUNT'],
						'COL_COUNT'        => $_iblock_property['COL_COUNT'],
						'LIST_TYPE'        => $_iblock_property['LIST_TYPE'],
						'MULTIPLE_CNT'     => $_iblock_property['MULTIPLE_CNT'],
						'FILE_TYPE'        => $_iblock_property['FILE_TYPE'],
						'SEARCHABLE'       => $_iblock_property['SEARCHABLE'],
						'FILTRABLE'        => $_iblock_property['FILTRABLE'],
						'LINK_IBLOCK_ID'   => $_iblock_property['LINK_IBLOCK_ID'],
						'WITH_DESCRIPTION' => $_iblock_property['WITH_DESCRIPTION'],
						'VERSION'          => $_iblock_property['VERSION'],
						'USER_TYPE'        => $_iblock_property['USER_TYPE']
					);
					// варианты значений свойств типа "список"
					if(sizeof($_iblock_property['list_values']))
					{
						foreach($_iblock_property['list_values'] as &$_list_value)
						{
							$_property['VALUES'][] = array
							(
								'VALUE'       => $_list_value['VALUE'],
								'DEF'         => $_list_value['DEF'],
								'SORT'        => $_list_value['SORT'],
								'XML_ID'      => $_list_value['XML_ID'],
								'EXTERNAL_ID' => $_list_value['EXTERNAL_ID']
							);
						}
					}
					if(!$cibp->Add($_property))
					{
						$DB->Rollback();
						ShowError(GetMessage('GREENSIGHT_IBLOCK_IMPORT_ERROR_PROP_CREATE'));
						break 3;
					}
				}
			}
		}
	}
	while(false);
	$DB->Commit();
	echo GetMessage('GREENSIGHT_IBLOCK_IMPORT_OK');
}

// подготовка данных для формы
// список типов инфоблоков
$arIBTypes = array(
	'REFERENCE' => array(
		GetMessage('GREENSIGHT_IBLOCK_IMPORT_NEW')
	),
	'REFERENCE_ID' => array(
		0
	)
);
$rsIBTypes = CIBlockType::GetList();
while($ar = $rsIBTypes->Fetch())
{
	$ar2 = CIBlockType::GetByIDLang($ar['ID'], LANGUAGE_ID);
	$arIBTypes['REFERENCE'][]    = $ar2['NAME'].' ('.$ar['ID'].')';
	$arIBTypes['REFERENCE_ID'][] = $ar['ID'];
}

?>
<form action="<?= BX_ROOT ?>/admin/greensight_iblock_import.php" method="post" enctype="multipart/form-data">
	<table class="list" cellspacing="0">
		<tr class="gutter">
			<td class="left"><div class="empty"></div></td><td class="right"><div class="empty"></div></td>
		</tr>
		<tr class="head">
			<td class="left">&nbsp;</td><td class="right">&nbsp;</td>
		</tr>
		<tr class="odd">
			<td class="left">
				<?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_IBLOCK_TYPE'); ?>
			</td>
			<td class="right">
				<?= SelectBoxFromArray('iblock_type', $arIBTypes, false, false, 'onchange="document.getElementById(\'newiblocktypediv\').style.display=(this.value==0 ? \'block\' : \'none\')"') ?>
				<div id="newiblocktypediv">
					<a href="/bitrix/admin/iblock_type_admin.php" target="_blank"><?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_GO') ?></a> <?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_OR') ?><br>
					<table>
						<tr>
							<td><label for="newiblocktype_name"><?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_NAME') ?></label></td>
							<td><input type="text" name="newiblocktype_name" id="newiblocktype_name"></td>
						</tr>
						<tr>
							<td><label for="newiblocktype_id"><?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_ID') ?></label></td>
							<td><input type="text" name="newiblocktype_id" id="newiblocktype_id"></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<tr class="even">
			<td class="left">
				<?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_OVERWRITE_MODE'); ?>
			</td>
			<td class="right">
				<input type="radio" name="overwrite" value="1" id="overwrite1" checked>
				<label for="overwrite1"><?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_OVERWRITE_MODE1'); ?></label><br>
				<input type="radio" name="overwrite" value="0" id="overwrite0">
				<label for="overwrite0"><?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_OVERWRITE_MODE0'); ?></label><br>
			</td>
		</tr>
		<tr class="odd">
			<td class="left">
				<?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_FILE'); ?>
			</td>
			<td class="right">
				<input type="file" name="import_file">
			</td>
		</tr>
	</table>
	<input type="submit" value="<?= GetMessage('GREENSIGHT_IBLOCK_IMPORT_SUBMIT') ?>">
</form>
<?

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>