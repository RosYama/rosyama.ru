<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление дефекта");

if($_GET['login'] == 'yes')
{
	LocalRedirect('/personal/add.php');
	die();
}

$APPLICATION->IncludeComponent("greensight:holes.addform", ".default", array(
	"MEDIUM_SIZEX" => "600",
	"MEDIUM_SIZEY" => "450",
	"SMALL_SIZEX" => "240",
	"SMALL_SIZEY" => "160"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>