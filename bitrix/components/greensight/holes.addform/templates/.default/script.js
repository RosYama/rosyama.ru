function checkHoleForm()
{
	var obj = document.getElementById('COORDINATES');
	if(!obj || !obj.value.length)
	{
		alert(mess['ADD_HOLE_FORM_ERROR_NO_COORD']);
		return false;
	}
	obj = document.getElementById('address');
	if(!obj || !obj.value.length)
	{
		alert(mess['ADD_HOLE_FORM_ERROR_NO_ADDRESS']);
		return false;
	}
	return true;
}

var arSearchResults = new Array();

searchResultsLoad = function(geocoder)
{
	var ob = document.getElementById('searchresults');
	if(!ob)
	{
		return;
	}
	ob.innerHTML = '';
	if(len = geocoder.length()) 
	{
		var obList = document.createElement('UL');
		obList.className = 'bx-yandex-search-results';
		var str = '';
		str += 'Результаты поиска: <b>' + len + '</b> объектов найдено.';
		arSearchResults = new Array();
		document.getElementById('clear_result_link').style.display = 'inline';
		for (var i = 0; i < len; i++)
		{
			arSearchResults[i] = geocoder.get(i);
			map.addOverlay(arSearchResults[i]);
			YMaps.Events.observe(arSearchResults[i], arSearchResults[i].Events.Click, function(pm, ev) { map.setCenter(pm.getCoordPoint(), (pm.precision == 'other' || pm.precision == 'suggest' ? 10 : 15)); clearSerchResults(); } );
			var obListElement = document.createElement('LI');
			var obLink = document.createElement('A');
			obLink.href = "javascript:void(0)";
			obLink.appendChild(document.createTextNode(arSearchResults[i].text));
			obLink.BXSearchIndex = i;
			obLink.onclick = showSearchResult;
			obListElement.appendChild(obLink);
			obList.appendChild(obListElement);
		}
	}
	else 
	{
		var str = 'Ничего не найдено';
	}
	ob.innerHTML = str;
	if(null != obList) ob.appendChild(obList);
	map.redraw();
}

showSearchResult = function(index)
{
	if(null == index || index.constructor == window.Event);
	index = this.BXSearchIndex;
	if(null != index && null != arSearchResults[index])
	{
		//arSearchResults[index].openBalloon();
		//map.panTo(arSearchResults[index].getGeoPoint());
		map.setCenter(arSearchResults[index].getGeoPoint(), (arSearchResults[index].precision == 'other' || arSearchResults[index].precision == 'suggest' ? 10 : 15));
	}
	var ob = document.getElementById('searchresults');
	if(!ob)
	{
		return;
	}
	ob.innerHTML = '';
	clearSerchResults();
}

searchByAddress = function(str)
{
	str = str.replace(/^[\s\r\n]+/g, '').replace(/[\s\r\n]+$/g, '');
	if (str == '') return;
	geocoder = new YMaps.Geocoder(str);
	YMaps.Events.observe(
		geocoder, 
		geocoder.Events.Load, 
		searchResultsLoad
	);
}

clearSerchResults = function()
{
	var ob = document.getElementById('searchresults');
	if(!ob)
	{
		return;
	}
	ob.innerHTML = '';
	for(var i in arSearchResults)
	{
		map.removeOverlay(arSearchResults[i]);
	}
	arSearchResults = new Array;
	document.getElementById('clear_result_link').style.display = 'none';
}

var coordpoint;

function setCoordValue(map, ev)
{
	if(coordpoint)
	{
		map.removeOverlay(coordpoint);
		coordpoint = null;
	}
	document.getElementById('COORDINATES').value = ev.getCoordPoint();
	var lat = document.getElementById('COORDINATES').value.split(',');
	var lon = lat[1];
	lat = lat[0];
	coordpoint = new YMaps.Placemark(new YMaps.GeoPoint(lat, lon), { style: 'default#violetPoint', draggable: true, hasBalloon: false, hideIcon: false });
	YMaps.Events.observe(coordpoint, coordpoint.Events.DragEnd, function (obj) {
		document.getElementById('COORDINATES').value = obj.getCoordPoint();
		geocodeOnSetCoordValue();
	});
	map.addOverlay(coordpoint);
	geocodeOnSetCoordValue();
}

function geocodeOnSetCoordValue()
{
	var geocoder = new YMaps.Geocoder(coordpoint.getGeoPoint());
	YMaps.Events.observe(geocoder, geocoder.Events.Load, function () {
		if(this.length())
		{
			var geo_text = this.get(0).text.split(',');
			var subjectrf;
			var city;
			var otherstr;
			do
			{
				// сразу отрежем название страны
				geo_text[0] = '';
				document.getElementById('address').value = geo_text.join(',').substr(2);
				// города - субъекты РФ
				if(geo_text[1] == ' Москва' || geo_text[1] == ' Санкт-Петербург')
				{
					subjectrf   = city = geo_text[1];
					geo_text[1] = '';
					// города-спутники
					if
					(
						geo_text[2] == ' Зеленоград'
						|| geo_text[2].indexOf('поселок') != -1
						|| geo_text[2].indexOf('город') != -1
						|| geo_text[2].indexOf('деревня') != -1
						|| geo_text[2].indexOf('село') != -1
					)
					{
						city        = geo_text[2];
						geo_text[2] = '';
						otherstr    = geo_text.join(',');
						break;
					}
					otherstr = geo_text.join(',');
					break;
				}
				// неизвестно что
				if(!geo_text[2])
				{
					subjectrf = city = '';
					otherstr = geo_text.join(',');
					break;
				}
				subjectrf = geo_text[1];
				geo_text[1] = '';
				// район или город
				if(geo_text[2].indexOf('район') != -1)
				{
					geo_text[2] = '';
					// точка попала в город
					if(geo_text[3])
					{
						city = geo_text[3];
						geo_text[3] = '';
						otherstr = geo_text.join(',');
					}
					// точка попала фиг знает куда
					else
					{
						subjectrf = city = '';
						otherstr = geo_text.join(',');
						break;
					}
				}
				else
				{
					city = geo_text[2];
					geo_text[2] = '';
					otherstr = geo_text.join(',');
				}
			}
			while(false);
			
		}
		else
		{
			subjectrf = city = otherstr = '';
		}
		while(otherstr.indexOf(',,') != -1)
		{
			otherstr = otherstr.replace(',,', '');
		}
		while(otherstr[0] == ' ' || otherstr[0] == ',')
		{
			otherstr = otherstr.substring(1);
		}
		document.getElementById('adr_subjectrf').value = subjectrf;
		document.getElementById('adr_city').value = city;
		document.getElementById('recognized_address_str').innerHTML = subjectrf + (city.length && city != subjectrf ? ', ' + city : '') + (otherstr.length ? ', ' : '');
		document.getElementById('other_address_str').innerHTML = otherstr;
	});
}

var PlaceMarks = new Array();
var bAjaxInProgress = false;
function GetPlacemarks(map)
{
	if(!bAjaxInProgress)
	{
		bAjaxInProgress = true;
		var mapBounds = map.getBounds();
		jQuery.get
		(
			'/bitrix/components/greensight/holes.yandex.view/holes_list.php',
			{
				bottom: mapBounds.getBottom(),
				left:   mapBounds.getLeft(),
				top:    mapBounds.getTop(),
				right:  mapBounds.getRight(),
				noevents: 1,
				skip_id: hole_id
			},
			function(data)
			{
				bAjaxInProgress = false;
				map.removeAllOverlays();
				eval(data);
			}
		);
	}
}