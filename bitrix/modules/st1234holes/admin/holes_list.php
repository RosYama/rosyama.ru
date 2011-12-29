<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule('st1234holes'))
{
	die(':(');
}

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
$arFilterFields = array
(
	'filter_id',
	'filter_user_id',
	'filter_user_login',
	'filter_latitude_from',
	'filter_latitude_to',
	'filter_longitude_from',
	'filter_longitude_to',
	'filter_date_created_from',
	'filter_date_created_to',
	'filter_state',
	'filter_type',
	'filter_address'
);
$lAdmin->InitFilter($arFilterFields);
$arFilter = array
(
	'ID'             => $filter_id,
	'USER_ID'        => $filter_user_id,
	'USER_LOGIN'     => $filter_user_login,
	'>LATITUDE'      => $filter_latitude_from,
	'<LATITUDE'      => $filter_latitude_to,
	'>LONGITUDE'     => $filter_longitude_from,
	'<LONGITUDE'     => $filter_longitude_to,
	'>DATE_CREATED'  => $filter_date_created_from,
	'<DATE_CREATED'  => $filter_date_created_to,
	'STATE'          => $filter_state,
	'ADDRESS'        => $filter_address,
	'TYPE'           => $filter_type
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
	$row->AddViewField('STATE',        $arRes['~STATE'].' '.$arRes['~DATE_STATUS']);
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

$APPLICATION->SetTitle(GetMessage('GREENSIGHT_ST1234_TITLE'));
if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$arFindFields = array
(
	'filter_user_id'      => GetMessage('GREENSIGHT_ST1234_filter_user_id'),
	'filter_user_login'   => GetMessage('GREENSIGHT_ST1234_filter_user_login'),
	'filter_latitude'     => GetMessage('GREENSIGHT_ST1234_filter_latitude'),
	'filter_longitude'    => GetMessage('GREENSIGHT_ST1234_filter_longitude'),
	'filter_date_created' => GetMessage('GREENSIGHT_ST1234_filter_date_created'),
	'filter_state'        => GetMessage('GREENSIGHT_ST1234_filter_state'),
	'filter_address'      => GetMessage('GREENSIGHT_ST1234_filter_address'),
	'filter_type'         => GetMessage('GREENSIGHT_ST1234_filter_type')
);

?>
<form method="GET" name="find_form" id="find_form" action="<?= $APPLICATION->GetCurPage() ?>">
<?
$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
$oFilter->Begin();
?>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_id') ?></td>
	<td><input type="text" name="filter_id" value="<?= $filter_id ?>"></td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_user_id') ?></td>
	<td><input type="text" name="filter_user_id" value="<?= $filter_user_id ?>"></td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_user_login') ?></td>
	<td><input type="text" name="filter_user_login" value="<?= $filter_user_login ?>"></td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_latitude') ?></td>
	<td>
		<input type="text" name="filter_latitude_from" value="<?= $filter_latitude_from ?>"> ...
		<input type="text" name="filter_latitude_to" value="<?= $filter_latitude_to ?>">
	</td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_longitude') ?></td>
	<td>
		<input type="text" name="filter_longitude_from" value="<?= $filter_longitude_from ?>"> ...
		<input type="text" name="filter_longitude_to" value="<?= $filter_longitude_to ?>">
	</td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_date_created') ?></td>
	<td>
		<input type="text" name="filter_date_created_from" value="<?= $filter_date_created_from ?>"> ...
		<input type="text" name="filter_date_created_to" value="<?= $filter_date_created_to ?>">
	</td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_state') ?></td>
	<td>
		<select name="filter_state">
			<option value=""<?=           $filter_state == ''           ? ' selected' : '' ?>></option>
			<option value="fresh"<?=      $filter_state == 'fresh'      ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_STATE_fresh') ?></option>
			<option value="inprogress"<?= $filter_state == 'inprogress' ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_STATE_inprogress') ?></option>
			<option value="fixed"<?=      $filter_state == 'fixed'      ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_STATE_fixed') ?></option>
			<option value="achtung"<?=    $filter_state == 'achtung'    ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_STATE_achtung') ?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_address') ?></td>
	<td><input type="text" name="filter_address" value="<?= $filter_address ?>"></td>
</tr>
<tr>
	<td><?= GetMessage('GREENSIGHT_ST1234_filter_type') ?></td>
	<td>
		<select name="filter_type">
			<option value=""<?=           $filter_state == ''           ? ' selected' : '' ?>></option>
			<option value="badroad"<?=    $filter_state == 'badroad'    ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_badroad') ?></option>
			<option value="holeonroad"<?= $filter_state == 'holeonroad' ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_holeonroad') ?></option>
			<option value="hatch"<?=      $filter_state == 'hatch'      ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_hatch') ?></option>
			<option value="crossing"<?=   $filter_state == 'crossing'   ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_crossing') ?></option>
			<option value="nomarking"<?=  $filter_state == 'nomarking'  ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_nomarking') ?></option>
			<option value="rails"<?=      $filter_state == 'rails'      ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_rails') ?></option>
			<option value="policeman"<?=  $filter_state == 'policeman'  ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_policeman') ?></option>
			<option value="fence"<?=      $filter_state == 'fence'      ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_fence') ?></option>
			<option value="holeinyard"<?= $filter_state == 'holeinyard' ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_holeinyard') ?></option>
			<option value="light"<?=      $filter_state == 'light'      ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_light') ?></option>
			<option value="snow"<?=       $filter_state == 'snow'       ? ' selected' : '' ?>><?= GetMessage('GREENSIGHT_ST1234_TYPE_snow') ?></option>
		</select>
	</td>
</tr>
<?
$oFilter->Buttons
(
	array
	(
		"table_id" => $sTableID,
		"url"      => $APPLICATION->GetCurPage(),
		"form"     => "find_form"
	)
);
$oFilter->End();

?></form><?

$lAdmin->DisplayList();

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

?>