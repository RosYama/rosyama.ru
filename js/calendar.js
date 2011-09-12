// JavaScript Document

/* Пример подключения скрипта в публичной части:


	<script src="calendar_ru.js"></script>
<form action="">
<p><big>Кликните на поле ввода даты для вызова календаря:</big><br>
с <input type="text" value="dd-mm-yy" onfocus="this.select();lcs(this)"
	onclick="event.cancelBubble=true;this.select();lcs(this)">
по <input type="text" value="dd-mm-yy" onfocus="this.select();lcs(this)"
	onclick="event.cancelBubble=true;this.select();lcs(this)">
</p>
</form>


*/




function getObj(objID)
{
    if (document.getElementById) {return document.getElementById(objID);}
    else if (document.all) {return document.all[objID];}
    else if (document.layers) {return document.layers[objID];}
}

function checkClick(e) {
	e?evt=e:evt=event;
	CSE=evt.target?evt.target:evt.srcElement;
	if (getObj('fc'))
		if (!isChild(CSE,getObj('fc')))
			getObj('fc').style.display='none';
}

function isChild(s,d) {
	while(s) {
		if (s==d) 
			return true;
		s=s.parentNode;
	}
	return false;
}

function Left(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function Top(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

// Calendar script
var now = new Date;
var sccd=now.getDate();
var sccm=now.getMonth();
var sccy=now.getFullYear();
var ccm=now.getMonth();
var ccy=now.getFullYear();
	 
document.write('<table id="fc" style="width:300px; left:300px; position:absolute;border-collapse:collapse;background:#FFFFFF;border:1px solid #303030;display:none;-moz-user-select:none;-khtml-user-select:none;user-select:none;" cellpadding=2>');
document.write('<tr style="font:bold 13px Arial"><td style="cursor:pointer;font-size:15px" onclick="csubm()">&laquo;</td><td colspan="5" id="mns" align="center"></td><td align="right" style="cursor:pointer;font-size:15px" onclick="caddm()">&raquo;</td></tr>');
document.write('<tr style="background:#FF9900;font:12px Arial;color:#FFFFFF"><td align=center>П</td><td align=center>В</td><td align=center>С</td><td align=center>Ч</td><td align=center>П</td><td align=center>С</td><td align=center>В</td></tr>');
for(var kk=1;kk<=6;kk++) {
	document.write('<tr>');
	for(var tt=1;tt<=7;tt++) {
		num=7 * (kk-1) - (-tt);
		document.write('<td id="v' + num + '" style="width:10px;height:10px">&nbsp;</td>');
	}
	document.write('</tr>');
}
document.write('<tr><td colspan="7" align="center" style="cursor:pointer;font:13px Arial;background:#FFC266" onclick="today()">Сегодня: '+addnull(sccd,sccm+1,sccy)+'</td></tr>');
document.write('</table>');

document.all?document.attachEvent('onclick',checkClick):document.addEventListener('click',checkClick,false);




var updobj;
function lcs(ielem) {
	updobj=ielem;
	getObj('fc').style.left=Left(ielem);
	getObj('fc').style.top=Top(ielem)+ielem.offsetHeight;
	getObj('fc').style.display='';
	
	// First check date is valid
	curdt=ielem.value;
	curdtarr=curdt.split('-');
	isdt=true;
	for(var k=0;k<curdtarr.length;k++) {
		if (isNaN(curdtarr[k]))
			isdt=false;
	}
	if (isdt&(curdtarr.length==3)) {
		ccm=curdtarr[1]-1;
		ccy=curdtarr[2];
		prepcalendar(curdtarr[0],curdtarr[1]-1,curdtarr[2]);
	}
	
}

function evtTgt(e)
{
	var el;
	if(e.target)el=e.target;
	else if(e.srcElement)el=e.srcElement;
	if(el.nodeType==3)el=el.parentNode; // defeat Safari bug
	return el;
}
function EvtObj(e){if(!e)e=window.event;return e;}
function cs_over(e) {
	evtTgt(EvtObj(e)).style.background='#FFEBCC';
}
function cs_out(e) {
	evtTgt(EvtObj(e)).style.background='#FFFFFF';
}
function cs_click(e) {
	updobj.value=calvalarr[evtTgt(EvtObj(e)).id.substring(1,evtTgt(EvtObj(e)).id.length)];
	getObj('fc').style.display='none';
}

var mn=new Array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентрябрь','Октябрь','Ноябрь','Декабрь');
var mnn=new Array('31','28','31','30','31','30','31','31','30','31','30','31');
var mnl=new Array('31','29','31','30','31','30','31','31','30','31','30','31');
var calvalarr=new Array(42);

function f_cps(obj) {
	obj.style.background='#FFFFFF';
	obj.style.font='10px Arial';
	obj.style.color='#333333';
	obj.style.textAlign='center';
	obj.style.textDecoration='none';
	obj.style.border='1px solid #606060';
	obj.style.cursor='pointer';
}

function f_cpps(obj) {
	obj.style.background='#C4D3EA';
	obj.style.font='10px Arial';
	obj.style.color='#FF9900';
	obj.style.textAlign='center';
	obj.style.textDecoration='line-through';
	obj.style.border='1px solid #6487AE';
	obj.style.cursor='default';
}

function f_hds(obj) {
	obj.style.background='#FFF799';
	obj.style.font='bold 10px Arial';
	obj.style.color='#333333';
	obj.style.textAlign='center';
	obj.style.border='1px solid #6487AE';
	obj.style.cursor='pointer';
}

// day selected
function prepcalendar ( hd, cm, cy )
{
	now=new Date();
	sd=now.getDate();
	td=new Date();
	td.setDate(1);
	td.setFullYear(cy);
	td.setMonth(cm);
	cd=td.getDay();
	if (cd==0)cd=6; else cd--;
	
	getObj('mns').innerHTML=mn[cm]+ ' ' + cy;
	
	marr=((cy%4)==0)?mnl:mnn;
	
	for(var d=1;d<=42;d++)
	{
		f_cps ( getObj ( 'v' + parseInt ( d ) ) );
		if ( ( d >= (cd -(-1) ) ) && ( d<=cd-(-marr[cm]) ) )
		{
			getObj('v'+parseInt(d)).onmouseover=cs_over;
			getObj('v'+parseInt(d)).onmouseout=cs_out;
			getObj('v'+parseInt(d)).onclick=cs_click;
			
			// if today
			if (sccm == cm && sccd == (d-cd) && sccy == cy)
				getObj('v'+parseInt(d)).style.color='#FF9900';

			getObj('v'+parseInt(d)).innerHTML=d-cd;
				
			calvalarr[d]=addnull(d-cd,cm-(-1),cy);
		}
		else
		{
			getObj('v'+d).innerHTML='&nbsp;';
			getObj('v'+parseInt(d)).onmouseover=null;
			getObj('v'+parseInt(d)).onmouseout=null;
			getObj('v'+parseInt(d)).onclick=null;
			getObj('v'+parseInt(d)).style.cursor='default';
		}
	}
}

prepcalendar('',ccm,ccy);

function caddm() {
	marr=((ccy%4)==0)?mnl:mnn;
	
	ccm+=1;
	if (ccm>=12) {
		ccm=0;
		ccy++;
	}
	prepcalendar('',ccm,ccy);
}

function csubm() {
	marr=((ccy%4)==0)?mnl:mnn;

	ccm-=1;
	if (ccm<0) {
		ccm=11;
		ccy--;
	}
	prepcalendar('',ccm,ccy);
}

function today() {
	updobj.value=addnull(now.getDate(),now.getMonth()+1,now.getFullYear());
	getObj('fc').style.display='none';
	prepcalendar('',sccm,sccy);
}

function addnull(d,m,y)
{
	var d0='',m0='';
	if (d<10)d0='0';
	if (m<10)m0='0';
	
	return ''+d0+d+'-'+m0+m+'-'+y;
}