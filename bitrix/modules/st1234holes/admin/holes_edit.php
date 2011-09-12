<?

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule('st1234holes'))
{
	die(':(');
}

$APPLICATION->SetTitle(GetMessage('GREENSIGHT_ST1234_TITLE'));
if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php');

if(isset($_GET['DELETE']))
{
	// удаление ямы
	C1234Hole::Delete($_GET['DELETE']);
	LocalRedirect('/bitrix/admin/greensight_holes_list.php');
	die();
}
elseif($_POST['ID'])
{
	list($latitude, $longitude) = explode(',', $_POST['COORD']);
	// редактирование ямы
	if(!C1234Hole::Update
	(
		$_POST['ID'],
		array
		(
			'TYPE'      => $_POST['TYPE'],
			'STATE'     => $_POST['STATE'],
			'USER_ID'   => $_POST['USER_ID'],
			'LATITUDE'  => $latitude,
			'LONGITUDE' => $longitude,
			'ADDRESS'   => $_POST['ADDRESS'],
			'COMMENT1'  => $_POST['COMMENT1'],
			'COMMENT2'  => $_POST['COMMENT2']
		),
		array(),
		&$error
	))
	{
		ShowError($error);
	}
	else
	{
		if($_POST['apply'])
		{
			LocalRedirect('/bitrix/admin/greensight_holes_edit.php?ID='.(int)$_POST['ID']);
		}
		else
		{
			LocalRedirect('/bitrix/admin/greensight_holes_list.php');
		}
		die();
	}
}

// форма редактирования
$arElement = C1234Hole::GetById($_GET['ID']);

?><form action="<?= $APPLICATION->GetCurPage() ?>" method="post" name="hole_edit"><?
$oTabControl = new CAdminTabControl
(
	'b_holes',
	array
	(
		array
		(
			'DIV'   => 'edit1',
			'TAB'   => GetMessage('GREENSIGHT_TAB1'),
			'ICON'  => 'main_user_edit',
			'TITLE' => GetMessage('GREENSIGHT_TAB1_TITLE'),
		)
	)
);
$oTabControl->Begin();
$oTabControl->BeginNextTab();
?>
	<tr>
		<td width="40%" class="field-name">ID:</td>
		<td><?= $arElement['ID'] ?><input type="hidden" name="ID" value="<?= $arElement['ID'] ?>"></td>
	</tr>
	<tr>
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_USER_ID') ?>:</td>
		<td>
			<input type="text" value="<?= $arElement['USER_ID'] ?>" id="user_id" name="USER_ID">
			<input type="button" onclick="jsUtils.OpenWindow('/bitrix/admin/user_search.php?FN=hole_edit&FC=user_id', 600, 500);" value="...">
		</td>
	</tr>
	<tr>
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_STATE') ?>:</td>
		<td>
			<select id="state" name="STATE">
				<option value="fresh"      <?= $arElement['STATE'] == 'fresh'      ? ' selected' : ''?>><?= GetMessage('GREENSIGHT_ST1234_STATE_fresh') ?></option>
				<option value="inprogress" <?= $arElement['STATE'] == 'inprogress' ? ' selected' : ''?>><?= GetMessage('GREENSIGHT_ST1234_STATE_inprogress') ?></option>
				<option value="foxed"      <?= $arElement['STATE'] == 'fixed'      ? ' selected' : ''?>><?= GetMessage('GREENSIGHT_ST1234_STATE_fixed') ?></option>
				<option value="achtung"    <?= $arElement['STATE'] == 'achtung'    ? ' selected' : ''?>><?= GetMessage('GREENSIGHT_ST1234_STATE_achtung') ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_TYPE') ?>:</td>
		<td>
			<select id="type" name="TYPE">
				<?
				$types = array('badroad', 'holeonroad', 'policeman', 'rails',
					'crossing', 'nomarking', 'fence', 'holeinyard',
					'light', 'hatch');
				foreach($types as $t)
				{
					echo '<option value="'.$t.'"'.($arElement['TYPE'] == $t ? ' selected' : '').'>'.GetMessage('GREENSIGHT_ST1234_TYPE_'.$t).'</option>';
				}
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_COORD') ?>:</td>
		<td>
			<?
			CIBlockPropertyMapYandex::GetPropertyFieldHtml
			(
				array
				(
					'ID'        => 'COORD',
					'NAME'      => 'COORD',
					'ACTIVE'    => 'Y',
					'CODE'      => 'COORD',
					'MULTIPLE'  => 'N',
					'USER_TYPE' => 'map_yandex',
					'USER_TYPE_SETTINGS' => ''
				),
				array
				(
					'VALUE' => $arElement['LATITUDE'].','.$arElement['LONGITUDE']
				),
				array
				(
					'VALUE'    => 'COORD',
					'FORM_NAM' => 'hole_edit',
					'MODE'     => 'FORM_FILL'
				)
			);
			?>
		</td>
	</tr>
	<tr valign="top">
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_ADDRESS') ?>:</td>
		<td>
			<textarea rows="10" cols="50" id="address" name="ADDRESS"><?= htmlspecialcharsEx($arElement['ADDRESS']) ?></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_COMMENT1') ?>:</td>
		<td>
			<textarea rows="10" cols="50" id="comment1" name="COMMENT1"><?= htmlspecialcharsEx($arElement['COMMENT1']) ?></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td width="40%" class="field-name"><?= GetMessage('GREENSIGHT_FIELDS_COMMENT2') ?>:</td>
		<td>
			<textarea rows="10" cols="50" id="comment2" name="COMMENT2"><?= htmlspecialcharsEx($arElement['COMMENT2']) ?></textarea>
		</td>
	</tr>
	<tr>
		<td width="40%" class="field-name"></td>
		<td>
			<input type="button" onclick="document.location='/bitrix/admin/greensight_holes_pdf.php?ID=<?= $arElement['ID'] ?>';" value="<?= GetMessage('GREENSIGHT_ST1234_PRINT_PDF') ?>">
		</td>
	</tr>
<?
$oTabControl->Buttons(' ');
$oTabControl->EndTab();
$oTabControl->End();
?></form><?

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

?>