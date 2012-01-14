<script language="javascript">
<!--

function GetCheckNull()
{
	for(var i=0;i<4;i++)
	{
		document.getElementById("chn" + i).checked = 0;
	}
	for(var i=0;i<6;i++)
	{
		document.getElementById("ch" + i).checked = 0;
	}
}
-->
</script>
<h1><?$APPLICATION->ShowTitle();?></h1>
<form action="/map/" name="f1" method="post">
<div class="filterCol filterStatus">
<p class="title">Показать дефекты со статусом</p>
	<label><span class="fresh"><input id="chn0" name="STATE[0]" type="checkbox" <?if($_REQUEST["STATE"][0]):?>checked="checked"<?endif?> value="fresh" /></span><ins>Новые</ins></label>
	<label><span class="inprogress"><input id="chn2" name="STATE[2]" type="checkbox"  <?if($_REQUEST["STATE"][2]):?>checked="checked"<?endif?> value="inprogress" /></span><ins>Отправлено заявление</ins></label>
	<label><span class="fixed"><input id="chn3" name="STATE[3]" type="checkbox"  <?if($_REQUEST["STATE"][3]):?>checked="checked"<?endif?> value="fixed" /></span><ins>Сделаны</ins></label>
	<label><span class="gibddre"><input id="chn5" name="STATE[5]" type="checkbox"  <?if($_REQUEST["STATE"][5]):?>checked="checked"<?endif?> value="gibddre" /></span><ins>Получен ответ</ins></label>
	<label><span class="achtung"><input id="chn1" name="STATE[1]" type="checkbox"  <?if($_REQUEST["STATE"][1]):?>checked="checked"<?endif?> value="achtung" /></span><ins>Не сделаны</ins></label>
	<label><span class="prosecutor"><input id="chn6" name="STATE[6]" type="checkbox"  <?if($_REQUEST["STATE"][6]):?>checked="checked"<?endif?> value="prosecutor" /></span><ins>Жалоба в прокуратуре</ins></label>
</div>
<div class="filterCol filterType">
<p class="title">Показать тип дефектов</p>
<label class="col1"><span><input id="ch0" name="TYPE[0]" type="checkbox"  value="badroad" <?if($_REQUEST["TYPE"][0]):?>checked="checked"<?endif?>  /></span><ins class="badroad">Разбитая дорога</ins></label>
<label class="col2"><span><input id="ch1" name="TYPE[10]" type="checkbox" value="hatch" <?if($_REQUEST["TYPE"][10]):?>checked="checked"<?endif?>  /></span><ins class="hatch">Люк</ins></label>
<label class="col3"><span><input id="ch2" name="TYPE[3]" type="checkbox"  value="holeonroad" <?if($_REQUEST["TYPE"][3]):?>checked="checked"<?endif?> /></span><ins class="holeonroad">Яма на дороге</ins></label>
<label class="col4"><span><input id="ch3" name="TYPE[5]" type="checkbox"  value="rails" <?if($_REQUEST["TYPE"][5]):?>checked="checked"<?endif?> /></span><ins class="rails">Рельсы</ins></label>
<label class="col1"><span><input id="ch5" name="TYPE[7]" type="checkbox"  value="holeinyard" <?if($_REQUEST["TYPE"][7]):?>checked="checked"<?endif?> /></span><ins class="holeinyard">Яма во дворе</ins></label>
<label class="col2"><span><input id="ch6" name="TYPE[11]" type="checkbox" value="snow" <?if($_REQUEST["TYPE"][11]):?>checked="checked"<?endif?> /></span><ins class="snow">Снег</ins></label>
<label class="col4"><span><input id="ch4" name="TYPE[6]" type="checkbox"  value="policeman" <?if($_REQUEST["TYPE"][6]):?>checked="checked"<?endif?> /></span><ins class="policeman">Полицейский</ins></label>
<input id="MAPLAT" name="MAPLAT" type="hidden" value="<?= htmlspecialcharsEx($_REQUEST["MAPLAT"]) ?>" />
<input id="MAPZOOM" name="MAPZOOM" type="hidden" value="<?= htmlspecialcharsEx($_REQUEST["MAPZOOM"]) ?>" />



</div>
<div class="submit"><input type="submit" name="button" id="button" value="Показать" /><input name="reset" value="Сбросить" onclick="GetCheckNull()" type="button" /></div>
</form>