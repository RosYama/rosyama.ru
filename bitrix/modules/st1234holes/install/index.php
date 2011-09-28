<?php

/**
 * Инсталлятор и деинсталлятор модуля st1234
 */

IncludeModuleLangFile(__FILE__);

class st1234holes extends CModule
{
	public $MODULE_ID = 'st1234holes';
	
	/**
	 * Типа конструктора - я посмотрел, как устроен модуль iblock, и сделал так же.
	 */
	public function st1234holes()
	{
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		$this->MODULE_VERSION      = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME         = GetMessage("GREENSIGHT_ST1234_INSTALL_NAME");
		$this->MODULE_DESCRIPTION  = GetMessage("GREENSIGHT_ST1234_INSTALL_DESCRIPTION");
	}
	
	/**
	 * Инсталлятор.
	 * @return bool
	 */
	public function DoInstall()
	{
		if(!$this->IsInstalled())
		{
			global $DB;
			$DB->Query("CREATE TABLE `b_holes` (
				`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`USER_ID` int(10) unsigned NOT NULL,
				`LATITUDE` double(13,11) NOT NULL,
				`LONGITUDE` double(14,11) NOT NULL,
				`ADDRESS` text NOT NULL,
				`STATE` enum('fresh','inprogress','fixed','achtung','prosecutor','gibddre') NOT NULL DEFAULT 'fresh',
				`DATE_CREATED` int(10) unsigned NOT NULL,
				`DATE_SENT` int(10) unsigned DEFAULT NULL,
				`DATE_STATUS` int(10) unsigned DEFAULT NULL,
				`COMMENT1` text,
				`COMMENT2` text,
				`TYPE` enum('badroad','holeonroad','hatch','crossing','nomarking','rails','policeman','fence','holeinyard','light') NOT NULL DEFAULT 'holeonroad',
				`ADR_SUBJECTRF` int(10) unsigned DEFAULT NULL,
				`ADR_CITY` varchar(50) DEFAULT NULL,
				`COMMENT_GIBDD_REPLY` text,
				`GIBDD_REPLY_RECEIVED` tinyint(1) DEFAULT '0',
				`PREMODERATED` tinyint(1) DEFAULT '0',
				`DATE_SENT_PROSECUTOR` INTEGER UNSIGNED default null,
				PRIMARY KEY (`ID`)
			  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
			$DB->Query("CREATE TABLE `b_gs_geoip` (
				`min_ip` int(10) unsigned NOT NULL,
				`max_ip` int(10) unsigned NOT NULL,
				`city` varchar(50) NOT NULL,
				`latitude` float(14,11) NOT NULL,
				`longitude` float(14,11) NOT NULL,
				PRIMARY KEY (`min_ip`,`max_ip`)
			  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
			return true;
		}
		return false;
	}
	
	/**
	 * Деинсталлятор.
	 * @return bool
	 */
	public function DoUninstall()
	{
		if($this->IsInstalled())
		{
			global $DB;
			$DB->Query("drop table `b_holes`");
			$DB->Query("drop table `b_gs_geoip`");
			return true;
		}
		return false;
	}
	
	/**
	 * Проверяет, установлен ли модуль.
	 * @return bool
	 */
	public function IsInstalled()
	{
		global $DB;
		$res = $DB->Query("show tables");
		while($ar = $res->Fetch())
		{
			$ar = array_values($ar);
			if($ar[0] == 'b_holes')
			{
				return true;
			}
		}
		return false;
	}
}

?>
