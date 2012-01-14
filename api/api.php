<?php

ob_start();
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
CModule::IncludeModule('st1234hole');
global $USER;
while(ob_get_level())
{
	ob_end_clean();
}

// уберём хедеры
if(!headers_sent())
{
	$_headers = headers_list();
	foreach($_headers as $header)
	{
		$header = explode(':', $header);
		header_remove($header[0]);
	}
}

if
(
	$_SERVER['SERVER_NAME'] != 'xml.rosyama.ru' // промышленный сервер
	&& $_SERVER['SERVER_NAME'] != 'xml_st1234.greensight.ru' // центральный отладочный сервер
	&& $_SERVER['SERVER_NAME'] != 'xml-st1234.greensight.ru' // центральный отладочный сервер
	&& $_SERVER['SERVER_NAME'] != 'xml.st1234.local' // отладочный сервер
)
{
	die();
}

// и добавим хедер
header("Content-Type: text/xml; charset=utf8");

// определение режима вызова
$_path = explode('?', ltrim($_SERVER['REQUEST_URI'], '/'));
$_path[0] = explode('/', trim($_path[0], ' /'));
if($_SERVER['REQUEST_METHOD'] == 'POST' || $_POST)
{
	switch(ToLower($_path[0][0]))
	{
		case 'add':
		{
			// добавление ямы
			$mode = 'add';
			break;
		}
		case 'authorize':
		{
			// попытка авторизации
			$mode = 'authorize';
			break;
		}
		case 'checkauth':
		{
			// проверка авторизованности
			$mode = 'checkauth';
			break;
		}
		case 'exit':
		{
			// разлогиниться
			$mode = 'exit';
			break;
		}
		case 'geocode':
		{
			// геокодирование
			$mode = 'geocode';
			break;
		}
		case 'my':
		{
			if(!isset($_path[0][1]))
			{
				// список своих ям
				$mode = 'personal-holes-list';
			}
			elseif((int)$_path[0][1])
			{
				$hole_id = (int)$_path[0][1];
				switch($_path[0][2])
				{
					case 'delete':
					{
						// удаление ямы
						$mode = 'delete';
						break;
					}
					case 'getgibddhead':
					{
						// узнать ФИО начальника УГИБДД
						$mode = 'getgibddhead';
						break;
					}
					case 'getupdatemethods':
					{
						// возможные методы обновления ямы
						$mode = 'getupdmethods';
						break;
					}
					case 'pdf_gibdd':
					{
						// пдф с заявлением в ГИБДД
						$mode = 'pdf';
						break;
					}
					case 'pdf_prosecutor':
					{
						// пдф с заявлением в ГИБДД
						$mode = 'pdf';
						break;
					}
					case 'update':
					{
						// изменение ямы
						$mode = 'update-common';
						break;
					}
					case 'revoke':
					{
						// отзыв заявления из ГИБДД
						$mode = 'update-revoke';
						break;
					}
					case 'revokep':
					{
						// отзыв заявления из прокуратуры
						$mode = 'update-revokep';
						break;
					}
					case 'setfixed':
					{
						// изменение ямы - поставить статус "починен"
						$mode = 'update-setfixed';
						break;
					}
					case 'setinprogress':
					{
						// изменение ямы - поставить статус "в процессе"
						$mode = 'update-setinprogress';
						break;
					}
					case 'setreplied':
					{
						// изменение ямы - поставить статус "получен ответ из ГИБДД"
						$mode = 'update-setreplied';
						break;
					}
					case 'toprosecutor':
					{
						// изменение ямы - поставить статус "заявление отправлено в прокуратуру"
						$mode = 'update-toprosecutor';
						break;
					}
					default:
					{
						if($_path[0][2] != '')
						{
							// нереализованный метод
							$mode = 'not-implemented';
						}
						else
						{
							// карточка ямы
							$mode = 'personal-hole-cart';
						}
						break;
					}
				}
			}
			break;
		}
	}
}
else
{
	if($_path[0][0] == '')
	{
		// список ям
		$mode = 'holes-list';
	}
	elseif((int)$_path[0][0] != 0)
	{
		// карточка ямы
		$mode    = 'hole-cart';
		$hole_id = (int)$_path[0][0];
	}
	elseif(!isset($_path[0][1]))
	{
		switch(ToLower($_path[0][0]))
		{
			case 'getregions':
			{
				// список регионов
				$mode = 'getregions';
				break;
			}
			case 'getgibddheadbyregion':
			{
				// узнать ФИО начальника УГИБДД по ID региона
				$mode = 'getgibddheadbyregion';
				break;
			}	
				
			case 'exit':
			{
				// разлогиниться
				$mode = 'exit';
				break;
			}
			case 'getfileuploadlimits':
			{
				// получить предельные размеры и количество файлов, которые можно загрузить
				$mode = 'uplparams';
				break;
			}
			case 'getupdatemethods':
			{
				// возможные методы обновления дефекта
				$mode    = 'getupdmethods';
				$hole_id = 0;
				break;
			}
		}
	}
}

C1234HoleApi::Execute($mode, $hole_id);

?>