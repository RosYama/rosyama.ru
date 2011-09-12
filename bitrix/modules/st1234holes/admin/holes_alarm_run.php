<?php

$path = explode('/', $_SERVER['PHP_SELF']);
$path = array_slice($path, 0, sizeof($path) - 4);
$path = implode('/', $path).'/php_interface/dbconn.php';
if(!file_exists($path))
{
	die("Cannot find dbconn.php\n");
}
require_once($path);
$cid = mysql_connect($DBHost, $DBLogin, $DBPassword);
if(!$cid)
{
	die(mysql_errno()." ".mysql_error()."\n");
}
if(!mysql_select_db($DBName, $cid))
{
	die(mysql_errno($cid)." ".mysql_error($cid)."\n");
}
mysql_query("set names utf8", $cid);
$sql_str = "select holes.*, U.`LOGIN`, U.`NAME`, U.`LAST_NAME`, U.`SECOND_NAME` from `b_holes` holes
	join `b_user` U on (U.`ID` = holes.`USER_ID`)
	where holes.`STATE` = 'inprogress'
	and holes.`DATE_SENT` < unix_timestamp() - 37 * 86400
	and !holes.`GIBDD_REPLY_RECEIVED`
	order by U.`ID` asc";
$res = mysql_query($sql_str, $cid);
if(!$res)
{
	die(mysql_errno($cid)." ".mysql_error($cid)."\n");
}
$user = 0;
$_holes = array();

$message_text =
'Здравствуйте. Это письмо сгенерировано автоматически и отвечать на него,
наверное, не нужно. Ниже приведён список пользователей и выложенных ими
дефектов, чей срок исправления после отправления уведомления в ГИБДД истёк.'."\n";

while($ar = mysql_fetch_assoc($res))
{
	$_holes[$ar['ID']] = $ar;
	if($ar['LOGIN'] != $user || !$user)
	{
		$message_text .= "\n".$ar['LAST_NAME'].' '.$ar['NAME'].' '.$ar['SECOND_NAME'].' ('.$ar['LOGIN'].'):'."\n";
		$user = $ar['LOGIN'];
	}
	$message_text .=
		$ar['ADDRESS'].' ('.round($ar['LATITUDE'], 2).' '.($ar['LATITUDE'] > 0 ? 'с' : 'ю').'.ш., '.round($ar['LONGITUDE'], 2).' '.($ar['LONGITUDE'] > 0 ? 'в' : 'з').'.д.), '
		.'дефект выложен на сайт '.date('Y.m.d H:i', $ar['DATE_CREATED']).', заявление отнесено в ГИБДД '
		.date('Y.m.d H:i', $ar['DATE_SENT']).'. http://'.htmlspecialchars($argv[2]).'/'.$ar['ID'].'/'."\n";
}
if(!$argv[1] || !preg_match('/[\w]([\w\-\.]*[\w]|)\@[\w]([\w\-\.]*[\w]|)\.[\w]{2,4}/', $argv[1]))
{
	die("Bad email\n");
}
if(sizeof($_holes))
{
	mail($argv[1], 'Просроченные дефекты от '.date('Y-m-d H:i'), $message_text);
	$sql_str = "update `b_holes` set `STATE` = 'achtung', `DATE_STATUS` = unix_timestamp() where `ID` in (".implode(',', array_keys($_holes)).")";
	if(!mysql_query($sql_str))
	{
		die(mysql_errno($cid)." ".mysql_error($cid)."\n");
	}
}
mysql_close($cid);
echo "Ok\n";

?>
