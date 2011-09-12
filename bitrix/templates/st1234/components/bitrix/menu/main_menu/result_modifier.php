<?php

if(strpos($APPLICATION->GetCurPage(), '/personal') === 0)
{
	foreach($arResult as &$item)
	{
		$item['SELECTED'] = false;
	}
}

?>