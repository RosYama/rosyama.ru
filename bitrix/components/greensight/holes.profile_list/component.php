<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('st1234holes'))
{
	die();
}

global $USER;
$arResult['HOLES'] = array();

$_holes = C1234Hole::GetList
(
	array
	(
		'ID' => 'desc'
	),
	array
	(
		'USER_ID' => $USER->GetID()
	)
);
foreach($_holes as &$hole)
{
	switch($hole['STATE'])
	{
		
		case 'inprogress':
		{
			if($hole['DATE_SENT'])
			{
				$hole['WAIT_DAYS'] = 38 - ceil((time() - $hole['DATE_SENT']) / 86400);
				$hole['WAIT_DAYS'] = GetMessage('WAIT').' '.(string)$hole['WAIT_DAYS'];
				$last_digit = (int)substr($hole['WAIT_DAYS'], strlen($hole['WAIT_DAYS']) - 1);
				if(substr($hole['WAIT_DAYS'], strlen($hole['WAIT_DAYS']) - 2, 1) == '1')
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 4 || !$last_digit)
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 1)
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAYS2');
				}
				else
				{
					$hole['WAIT_DAYS'] .= ' '.GetMessage('DAY');
				}
			}
			$arResult['HOLES']['INPROGRESS'][] = $hole;
			break;
		}
		case 'achtung':
		{
			if($hole['DATE_SENT'])
			{
				$hole['PAST_DAYS'] = GetMessage('PAST').' '.(string)(ceil((time() - $hole['DATE_SENT']) / 86400) - 37);
				$last_digit = (int)substr($hole['PAST_DAYS'], strlen($hole['PAST_DAYS']) - 1);
				if(substr($hole['PAST_DAYS'], strlen($hole['PAST_DAYS']) - 2, 1) == '1')
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 4 || !$last_digit)
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAYS5');
				}
				elseif($last_digit > 1)
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAYS2');
				}
				else
				{
					$hole['PAST_DAYS'] .= ' '.GetMessage('DAY');
				}
			}
			$arResult['HOLES']['INPROGRESS'][] = $hole;
			break;
		}
		case 'fixed':
		{
			$arResult['HOLES']['FIXED'][] = $hole;
			break;
		}
		case 'gibddre':
		case 'prosecutor':
		{
			$arResult['HOLES']['INPROGRESS'][] = $hole;
			break;
		}
		case 'fresh':
		default:
		{
			$arResult['HOLES']['FRESH'][] = $hole;
			break;
		}
	}
}

$this->IncludeComponentTemplate();

?>