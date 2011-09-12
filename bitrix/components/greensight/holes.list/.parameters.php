<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array
(
	"PARAMETERS" => array
	(
		'HOLES_PER_PAGE' => array
		(
			'NAME' => GetMessage('HOLES_PER_PAGE')
		),
		'PREMODERATION' => array
		(
			'NAME' => GetMessage('PREMODERATION'),
			'TYPE' => 'CHECKBOX'
		),
		'MIN_DELAY_TIME' => array
		(
			'NAME' => GetMessage('MIN_DELAY_TIME')
		)
	)
);
?>