var jsYandexCE_search = {
	map: null,
	arData: null,
	obForm: null,
	
	currentView: '',
	
	bPositionFixed: true,
	
	__arValidKeys: ['yandex_lat', 'yandex_lon', 'yandex_scale'],
	
	__currentPolyLine: null,
	__currentPolyLineObject: null,
	
	init: function() 
	{
		BX.loadCSS('/bitrix/components/bitrix/map.yandex.system/templates/.default/style.css');
	
		//jsYandexCE_search.map = GLOBAL_arMapObjects['system_search_edit'];
		jsYandexCE_search.context = jsYandexCE_search.map.bx_context;
		
		jsYandexCE_search.arData = arPositionData;
		jsYandexCE_search.obForm = document.forms['bx_popup_form_yandex_map'];
		jsYandexCE_search.obForm.onsubmit = jsYandexCE_search.__saveChanges;
		
		jsYandexCE_search.context.YMaps.Events.observe(jsYandexCE_search.map, jsYandexCE_search.map.Events.Move, jsYandexCE_search.__getPositionValues);
		jsYandexCE_search.context.YMaps.Events.observe(jsYandexCE_search.map, jsYandexCE_search.map.Events.Update, jsYandexCE_search.__getPositionValues);
		jsYandexCE_search.context.YMaps.Events.observe(jsYandexCE_search.map, jsYandexCE_search.map.Events.ChangeType, jsYandexCE_search.__getPositionValues);
		
		if (!jsYandexCE_search.arData.yandex_lat || !jsYandexCE_search.arData.yandex_lon || !jsYandexCE_search.arData.yandex_scale)
		{
			var obPos = jsYandexCE_search.map.getCenter();
			jsYandexCE_search.arData.yandex_lat = obPos.getLat();
			jsYandexCE_search.arData.yandex_lon = obPos.getLng();
			jsYandexCE_search.arData.yandex_scale = jsYandexCE_search.map.getZoom();
			jsYandexCE_search.bPositionFixed = false;
		}
		else
		{
			jsYandexCE_search.bPositionFixed = true;
		}

		jsYandexCE_search.setControlValue('yandex_lat', jsYandexCE_search.arData.yandex_lat);
		jsYandexCE_search.setControlValue('yandex_lon', jsYandexCE_search.arData.yandex_lon);
		jsYandexCE_search.setControlValue('yandex_scale', jsYandexCE_search.arData.yandex_scale);

		jsYandexCE_search.currentView = jsYandexMess.current_view;
		
		var obType = jsYandexCE_search.map.getType();
		jsYandexCE_search.setControlValue('yandex_view', obType.getName());
		
		document.getElementById('bx_restore_position').onclick = jsYandexCE_search.restorePositionValues;
		document.getElementById('bx_yandex_position_fix').onclick = function () {jsYandexCE_search.setFixedFlag(this.checked)};
		jsYandexCE_search.setFixedFlag(document.getElementById('bx_yandex_position_fix').defaultChecked);

		document.getElementById('bx_yandex_map_controls').style.visibility = 'visible';
		document.getElementById('bx_yandex_map_address_search').style.visibility = 'visible';
	},
	
	__getPositionValues: function()
	{
		if (jsYandexCE_search.bPositionFixed)
			return;
	
		var obPos = jsYandexCE_search.map.getCenter();
		jsYandexCE_search.arData.yandex_lat = obPos.getLat();
		jsYandexCE_search.arData.yandex_lon = obPos.getLng();
		jsYandexCE_search.arData.yandex_scale = jsYandexCE_search.map.getZoom();
		
		jsYandexCE_search.setControlValue('yandex_lat', jsYandexCE_search.arData.yandex_lat);
		jsYandexCE_search.setControlValue('yandex_lon', jsYandexCE_search.arData.yandex_lon);
		jsYandexCE_search.setControlValue('yandex_scale', jsYandexCE_search.arData.yandex_scale);
		
		var obCurrentView = jsYandexCE_search.map.getType();
		
		jsYandexCE_search.currentView = (
			obCurrentView == jsYandexCE_search.context.YMaps.MapType.HYBRID
			? 'HYBRID'
			: (
				obCurrentView == jsYandexCE_search.context.YMaps.MapType.SATELLITE
				? 'SATELLITE'
				: 'MAP'
			)
		);
		
		jsYandexCE_search.setControlValue('yandex_view', obCurrentView.getName());
	},
	
	restorePositionValues: function(e)
	{
		BX.PreventDefault(e);
	
		//alert(jsYandexCE_search.currentView);
		if (jsYandexCE_search.currentView && jsYandexCE_search.context.YMaps.MapType[jsYandexCE_search.currentView])
			jsYandexCE_search.map.setType(jsYandexCE_search.context.YMaps.MapType[jsYandexCE_search.currentView]);
		
		jsYandexCE_search.map.setZoom(jsYandexCE_search.arData.yandex_scale);
		jsYandexCE_search.map.panTo(new jsYandexCE_search.context.YMaps.GeoPoint(jsYandexCE_search.arData.yandex_lon, jsYandexCE_search.arData.yandex_lat));
	},
	
	setFixedFlag: function(value)
	{
		jsYandexCE_search.bPositionFixed = value;
		if (!value)
			jsYandexCE_search.__getPositionValues();
	},
	
	setControlValue: function(control, value)
	{
		var obControl = jsYandexCE_search.obForm['bx_' + control];
		if (null != obControl)
			obControl.value = value;
			
		var obControlOut = document.getElementById('bx_' + control + '_value');
		if (null != obControlOut)
			obControlOut.innerHTML = value;
	},
	
	__checkValidKey: function(key)
	{
		if (Number(key) == key)
			return true;
	
		for (var i = 0, len = jsYandexCE_search.__arValidKeys.length; i < len; i++)
		{
			if (jsYandexCE_search.__arValidKeys[i] == key)
				return true;
		}
		
		return false;
	},
	
	__serialize: function(obj)
	{
  		if (typeof(obj) == 'object')
  		{
    		var str = '', cnt = 0;
		    for (var i in obj)
		    {
				if (jsYandexCE_search.__checkValidKey(i))
				{
					++cnt;
					str += jsYandexCE_search.__serialize(i) + jsYandexCE_search.__serialize(obj[i]);
				}
		    }
		    
    		str = "a:" + cnt + ":{" + str + "}";
    		
    		return str;
		}
		else if (typeof(obj) == 'boolean')
		{
			return 'b:' + (obj ? 1 : 0) + ';';
		}
		else if (null == obj)
		{
			return 'N;'
		}
		else if (Number(obj) == obj && obj != '' && obj != ' ')
		{
			if (Math.floor(obj) == obj)
				return 'i:' + obj + ';';
			else
				return 'd:' + obj + ';';
    	}
  		else if(typeof(obj) == 'string')
  		{
			obj = obj.replace(/\r\n/g, "\n");
			obj = obj.replace(/\n/g, "###RN###");

			var offset = 0;
			if (window._global_BX_UTF)
			{
				for (var q = 0, cnt = obj.length; q < cnt; q++)
				{
					if (obj.charCodeAt(q) > 127) offset++;
				}
			}
			
  			return 's:' + (obj.length + offset) + ':"' + obj + '";';
		}
	},
	
	__saveChanges: function()
	{
		if (!jsYandexCE_search.map) 
			return false;
	
		window.jsYandexCEOpener_search.saveData(jsYandexCE_search.__serialize(jsYandexCE_search.arData), jsYandexCE_search.currentView);
		delete jsYandexCE_search.map;
		return false;
	}
}

var jsYandexCESearch = {
	bInited: false,

	map: null,
	geocoder: null,
	obInput: null,
	timerID: null,
	timerDelay: 1000,
	
	arSearchResults: [],
	
	obOut: null,
	
	__init: function(input)
	{
		if (jsYandexCESearch.bInited) return;
		
		jsYandexCESearch.map = jsYandexCE_search.map;
		jsYandexCESearch.obInput = input;
		
		input.form.onsubmit = function() {jsYandexCESearch.doSearch(); return false;}
		
		input.onfocus = jsYandexCESearch.showResults;
		input.onblur = jsYandexCESearch.hideResults;
		
		jsYandexCESearch.bInited = true;
	},
	
	setTypingStarted: function(input)
	{
		if (!jsYandexCESearch.bInited)
			jsYandexCESearch.__init(input);

		jsYandexCESearch.hideResults();
			
		if (null != jsYandexCESearch.timerID)
			clearTimeout(jsYandexCESearch.timerID);
	
		jsYandexCESearch.timerID = setTimeout(jsYandexCESearch.doSearch, jsYandexCESearch.timerDelay);
	},
	
	doSearch: function()
	{
		var value = BX.util.trim(jsYandexCESearch.obInput.value);
		if (value.length > 1)
		{
			var geocoder = new jsYandexCE_search.context.YMaps.Geocoder(value);
		
			jsYandexCE_search.context.YMaps.Events.observe(
				geocoder, 
				geocoder.Events.Load, 
				jsYandexCESearch.__searchResultsLoad
			);
			
			jsYandexCE_search.context.YMaps.Events.observe(
				geocoder, 
				geocoder.Events.Fault, 
				jsYandexCESearch.handleError
			);
		}
	},
	
	handleError: function(error)
	{
		alert(this.jsMess.mess_error + ': ' + error.message);
	},
	
	__generateOutput: function()
	{
		var obPos = BX.pos(jsYandexCESearch.obInput);
		
		jsYandexCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsYandexCESearch.obOut.className = 'bx-yandex-address-search-results';
		jsYandexCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
		jsYandexCESearch.obOut.style.left = obPos.left + 'px';
	},

	__searchResultsLoad: function(geocoder)
	{
		var _this = jsYandexCESearch;
	
		if (null == _this.obOut)
			_this.__generateOutput();
			
		_this.obOut.innerHTML = '';
		_this.clearSearchResults();
		
		if (len = geocoder.length()) 
		{
			for (var i = 0; i < len; i++)
			{
				_this.arSearchResults[i] = geocoder.get(i);
				
				var obListElement = document.createElement('LI');
				
				if (i == 0)
					obListElement.className = 'bx-yandex-first';

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				var obText = obLink.appendChild(document.createElement('SPAN'));
				obText.appendChild(document.createTextNode(_this.arSearchResults[i].text));
				
				obLink.BXSearchIndex = i;
				obLink.onclick = _this.__showSearchResult;
				
				obListElement.appendChild(obLink);
				_this.obOut.appendChild(obListElement);
			}
		} 
		else 
		{
			//var str = _this.jsMess.mess_search_empty;
			_this.obOut.innerHTML = '<li class="bx-yandex-notfound">' + window.jsYandexMess.nothing_found + '</li>';
		}
		
		_this.showResults();
		
		//_this.map.redraw();
	},
	
	__showSearchResult: function()
	{
		if (null !== this.BXSearchIndex)
		{
			jsYandexCESearch.map.panTo(jsYandexCESearch.arSearchResults[this.BXSearchIndex].getGeoPoint());
			jsYandexCESearch.map.redraw();
		}
	},
	
	showResults: function()
	{
		if (null != jsYandexCESearch.obOut)
			jsYandexCESearch.obOut.style.display = 'block';
	},

	hideResults: function()
	{
		if (null != jsYandexCESearch.obOut)
		{
			setTimeout("jsYandexCESearch.obOut.style.display = 'none'", 300);
		}
	},
	
	clearSearchResults: function()
	{
		for (var i = 0; i < jsYandexCESearch.arSearchResults.length; i++)
		{
			delete jsYandexCESearch.arSearchResults[i];
		}

		jsYandexCESearch.arSearchResults = [];
	},
	
	clear: function()
	{
		if (!jsYandexCESearch.bInited)
			return;
			
		jsYandexCESearch.bInited = false;
		if (null != jsYandexCESearch.obOut)
		{
			jsYandexCESearch.obOut.parentNode.removeChild(jsYandexCESearch.obOut);
			jsYandexCESearch.obOut = null;
		}
		
		jsYandexCESearch.arSearchResults = [];
		jsYandexCESearch.map = null;
		jsYandexCESearch.geocoder = null;
		jsYandexCESearch.obInput = null;
		jsYandexCESearch.timerID = null;
	}
}
