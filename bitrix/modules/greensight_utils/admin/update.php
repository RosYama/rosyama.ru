<?php

/**
 * Система поддержки структуры БД и вообще всего, что угодно, в актуальном состоянии
 */

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);
$APPLICATION->SetTitle(GetMessage('GREENSIGHT_UPDATE_TITLE'));
if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

// определение текущей и доступной версий таблиц
$iVersion          = (int)COption::GetOptionInt('greensight_utils', 'table_version', 0);
$iVersionAvailable = 0;
$dir               = opendir($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/greensight_utils/updates/');
while($file = readdir($dir))
{
	$file = substr($file, 0, strpos($file, '.'));
	$file = (int)$file;
	$iVersionAvailable = max($iVersionAvailable, $file);
}
closedir($dir);

if($_POST)
{
	// обновление
	$_update_files = array();
	$dir           = opendir($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/greensight_utils/updates/');
	while($file = readdir($dir))
	{
		$fileid = substr($file, 0, strpos($file, '.'));
		$fileid = (int)$fileid;
		if($fileid > $iVersion)
		{
			$_update_files[$fileid] = $file;
		}
	}
	closedir($dir);
	ksort(&$_update_files);
	$bOk = true;
	foreach($_update_files as $id => $f)
	{
		$result = require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/greensight_utils/updates/'.$f);
		if($result === false)
		{
			$bOk = false;
			ShowError(GetMessage('GREENSIGHT_UPDATE_ERROR').' '.$f);
			break;
		}
		COption::SetOptionInt('greensight_utils', 'table_version', $id);
		$iVersion = $id;
	}
	if($bOk)
	{
		echo GetMessage('GREENSIGHT_UPDATE_OK').'<br><br>';
	}
}

?>
<form action="/bitrix/admin/greensight_update.php" method="post">
	<?= GetMessage('GREENSIGHT_UPDATE_VERSION') ?>: <strong><?= $iVersion ?></strong><br>
	<?= GetMessage('GREENSIGHT_UPDATE_VERSION_AVAILABLE') ?>: <strong><?= $iVersionAvailable ?></strong><br>
	<input type="submit" name="submit" value="<?= GetMessage('GREENSIGHT_UPDATE_SUBMIT') ?>">
</form>
<?


include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>