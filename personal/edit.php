<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Редактирование дефекта");

$APPLICATION->IncludeComponent("greensight:holes.addform", ".default", array(
	"ID"                 => $_REQUEST["ID"],
	"FIX_ID"             => $_REQUEST["FIX_ID"],
	"SENT_ID"            => $_REQUEST["SENT_ID"],
	"DELETE_ID"          => $_REQUEST["DEL_ID"],
	"CANCEL_ID"          => $_REQUEST["CANCEL_ID"],
	"REFIX_ID"           => $_REQUEST["REFIX_ID"],
	"GIBDD_REPLY_ID"     => $_REQUEST['GIBDD_REPLY_ID'],
	"PROSECUTOR_ID"      => $_REQUEST['PROSECUTOR_ID'],
	"REPROSECUTOR_ID"    => $_REQUEST['REPROSECUTOR_ID'],
	"PREMODERATE_ID"     => $_REQUEST['PREMODERATE_ID'],
	'PREMODERATE_ALL'    => $_REQUEST['PREMODERATE_ALL'],
	'DELETE_ALL'         => $_REQUEST['DELETE_ALL'],
	'DELETE_GIBDDRE_IMG' => $_REQUEST['DELETE_GIBDDRE_IMG'],
	"MEDIUM_SIZEX"       => "600",
	"MEDIUM_SIZEY"       => "450",
	"SMALL_SIZEX"        => "240",
	"SMALL_SIZEY"        => "160"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>