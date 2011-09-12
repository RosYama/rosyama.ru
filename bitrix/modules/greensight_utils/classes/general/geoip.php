<?php

class CGreensightGeoip
{
	/**
	 * Получить коорднаты города, относящегося к блоку ip-адресов, в который
	 * входит адрес указанного диапазона.
	 * @param string $ip адрес
	 */
	public static function GetCoordinatesByIP($ip)
	{
		global $DB;
		if(!preg_match('/([\d]{1,3}\.){3}[\d]{1,3}/', $ip))
		{
			return false;
		}
		$sql_str = "select `latitude`, `longitude` from `b_gs_geoip` where `max_ip` >= inet_aton('".$ip."') and `min_ip` <= inet_aton('".$ip."') order by `max_ip` asc, `min_ip` desc limit 1";
		$res = $DB->Query($sql_str);
		return $res->Fetch();
	}
}

?>