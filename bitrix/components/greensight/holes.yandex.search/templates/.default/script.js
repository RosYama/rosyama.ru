var JCBXYandexSearch = function(map_id, obOut, jsMess)
{ 
	var _this = this;
	
	this.map_id = map_id;
	this.map = GLOBAL_arMapObjects[this.map_id];

	this.obOut = obOut;
	
	if (null == this.map)
		return false;

	this.arSearchResults = [];
	this.jsMess = jsMess;
	
	this.__searchResultsLoad = function(geocoder)
	{
		if (null == _this.obOut)
			return;
	
		_this.obOut.innerHTML = '';
		_this.clearSearchResults();
		
		var obList = null;
		if (len = geocoder.length()) 
		{
			obList = document.createElement('UL');
			obList.className = 'bx-yandex-search-results';
			var str = '';
			str += _this.jsMess.mess_search + ': <b>' + len + '</b> ' + _this.jsMess.mess_found + '.';
			
			for (var i = 0; i < len; i++)
			{
				_this.arSearchResults[i] = geocoder.get(i);
				_this.map.addOverlay(_this.arSearchResults[i]);
				
				var obListElement = document.createElement('LI');

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				obLink.appendChild(document.createTextNode(_this.arSearchResults[i].text));
				
				obLink.BXSearchIndex = i;
				obLink.onclick = _this.__showSearchResult;
				
				obListElement.appendChild(obLink);
				obList.appendChild(obListElement);
			}
		} 
		else 
		{
			var str = _this.jsMess.mess_search_empty;
		}
		
		_this.obOut.innerHTML = str;
		
		if (null != obList)
			_this.obOut.appendChild(obList);
			
		_this.map.redraw();
	};
	
	this.__showSearchResult = function(index)
	{
		if (null == index || index.constructor == window.Event);
			index = this.BXSearchIndex;
	
		if (null != index && null != _this.arSearchResults[index])
		{
			_this.arSearchResults[index].openBalloon();
			_this.map.panTo(_this.arSearchResults[index].getGeoPoint());
		}
	};
	
	this.searchByAddress = function(str)
	{
		//str = jsUtils.trim(str);
		str = str.replace(/^[\s\r\n]+/g, '').replace(/[\s\r\n]+$/g, '');
	
		if (str == '')
			return;
		
		geocoder = new _this.map.bx_context.YMaps.Geocoder(str);
		
		_this.map.bx_context.YMaps.Events.observe(
			geocoder, 
			geocoder.Events.Load, 
			_this.__searchResultsLoad
		);
		
		_this.map.bx_context.YMaps.Events.observe(
			geocoder, 
			geocoder.Events.Fault, 
			_this.handleError
		);
	}
}

JCBXYandexSearch.prototype.handleError = function(error)
{
	alert(this.jsMess.mess_error + ': ' + error.message);
}

JCBXYandexSearch.prototype.clearSearchResults = function()
{
	for (var i = 0; i < this.arSearchResults.length; i++)
	{
		this.arSearchResults[i].closeBalloon();
		this.map.removeOverlay(this.arSearchResults[i]);
		delete this.arSearchResults[i];
	}

	this.arSearchResults = [];
}


if (!window.BX_YMapAddPlacemark)
{
	window.BX_YMapAddPlacemark = function(map, arPlacemark)
	{
		if (null == map)
			return false;
		
		if(!arPlacemark.LAT || !arPlacemark.LON)
			return false;
			
		document.getElementById("MAPLAT").value = arPlacemark.LAT;
		document.getElementById("MAPLOT").value = arPlacemark.LON;
		
		var obPlacemark = new map.bx_context.YMaps.Placemark(new map.bx_context.YMaps.GeoPoint(arPlacemark.LON, arPlacemark.LAT));
		
		if (null != arPlacemark.TEXT && arPlacemark.TEXT.length > 0)
		{
			document.getElementById("MAPTEXT").value = arPlacemark.TEXT.replace(/\n/g, '<br />');
			
			obPlacemark.setBalloonContent(arPlacemark.TEXT.replace(/\n/g, '<br />'));

			var value_view = '';
			if (arPlacemark.TEXT.length > 0)
			{
				var rnpos = arPlacemark.TEXT.indexOf("\n");
				value_view = rnpos <= 0 ? arPlacemark.TEXT : arPlacemark.TEXT.substring(0, rnpos);
				//value_view = value_view.replace(/>/g, '&gt;');
				//value_view = value_view.replace(/</g, '&lt;');
			}
			
			obPlacemark.setIconContent(value_view);
		}

		map.addOverlay(obPlacemark);
		
		return obPlacemark;
	}
}

if (!window.BX_YMapAddPolyline)
{
	window.BX_YMapAddPolyline = function(map, arPolyline)
	{
		if (null == map)
			return false;
		
		if (null != arPolyline.POINTS && arPolyline.POINTS.length > 1)
		{
			var arPoints = [];
			for (var i = 0, len = arPolyline.POINTS.length; i < len; i++)
			{
				arPoints[i] = new map.bx_context.YMaps.GeoPoint(arPolyline.POINTS[i].LON, arPolyline.POINTS[i].LAT);
			}
		}
		else
		{
			return false;
		}
		
		if (null != arPolyline.STYLE)
		{
			var obStyle = new map.bx_context.YMaps.Style();
			obStyle.lineStyle = new map.bx_context.YMaps.LineStyle();
			obStyle.lineStyle.strokeColor = arPolyline.STYLE.lineStyle.strokeColor;
			obStyle.lineStyle.strokeWidth = arPolyline.STYLE.lineStyle.strokeWidth;
			
			var style_id = "bitrix#line_" + Math.random();
			
			map.bx_context.YMaps.Styles.add(style_id, obStyle);
		}
		
		var obPolyline = new map.bx_context.YMaps.Polyline(
			arPoints,
			{style: style_id, clickable: true}
		);
		obPolyline.setBalloonContent(arPolyline.TITLE);
		
		map.addOverlay(obPolyline);
		
		return obPolyline;
	}
}
