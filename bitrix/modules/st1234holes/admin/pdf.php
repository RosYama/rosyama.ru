<?php


require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule('st1234holes'))
{
	die(':(');
}

$APPLICATION->SetTitle(GetMessage('GREENSIGHT_ST1234_PDF_TITLE'));
if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if(isset($_POST['ID']))
{
	// формирование пдфника
	$_POST['ID'] = (int)$_POST['ID'];
	$arElement   = C1234Hole::GetById($_POST['ID']);
	if(!$arElement['ID'])
	{
		LocalRedirect('..');
		die();
	}
	foreach($arElement['pictures']['medium']['fresh'] as &$v)
	{
		$v = $_SERVER['DOCUMENT_ROOT'].$v;
	}
	$PDF = new pdf1234();
	ob_end_clean();
	$PDF->getpdf
	(
		$arElement['TYPE'],
		array
		(
			'chief'       => iconv('UTF-8', 'windows-1251', $_POST['chief']),
			'fio'         => iconv('UTF-8', 'windows-1251', $_POST['fio']),
			'address'     => iconv('UTF-8', 'windows-1251', $_POST['address']),
			'street'      => iconv('UTF-8', 'windows-1251', $_POST['street']),
			'reason'      => iconv('UTF-8', 'windows-1251', $_POST['reason']),
			'date1.day'   => date('d', $arElement['DATE_CREATED']),
			'date1.month' => date('m', $arElement['DATE_CREATED']),
			'date1.year'  => date('y', $arElement['DATE_CREATED']),
			'date2.day'   => date('d'),
			'date2.month' => date('m'),
			'date2.year'  => date('y'),
			'date3.day'   => date('d'),
			'date3.month' => date('m'),
			'date3.year'  => date('y'),
			'signature'   => iconv('UTF-8', 'windows-1251', $_POST['signature'])
		),
		$arElement['pictures']['medium']['fresh']
	);
	die();
}

// форма заполнения данных для пдф
$arElement = C1234Hole::GetById($_GET['ID']);

?><form action="<?= $APPLICATION->GetCurPage() ?>" method="post" name="pdf_form"><?
$oTabControl = new CAdminTabControl
(
	'pdf',
	array
	(
		array
		(
			'DIV'   => 'edit1',
			'TAB'   => GetMessage('GREENSIGHT_TABPDF'),
			'ICON'  => 'main_user_edit',
			'TITLE' => GetMessage('GREENSIGHT_TABPDF_TITLE'),
		)
	)
);
$oTabControl->Begin();
$oTabControl->BeginNextTab();
?>
	<input type="hidden" name="ID" value="<?= $arElement['ID'] ?>">
	<tr valign="top">
		<td class="field-name" width="40%">
			<?=GetMessage('GREENSIGHT_FOR_CHEEF')?><br>
			<?=GetMessage('GREENSIGHT_UNIT')?>
		</td>
		<td>
			<textarea cols="40" rows="5" name="chief"></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td class="field-name" width="40%">
			<?=GetMessage('GREENSIGHT_FROM')?><br>
			<?=GetMessage('GREENSIGHT_YOUR_NAME')?>
		</td>
		<td>
			<textarea cols="40" rows="5" name="fio"></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td class="field-name" width="40%">
			<?=GetMessage('YOUR_MAIL')?><br>
			<?=GetMessage('PROMISE')?>
		</td>
		<td>
			<textarea cols="40" rows="5" name="address"></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td class="field-name" width="40%">
			<?= $arElement['~DATE_CREATED'] ?> <?=GetMessage('MESSAGE');?><br>
			<?=GetMessage('STREET_TITLE');?><br>
			<?=GetMessage('DEFECT');?>
		</td>
		<td>
			<textarea cols="40" rows="5" name="street"><?=GetMessage('STREET_ADDRES');?> <?= htmlspecialcharsEx($arElement['ADDRESS']) ?></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td class="field-name" width="40%">
			<?=GetMessage('YOUR_MIND')?>
		</td>
		<td>
			<textarea cols="40" rows="5" name="reason"></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td class="field-name" width="40%">
		<?=GetMessage('SIGNATURE')?>
		</td>
		<td>
			<input type="text" name="signature">
		</td>
	</tr>
<?
$oTabControl->Buttons(' ');
$oTabControl->EndTab();
$oTabControl->End();
?></form><?

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");


?>