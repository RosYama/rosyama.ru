<?

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);

if(isset($_GET['alarm']))
{
	$command = "php ".$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/st1234holes/admin/holes_alarm_run.php '".$_GET['email']."' '".$_SERVER['SERVER_NAME']."'";
	COption::SetOptionString('st1234holes', 'alarm_email', $_GET['email']);
	echo '<pre>';
	echo `$command`;
	echo '</pre>';
	die();
}

if(!CModule::IncludeModule('st1234holes'))
{
	die(':(');
}

$APPLICATION->SetTitle(GetMessage('GREENSIGHT_ST1234_ALARM_TITLE'));
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php');

$sTableID = 'b_holes';
$oSort  = new CAdminSorting($sTableID, $by, $order);
$lAdmin = new CAdminList($sTableID, $oSort);
$lAdmin->AddHeaders
(
	array
	(
		array
		(
			'id'      => 'ID',
			'name'    => 'ID',
			'content' => 'ID',
			'sort'    => 'ID',
			'default' => true
		),
		array
		(
			'id'      => 'USER_LOGIN',
			'name'    => 'USER_LOGIN',
			'content' => GetMessage('GREENSIGHT_ST1234_USER_LOGIN'),
			'sort'    => 'LOGIN',
			'default' => true
		),
		array
		(
			'id'      => 'COORDINATES',
			'name'    => 'COORDINATES',
			'content' => GetMessage('GREENSIGHT_ST1234_COORDINATES'),
			'default' => true
		),
		array
		(
			'id'      => 'DATE_CREATED',
			'name'    => 'DATE_CREATED',
			'content' => GetMessage('GREENSIGHT_ST1234_DATE_CREATED'),
			'sort'    => 'DATE_CREATED',
			'default' => true
		),
		array
		(
			'id'      => 'STATE',
			'name'    => 'STATE',
			'content' => GetMessage('GREENSIGHT_ST1234_STATE'),
			'sort'    => 'STATE',
			'default' => true
		),
		array
		(
			'id'      => 'ADDRESS',
			'name'    => 'ADDRESS',
			'content' => GetMessage('GREENSIGHT_ST1234_ADDRESS'),
			'default' => true
		),
		array
		(
			'id'      => 'TYPE',
			'name'    => 'TYPE',
			'content' => GetMessage('GREENSIGHT_ST1234_TYPE'),
			'sort'    => 'TYPE',
			'default' => true
		)
	)
);
$arFilter = array
(
	'STATE' => 'achtung'
);

$arSort = array();
if($by)
{
	$arSort[$by] = $order;
}

$page           = (int)$_GET['page'];
$holes_per_page = $_GET['hpp'] > 0 ? (int)$_GET['hpp'] : 50;
$pages_count    = 0;

$rsData = C1234Hole::GetList
(
	$arSort,
	$arFilter,
	array
	(
		'limit'  => $holes_per_page,
		'offset' => $holes_per_page * $page
	),
	&$pages_count
);

foreach($rsData as $arRes)
{
	$arRes['~STATE'] = GetMessage('GREENSIGHT_ST1234_STATE_'.$arRes['STATE']);
	$arRes['~TYPE']  = GetMessage('GREENSIGHT_ST1234_TYPE_'.$arRes['TYPE']);
	$row =& $lAdmin->AddRow();
	$row->AddViewField('ID',           $arRes['ID']);
	$row->AddViewField('USER_LOGIN',   $arRes['LOGIN']);
	$row->AddViewField('COORDINATES',  $arRes['LATITUDE'].','.$arRes['LONGITUDE']);
	$row->AddViewField('DATE_CREATED', $arRes['~DATE_CREATED']);
	$row->AddViewField('STATE',        $arRes['~STATE']);
	$row->AddViewField('ADDRESS',      $arRes['ADDRESS']);
	$row->AddViewField('TYPE',         $arRes['~TYPE']);
	$arActions = array
	(
		array
		(
			'ICON'    => 'edit',
			'DEFAULT' => true,
			'TEXT'    => GetMessage('GREENSIGHT_ST1234_EDIT'),
			'ACTION'  => $lAdmin->ActionRedirect('greensight_holes_edit.php?ID='.$arRes['ID'])
		),
		array
		(
			'ICON'    => 'delete',
			'DEFAULT' => false,
			'TEXT'    => GetMessage('GREENSIGHT_ST1234_DELETE'),
			'ACTION'  => $lAdmin->ActionRedirect('greensight_holes_edit.php?DELETE='.$arRes['ID'])
		),
		array
		(
			'ICON'    => '',
			'DEFAULT' => false,
			'TEXT'    => GetMessage('GREENSIGHT_ST1234_PRINT_PDF'),
			'ACTION'  => $lAdmin->ActionRedirect('greensight_holes_pdf.php?ID='.$arRes['ID'])
		)
	);
	$row->AddActions($arActions);
}

$pagination = '';
if($page - 10 > 0)
{
	$pagination .= '... ';
}
for($i = max(0, $page - 10); $i < $page; $i++)
{
	$pagination .= '<a href="?page='.$i.'&hpp='.$holes_per_page.'">'.($i + 1).'</a> ';
}
$pagination .= (++$i).' ';
for(; $i < min($pages_count, $page + 11); $i++)
{
	$pagination .= '<a href="?page='.$i.'&hpp='.$holes_per_page.'">'.($i + 1).'</a> ';
}
if($page < $pages_count - 10)
{
	$pagination .= '...';
}

$lAdmin->AddFooter
(
	array
	(
		array
		(
			'title' => GetMessage('GREENSIGHT_ST1234_HPP:'),
			'value' => '<input type="text" name="hpp" value="'.$holes_per_page.'" onkeyup="if(event.keyCode==13)document.location=\'?hpp=\'+this.value;"></form>'
		),
		array
		(
			'title' => GetMessage('GREENSIGHT_ST1234_PAGES:'),
			'value' => $pagination
		)
	)
);

$lAdmin->CheckListMode();
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<label for="alarm_email"><?= GetMessage('GREENSIGHT_ST1234_EMAIL_ACHTUNG_LIST') ?></label>
<input type="text" value="<?= COption::GetOptionString('st1234holes', 'alarm_email', 'rossyama@gmail.com') ?>" id="alarm_email">
<input type="button" value="<?= GetMessage('GREENSIGHT_ST1234_REFRESH_ACHTUNG_LIST') ?>" onclick="document.location='/bitrix/admin/greensight_holes_alarm.php?alarm&email='+document.getElementById('alarm_email').value">
<?

$lAdmin->DisplayList();

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

?>