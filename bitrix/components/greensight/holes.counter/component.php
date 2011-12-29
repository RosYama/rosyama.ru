<?php

if(!CModule::IncludeModule('st1234holes'))
{
	return;
}

$arResult['facts'] = 0;
$arResult['fixed'] = 0;
$arResult['gibddre'] = 0;

$arResult = C1234Hole::GetCounts();

$s = (string)$arResult['facts'];
if($s[strlen($s) - 2] == 1) // 10, 11, ... 19
{
	$arResult['ending1'] = 2;
}
elseif($s[strlen($s) - 1] == 1) // 1
{
	$arResult['ending1'] = 0;
}
elseif($s[strlen($s) - 1] > 1 && $s[strlen($s) - 1] < 5) // 2,3,4
{
	$arResult['ending1'] = 1;
}
else
{
	$arResult['ending1'] = 2;
}
$s = (string)$arResult['fixed'];
if($s[strlen($s) - 2] == 1) // 10, 11, ... 19
{
	$arResult['ending2'] = 2;
}
elseif($s[strlen($s) - 1] == 1) // 1
{
	$arResult['ending2'] = 0;
}
elseif($s[strlen($s) - 1] > 1 && $s[strlen($s) - 1] < 5) // 2,3,4
{
	$arResult['ending2'] = 1;
}
else
{
	$arResult['ending2'] = 2;
}

$this->IncludeComponentTemplate();

?>