var bFilterAjaxInProgress = false;

/**
 * Обработка нажатия на город в выпадающем списке.
 */
function onCityClick(city)
{
	var obj = document.getElementById('filter_city');
	if(obj)
	{
		obj.value = city;
		obj.className = '';
	}
	$('#filter_city_tip').slideUp();
	$('#filter_city_tip')[0].innerHTML = "";
}

/**
 * Уход фокуса с инпута ввода города
 * @param HTMLInputObject obj собственно, тот инпут
 */
function onFilterCityBlur(obj)
{
	if(!obj.value.length)
	{
		obj.value = 'Город';
		obj.className = 'disabled';
	}
	//$('#filter_city_tip').slideUp();
	//$('#filter_city_tip')[0].innerHTML = "";
}

/**
 * Нажатие на инпут города
 * @param HTMLInputObject obj собственно, тот инпут
 */
function onFilterCityClick(obj)
{
	if(obj.value == 'Город')
	{
		obj.value = '';
	}
	obj.className = '';
}

/**
 * Несколько предварительных действий перед сабмитом формы.
 */
function onFilterFormSubmit()
{
	var obj = document.getElementById('filter_rf_subject');
	if(obj && obj.value == 'Субъект РФ')
	{
		obj.value = '';
	}
	obj = document.getElementById('filter_city');
	if(obj && obj.value == 'Город')
	{
		obj.value = '';
	}
}

/**
 * Обработка события нажатия кнопки на инпут ввода города в фильтре.
 */
function onFilterCityKeyUp(obj)
{
	if(obj.value.length > 1)
	{
		obj.className = '';
		if(bFilterAjaxInProgress)
		{
			return;
		}
		bFilterAjaxInProgress = true;
		jQuery.get(
			'/',
			{
				ajax: 'getcity',
				city: obj.value,
				rfsubjid: document.getElementById('filter_rf_subject_id').value
			},
			function(data)
			{
				bFilterAjaxInProgress = false;
				if(data)
				{
					$('#filter_city_tip')[0].innerHTML = data;
					$('#filter_city_tip').slideDown();
				}
				else
				{
					$('#filter_city_tip').slideUp();
					$('#filter_city_tip')[0].innerHTML = "";
				}
			}
		);
	}
	else
	{
		$('#filter_city_tip').slideUp();
		$('#filter_city_tip')[0].innerHTML = "";
	}
}

/**
 * Обработка события нажатия кнопки на инпут ввода субъекта РФ в фильтре.
 */
function onFilterRFSKeyUp(obj)
{
	if(obj.value.length > 1)
	{
		obj.className = '';
		if(bFilterAjaxInProgress)
		{
			return;
		}
		bFilterAjaxInProgress = true;
		jQuery.get(
			'/',
			{
				ajax: 'getrfsubj',
				rfsubj: obj.value
			},
			function(data)
			{
				bFilterAjaxInProgress = false;
				if(data)
				{
					$('#filter_rf_subject_tip')[0].innerHTML = data;
					$('#filter_rf_subject_tip').slideDown();
				}
				else
				{
					$('#filter_rf_subject_tip').slideUp();
					$('#filter_rf_subject_tip')[0].innerHTML = "";
				}
			}
		);
	}
	else
	{
		$('#filter_rf_subject_tip').slideUp();
		$('#filter_rf_subject_tip')[0].innerHTML = "";
		$('#filter_rf_subject_id')[0].value = "";
	}
}

/**
 * Отработка нажатия на спан в подсказке по субъектам РФ.
 */
function onRFSubjClick(id, text)
{
	var obj = document.getElementById('filter_rf_subject_id');
	if(obj)
	{
		obj.value = id;
	}
	obj = document.getElementById('filter_rf_subject');
	if(obj)
	{
		obj.value = text;
		obj.className = '';
	}
	$('#filter_rf_subject_tip').slideUp();
	$('#filter_rf_subject_tip')[0].innerHTML = "";
}

/**
 * Уход фокуса с инпута ввода субъекта РФ.
 * @param HTMLInputObject obj собственно, тот инпут
 */
function onRFSubjectBlur(obj)
{
	if(!obj.value.length)
	{
		obj.value = 'Субъект РФ';
		obj.className = 'disabled';
	}
	//$('#filter_rf_subject_tip').slideUp();
	//$('#filter_rf_subject_tip')[0].innerHTML = "";
}

/**
 * Нажатие на инпут с субъектами РФ.
 * @param HTMLInputObject obj собственно, тот инпут
 */
function onRFSubjectClick(obj)
{
	if(obj.value == 'Субъект РФ')
	{
		obj.value = '';
	}
}