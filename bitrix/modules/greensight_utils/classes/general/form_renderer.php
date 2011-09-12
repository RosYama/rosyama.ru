<?php

/**
 * Класс простенького рендеринга форм в шаблонах на основе аррезалта.
 *
 * 22 02 2011
 *
 * Прямо тут напишу описание массива, который переваривается этим классом.
 * $_data = array(
 *   'FIELDS'  => <список полей>,
 *   'ID'      => id формы
 *   'METHOD'  => method формы
 *   'ENCTYPE' => enctype формы
 *   'ACTION'  => action формы
 *   'LEGEND'  => что там сверху написано будет
 * )
 * Список полей = array(
 *   array( <описание поля> ),
 *   array( <описание поля> ),
 *   ...
 * )
 * Описание поля = array(
 *   'ID'        => айдишник поля
 *   'NAME'      => имя поля
 *   'LABEL'     => текстовая подпись на человечьем языке
 *   'TYPE'      => тип. понимаются адекватно следующие:
 *   	text, textarea, file, select, submit, hidden, button
 *   	textarray                 - это комбинированная пижня, отрисовывающая массив с ключами и значениями,
 *   	date                      - дата
 *   	bind_iblock_element1      - через одно место организованная привязка к элементам инфоблоков (без возможности создать новый прямо так)
 *   	bind_iblock_element2      - привязка к элементам инфоблоков (с возможностью создать новый прямо так)
 *   	multibind_iblock_element1 - через одно место множественна организованная привязка к элементам инфоблоков (без возможности создать новый прямо так)
 *   	multibind_iblock_element2 - множественная привязка к элементам инфоблоков (с возможностью создать новый прямо так)
 *   	bind_forum_topic1         - привязка к теме форума
 *   	bind_forum_topic2         - привязка к теме форума (с возможностью создать новую)
 *   	bind_user                 - привязка к пользователю
 *   	multibind_user            - множественная привязка к пользователям
 *   	selectplus                - селект с возможностью создать новый элемент
 *   	yandexmap                 - привязка к Яндекс-карте
 *   	multifile                 - множественная загрузка файлов
 *   	все прочие трактуются как text
 *   'REQUIRED'   => отмечается как обязательное
 *   'VALUE'      => значение
 *   'DISABLED'   => если true, то поле будет дизабленным
 *   'CLASS'      => имя класса
 *   'ONCLICK'    => то, что происходит при онклике
 *   'ONHOVER'    => -/- onMouseOver
 *   'ONHOUT'     => -/- onMouseOut
 *   'ONCHANGE'   => -/- onChange
 *   'IBLOCK_ID'  => только для типов bind_iblock_* - привязка к инфоблоку
 *   'FIELDS'     => то же, что и FIELDS уровнем выше для складывания полей в свойства поля. Применяется с некоторыми типами
 *   'ITEMS'      => элементы для селекта в виде массива в формате значение=>надпись
 *   'FORUMS'     => список форумов для селекта в bind_forum_topic2
 *   'MAP_CENTER' => центр карты (коррдината1,координата2) - только для карт
 *   'FORUM_ID'   => номер форума (для привязки к теме форума)
 *   'WCLASS'     => дополнительное имя класса для дива-враппера поля
 *   'WSTYLE'     => содержимое свойства style для дива-враппера
 *   'SPAN_VALUE' => содержимое спана рядом с инпутом (для привязок)
 *   
 * )
 * 
 */

class CFormRenderer
{
	protected static $formname;
	
	/**
	 * Запустить рендеринг формы.
	 * @param  array  $_data массив, котрый должен быть правильно сформирован
	 * @return string HTML-текст
	 */
	public static function RenderForm($_data)
	{
		CFormRenderer::$formname = $_data['ID'];
		$result = '<fieldset>
			<form
				id="'.$_data['ID'].'"
				name="'.CFormRenderer::$formname.'"
				action="'.$_data['ACTION'].'"'
				.($_data['ENCTYPE'] ? ' enctype="'.$_data['ENCTYPE'].'"' : '')
				.' method="'.($_data['METHOD'] ? $_data['METHOD'] : 'get').'">'
				.($_data['LEGEND'] ? '<legend>'.$_data['LEGEND'].'</legend>' : '');
		$result .= CFormRenderer::RenderFields($_data);
		$result .= '</form></fieldset>';
		return $result;
	}
	
	/**
	 * Отрисовать одно поле.
	 * @param  array  $_field массив с описанием поля
	 * @return string HTML-текст
	 */
	public static function RenderField($_field)
	{
		if(ToLower($_field['TYPE']) != 'hidden')
		{
			$result = '<div class="rf_fwrapper_o '.$_field['WCLASS'].'" '
				.($_field['WSTYLE'] ? 'style="'.$_field['WSTYLE'].'" ' : '')
				.'id="rf_wrapper_o_'.$_field['ID'].'">
				<div class="rf_lwrapper" id="rf_lwrapper_'.$_field['ID'].'">
					<label for="'.$_field['ID'].'" id="rf_label_'.$_field['ID'].'">'.$_field['LABEL'].'</label>
					'.($_field['REQUIRED'] ? '<span class="rf_required">*</span>' : '').'
				</div>
				<div class="rf_fwrapper_i" id="rf_fwrapper_i_'.$_field['ID'].'">';
		}
		else
		{
			$result = '';
		}
		// общая строка для большинства элементов
		$field = 'name="'.$_field['NAME'].'" id="'.$_field['ID'].'"'
			.($_field['DISABLED'] ? ' disabled'                              : '')
			.($_field['CLASS']    ? ' class="'.      $_field['CLASS'].'"'    : '')
			.($_field['ONCLICK']  ? ' onclick="'.    $_field['ONCLICK'].'"'  : '')
			.($_field['ONHOVER']  ? ' onmouseover="'.$_field['ONHOVER'].'"'  : '')
			.($_field['ONCHANGE'] ? ' onchange="'.   $_field['ONCHANGE'].'"' : '')
			.($_field['ONHOUT']   ? ' onmouseout="'. $_field['ONHOUT'].'"'   : '');
		// типы почти по алфавиту
		switch(ToLower($_field['TYPE']))
		{
			// привязка к теме форума с возможностью создать новую
			case 'bind_forum_topic2':
			{
				$result .= '<div class="rf_selectforum">
					<select id="'.$_field['ID'].'_selectforum" name="'.$_field['NAME'].'_selectforum">
					<option value="" selected></option>';
				foreach($_field['FORUMS'] as $k => $v)
				{
					$result .= '<option value="'.$k.'">'.$v.'</option>';
				}
				$result .= '</select></div>
					<div class="rf_createtopic">
						<div class="rf_inner_label" id="rf_inner_label_'.$_field['ID'].'">
							<label for="'.$_field['ID'].'_createtopic">Название темы форума</label>
						</div>
						<div class="rf_inner_input" id="rf_inner_input_'.$_field['ID'].'">
							<input type="text" id="'.$_field['ID'].'_createtopic" name="'.$_field['NAME'].'_createtopic">
						</div>
					</div>';
				// обратите внимание - тут нет брейка, следом должен идти bind_forum_topic1
			}
			
			// привязка к теме форума
			case 'bind_forum_topic1':
			{
				$result .= '<input type="text" '.$field.' value="'.$_field['VALUE'].'">
					<input type="button" onclick="jsUtils.OpenWindow(\'/bitrix/admin/forum_topics_search_gs.php?FC='.$_field['ID'].($_field['FORUM_ID'] ? '&FORUM_ID='.$_field['FORUM_ID'] : '').'\', 600, 500);" value="...">';
				break;
			}
			
			// привязка к элементу инфоблока с возможностью создать новый
			case 'bind_iblock_element2':
			{
				$result .= '<span class="rf_command" onclick="document.getElementById(\''.$_field['ID'].'_creatediv\').style.display=\'block\';">Создать</span>
					<div class="rf_hidden" id="'.$_field['ID'].'_creatediv">';
				$result .= CFormRenderer::RenderFields($_field);
				$result .= '</div>';
				// обратите внимание - тут нет брейка, следом должен идти bind_iblock_element1
			}
			
			// привязка к элементу инфоблока
			case 'bind_iblock_element1':
			{
				$result .= '<input type="text" '.$field.' value="'.$_field['VALUE'].'">
					<span id="sp_'.md5($_field['ID']).'_text">'.$_field['SPAN_VALUE'].'</span>
					<input type="button" onclick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?IBLOCK_ID='.(int)$_field['IBLOCK_ID'].'&n='.$_field['ID'].'&k=text\', 600, 500);" value="...">';
				break;
			}
			
			// привязка к пользователю
			case 'bind_user':
			{
				$result .= '<input type="text" '.$field.' value="'.$_field['VALUE'].'">
					<span id="div_sp_'.md5($_field['ID']).'_text">'.$_field['SPAN_VALUE'].'</span>
					<input type="button" onclick="jsUtils.OpenWindow(\'/bitrix/admin/user_search.php?FN='.CFormRenderer::$formname.'&FC='.$_field['ID'].'\', 600, 500);" value="...">';
				// немного говнеца в стиле битрикса
				$result .= '<iframe id="rf_iframe_'.$_field['ID'].'" style="width: 0; height: 0; border: none;"></iframe>
					<script type="text/javascript">
					var prev_'.$_field['ID'].'_val = \''.$_field['VALUE'].'\';
					function rf_input_'.$_field['ID'].'_check()
					{
						var val = document.getElementById(\''.$_field['ID'].'\').value;
						if(val != prev_'.$_field['ID'].'_val)
						{
							document.getElementById("rf_iframe_'.$_field['ID'].'").src=\'/bitrix/admin/get_user.php?ID=\'
								+ val + \'&strName=sp_'.md5($_field['ID']).'_text&lang=ru&admin_section=Y\';
							prev_'.$_field['ID'].'_val = val;
						}
					}
					setInterval(function() { rf_input_'.$_field['ID'].'_check(); }, 1000);
					</script>';
				break;
			}
			
			// кнопарь
			case 'button':
			{
				$result .= '<input type="button" '.$field.' value="'.$_field['VALUE'].'">';
				break;
			}
			
			// обычный текстовый инпут с календариком
			case 'date':
			{
				ob_start();
				global $APPLICATION;
				$APPLICATION->IncludeComponent
				(
					"bitrix:main.calendar",
					"",
					array
					(
						"SHOW_INPUT"         => "Y",
						"FORM_NAME"          => CFormRenderer::$formname,
						"INPUT_NAME"         => $_field['NAME'],
						"INPUT_NAME_FINISH"  => "",
						"INPUT_VALUE"        => $_field['VALUE'],
						"INPUT_VALUE_FINISH" => "", 
						"SHOW_TIME"          => "N",
						"HIDE_TIMEBAR"       => "N"
					)
				);
				$result .= ob_get_clean();
				/*$result .= '<input type="text" '.$field.' value="'.htmlspecialchars($_field['VALUE']).'">'
					.Calendar($_field['NAME'], CFormRenderer::$formname);*/
				break;
			}
			
			// файл
			case 'file':
			{
				$result .= '<input type="file" '.$field.'>';
				break;
			}
			
			// хидден
			case 'hidden':
			{
				$result .= '<input type="hidden" '.$field.' value="'.htmlspecialcharsEx($_field['VALUE']).'">';
				break;
			}
			
			// множественная привязка к элементу инфоблока
			case 'multibind_iblock_element1':
			{
				$max_i = 10;
				if(is_array($_field['VALUE']))
				{
					$max_i = sizeof($_field['VALUE']) + 10;
				}
				for($i = 0; $i < $max_i; $i++)
				{
					$result .= '<div class="rf_multielementbind_wrapper'.($i && !$_field['VALUE'][$i] ? ' rf_hidden' : '').'" id="multielementbind_wrapper_'.$_field['ID'].'_'.$i.'">';
					$result .= '<input type="text" '
						.'name="'.$_field['NAME'].'_'.$i.'" id="'.$_field['ID'].'_'.$i.'"'
						.' value="'.$_field['VALUE'][$i].'">
						<span id="sp_'.md5($_field['ID']).'_text">'.$_field['SPAN_VALUE'][$i].'</span>
						<input type="button" onclick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID='.(int)$_field['IBLOCK_ID'].'&n='.$_field['ID'].'_'.$i.'&k=text\', 600, 500);" value="...">'
						.($i == $max_i - 1 ? '' : '<span class="rf_command" onclick="document.getElementById(\'multielementbind_wrapper_'.$_field['ID'].'_'.($i + 1).'\').style.display = \'block\';this.style.display=\'none\';">Ещё...</span>')
						.'</div>';
				}
				break;
			}
			
			// множественная привязка к элементу инфоблока с возможностью создать новый
			case 'multibind_iblock_element2':
			{
				$max_i = 10;
				if(is_array($_field['VALUE']))
				{
					$max_i = sizeof($_field['VALUE']) + 10;
				}
				for($i = 0; $i < $max_i; $i++)
				{
					$result .= '<div class="rf_multielementbind_wrapper'.($i && !$_field['VALUE'][$i] ? ' rf_hidden' : '').'" id="multielementbind_wrapper_'.$_field['ID'].'_'.$i.'">
						<span class="rf_command" onclick="document.getElementById(\''.$_field['ID'].'_creatediv_'.$i.'\').style.display=\'block\';">Создать</span>
						<div class="rf_hidden" id="'.$_field['ID'].'_creatediv_'.$i.'">';
					$result .= CFormRenderer::RenderFields($_field, 'sub_', '_'.$i);
					$result .= '</div>';
					$result .= '<input type="text" '
						.'name="'.$_field['NAME'].'_'.$i.'" id="'.$_field['ID'].'_'.$i.'"'
						.' value="'.$_field['VALUE'][$i].'">
						<span id="sp_'.md5($_field['ID'].'_'.$i).'_text">'.$_field['SPAN_VALUE'][$i].'</span>
						<input type="button" onclick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID='.(int)$_field['IBLOCK_ID'].'&n='.$_field['ID'].'_'.$i.'&k=text\', 600, 500);" value="...">'
						.($i == $max_i - 1 ? '' : '<span class="rf_command" onclick="document.getElementById(\'multielementbind_wrapper_'.$_field['ID'].'_'.($i + 1).'\').style.display = \'block\';this.style.display=\'none\';">Ещё...</span>')
						.'</div>';
				}
				break;
			}
			
			// множественная привязка к пользователям
			case 'multibind_user':
			{
				$max_i = 5;
				if(is_array($_field['VALUE']))
				{
					$max_i = sizeof($_field['VALUE']) + 5;
				}
				for($i = 0; $i < $max_i; $i++)
				{
					$result .= '<div class="rf_multielementbind_wrapper'.($i && !$_field['VALUE'][$i] ? ' rf_hidden' : '').'" id="multiuserbind_wrapper_'.$_field['ID'].'_'.$i.'">'
						.'<input type="text" '
						.'name="'.$_field['NAME'].'_'.$i.'" id="'.$_field['ID'].'_'.$i.'"'
						.' value="'.$_field['VALUE'][$i].'">
						<span id="div_sp_'.md5($_field['ID'].'_'.$i).'_text">'.$_field['SPAN_VALUE'][$i].'</span>
						<input type="button" onclick="jsUtils.OpenWindow(\'/bitrix/admin/user_search.php?FN='.CFormRenderer::$formname.'&FC='.$_field['ID'].'_'.$i.'\', 600, 500);" value="...">'
						.(
							// много говнеца в стиле битрикса
							'<iframe id="rf_iframe_'.$_field['ID'].'_'.$i.'" style="width: 0; height: 0; border: none;"></iframe>
							<script type="text/javascript">
								var prev_'.$_field['ID'].'_'.$i.'_val = \''.$_field['VALUE'][$i].'\';
								function rf_input_'.$_field['ID'].'_'.$i.'_check()
								{
									var val = document.getElementById(\''.$_field['ID'].'_'.$i.'\').value;
									if(val != prev_'.$_field['ID'].'_'.$i.'_val)
									{
										document.getElementById("rf_iframe_'.$_field['ID'].'_'.$i.'").src=\'/bitrix/admin/get_user.php?ID=\'
											+ val + \'&strName=sp_'.md5($_field['ID'].'_'.$i).'_text&lang=ru&admin_section=Y\';
										prev_'.$_field['ID'].'_'.$i.'_val = val;
									}
								}
								setInterval(function() { rf_input_'.$_field['ID'].'_'.$i.'_check(); }, 1000);
							</script>'
						)
						.($i == $max_i - 1 ? '' : '<span class="rf_command" onclick="document.getElementById(\'multiuserbind_wrapper_'.$_field['ID'].'_'.($i + 1).'\').style.display = \'block\';this.style.display=\'none\';">Ещё...</span>')
						.'</div>';
				}
				break;
			}
			
			// множественные файлы
			case 'multifile':
			{
				$result .= '<div id="multifile_wrapper_'.$_field['ID'].'_0" class="rf_multifile_wrapper">
					<input
					type="file"
					id="'.$_field['ID'].'_0"
					name="'.$_field['NAME'].'_0"> <span class="rf_command" onclick="document.getElementById(\'multifile_wrapper_'.$_field['ID'].'_1\').style.display=\'block\';">Ещё</span></div>';
				for($i = 1; $i < 5; $i++)
				{
					$result .= '<div id="multifile_wrapper_'.$_field['ID'].'_'.$i.'" class="rf_multifile_wrapper rf_hidden">
						<input
						type="file"
						id="'.$_field['ID'].'_'.$i.'"
						name="'.$_field['NAME'].'_'.$i.'">';
					if($i < 4)
					{
						$result .= '<span class="rf_command" onclick="document.getElementById(\'multifile_wrapper_'.$_field['ID'].'_'.($i + 1).'\').style.display=\'block\';">Ещё</span>';
					}
					$result .= '</div>';
				}
				break;
			}
			
			// селект с возможностью создать новый элемент
			case 'selectplus':
			{
				$result .= '<div class="rf_hidden" id="'.$_field['ID'].'_creatediv">
					<input type="text" id="'.$_field['ID'].'_addnew" name="'.$_field['NAME'].'_addnew">
					</div>
					<span class="rf_command" onclick="document.getElementById(\''.$_field['ID'].'_creatediv\').style.display=\'block\';">Добавить элемент</span>';
				// обратите внимание на отсутствие брейка - далене должен идти case 'select'
			}
			
			// селект
			case 'select':
			{
				$result .= '<select '.$field.'>
					<option value=""'.($_field['VALUE'] == '' ? ' selected' : '').'></option>';
				foreach($_field['ITEMS'] as $k => $v)
				{
					$result .= '<option value="'.$k.'"'.($_field['VALUE'] == $k ? ' selected' : '').'>'.$v.'</option>';
				}
				$result .= '</select>';
				break;
			}
			
			// сабмит
			case 'submit':
			{
				$result .= '<input type="submit" '.$field.' value="'.$_field['VALUE'].'">';
				break;
			}
			
			// текстареа
			case 'textarea':
			{
				$result .= '<textarea '.$field.' rows="10" cols="60">'.htmlspecialchars($_field['VALUE']).'</textarea>';
				break;
			}
			
			// массив текстовых полей с ключами
			case 'textarray':
			{
				if(is_array($_field['VALUE']))
				{
					$sizeof = sizeof($_field['VALUE']);
				}
				else
				{
					$_field['VALUE'] = array(array('' => ''));
					$sizeof = 5;
				}
				$result .= '<table id="'.$_field['ID'].'_table">';
				$i = 0;
				foreach($_field['VALUE'] as &$v)
				{
					$result .= '<tr id="'.$_field['ID'].'_tr_'.$i.'">
						<td><input type="text" class="'.$_field['CLASS'].' rf_textarray_key"   name="'.$_field['ID'].'_key['.$i.']"   id="'.$_field['ID'].'_key_'.$i.'" value="'.htmlspecialchars($k).'"></td>
						<td><input type="text" class="'.$_field['CLASS'].' rf_textarray_value" name="'.$_field['ID'].'_value['.$i.']" id="'.$_field['ID'].'_key_'.$i.'" value="'.htmlspecialchars($v).'"></td>
						</tr>';
					$i++;
				}
				// тут немножко говнеца
				$result .= '</table>
					<script type="text/javascript">
						var last'.$_field['ID'].'_counter = "'.$i.'";
					</script>
					<span class="rf_command" onclick="
						var newtr = document.createElement(\'tr\');
						newtr.id  = \''.$_field['ID'].'_tr_\' + last'.$_field['ID'].'_counter;
						document.getElementById(\''.$_field['ID'].'_table\').appendChild(newtr);
						var newinp       = document.createElement(\'input\');
						newinp.type      = \'text\';
						newinp.className = \''.$_field['CLASS'].' rf_textarray_key\';
						newinp.id        = \''.$_field['ID'].'_key_\' + last'.$_field['ID'].'_counter;
						newinp.name      = \''.$_field['ID'].'_key[\' + last'.$_field['ID'].'_counter + \']\';
						var newtd        = document.createElement(\'td\');
						newtr.appendChild(newtd);
						newtd.appendChild(newinp);
						newinp           = document.createElement(\'input\');
						newinp.type      = \'text\';
						newinp.className = \''.$_field['CLASS'].' rf_textarray_key\';
						newinp.id        = \''.$_field['ID'].'_value_\' + last'.$_field['ID'].'_counter;
						newinp.name      = \''.$_field['ID'].'_value[\' + last'.$_field['ID'].'_counter + \']\';
						newtd            = document.createElement(\'td\');
						newtr.appendChild(newtd);
						newtd.appendChild(newinp)
						last'.$_field['ID'].'_counter++;
					">Ещё</span>';
				break;
			}
			
			// привязка к Яндекс-карте
			case 'yandexmap':
			{
				require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/properties.php');
				ob_start();
				CIBlockPropertyMapYandex::GetPropertyFieldHtml
				(
					array
					(
						'ID'        => $_field['ID'],
						'NAME'      => $_field['LABEL'],
						'ACTIVE'    => 'Y',
						'CODE'      => $_field['NAME'],
						'MULTIPLE'  => 'N',
						'USER_TYPE' => 'map_yandex',
						'USER_TYPE_SETTINGS' => ''
					),
					array
					(
						'VALUE' => $_field['VALUE']
					),
					array
					(
						'VALUE'    => $_field['NAME'],
						'FORM_NAM' => CFormRenderer::$formname,
						'MODE'     => 'FORM_FILL'
					)
				);
				$cart = ob_get_clean();
				if($_field['MAP_CENTER'])
				{
				    $cart = str_replace('map.disableRuler();', 'map.disableRuler(); map.setCenter(new context.YMaps.GeoPoint('.$_field['MAP_CENTER'].'), 10, context.YMaps.MapType.MAP);', $cart);
				}
				$result .= $cart;
				break;
			}
			
			//Пароль
			case 'password':
			{
				$result .= '<input type="password" '.$field.'>';
				break;
			}
			
			// обычный текстовый инпут
			case 'text':
			default:
			{
				$result .= '<input type="text" '.$field.' value="'.htmlspecialchars($_field['VALUE']).'">';
				break;
			}
		}
		if(ToLower($_field['TYPE'] != 'hidden'))
		{
			$result .= '</div></div>';
		}
		return $result;
	}
	
	/**
	 * Нарисовать отдельно поля, без окружающей формы.
	 * @param  array  $_data  ну, массив...
	 * @param  string $prefix префикс для всех ID и NAME полей
	 * @param  string $suffix суффикс для всех ID и NAME полей
	 * @return string
	 */
	public static function RenderFields($_data, $prefix = '', $suffix = '')
	{
		$result = '';
		foreach($_data['FIELDS'] as &$_field)
		{
			$_field['ID']   = $prefix.$_field['ID'].$suffix;
			$_field['NAME'] = $prefix.$_field['NAME'].$suffix;
			$result .= CFormRenderer::RenderField($_field);
		}
		return $result;
	}
}

?>
