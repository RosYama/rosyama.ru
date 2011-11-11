<?php

IncludeModuleLangFile(__FILE__);

class C1234HoleApiXML
{
	/**
	 * Шаблон ошибки
	 */
	public static function Error($code)
	{
		return "\t".'<error code="'.htmlspecialchars($code).'">'.GetMessage('APIERROR_'.$code).'</error>'."\n";
	}
	
	/**
	 * Шаблон ямы
	 */
	public static function Hole($hole, $xml_depth = 0)
	{
		if(!$_users[$hole['USER_ID']])
		{
			$u = CUser::GetByID($hole['USER_ID']);
			$u = $u->Fetch();
			$_users[$hole['USER_ID']] = array
			(
				'NAME'        => htmlspecialchars(trim($u['NAME'])),
				'SECOND_NAME' => htmlspecialchars(trim($u['SECOND_NAME'])),
				'LAST_NAME'   => htmlspecialchars(trim($u['LAST_NAME'])),
			);
		}
		ob_start();
		?>
	<hole id="<?= $hole['ID'] ?>">
		<id><?= $hole['ID'] ?></id>
		<username full="<?= $_users[$hole['USER_ID']]['NAME'].' '.$_users[$hole['USER_ID']]['LAST_NAME'].' '.$_users[$hole['USER_ID']]['SECOND_NAME'] ?>">
			<name><?= $_users[$hole['USER_ID']]['NAME'] ?></name>
			<secondname><?= $_users[$hole['USER_ID']]['SECOND_NAME'] ?></secondname>
			<lastname><?= $_users[$hole['USER_ID']]['LAST_NAME'] ?></lastname>
		</username>
		<latitude><?= $hole['LATITUDE'] ?></latitude>
		<longitude><?= $hole['LONGITUDE'] ?></longitude>
		<address city="<?= htmlspecialchars(trim($hole['ADR_CITY'])) ?>" subjectrf="<?= $hole['ADR_SUBJECTRF'] ?>"><?= htmlspecialchars($hole['ADDRESS']) ?></address>
		<state code="<?= $hole['STATE'] ?>"><?= GetMessage('HOLE_STATE_'.$hole['STATE']) ?></state>
		<type code="<?= $hole['TYPE'] ?>"><?= GetMessage('HOLE_TYPE_'.$hole['TYPE']) ?></type>
		<datecreated readable="<?= $hole['~DATE_CREATED'] ?>"><?= $hole['DATE_CREATED'] ?></datecreated>
		<datesent readable="<?= $hole['~DATE_SENT'] ?>"><?= $hole['DATE_SENT'] ?></datesent>
		<datestatus readable="<?= $hole['~DATE_STATUS'] ?>"><?= $hole['DATE_STATUS'] ?></datestatus>
		<commentfresh><?= htmlspecialchars($hole['COMMENT1']) ?></commentfresh>
		<commentfixed><?= htmlspecialchars($hole['COMMENT2']) ?></commentfixed>
		<commentgibddre><?= htmlspecialchars($hole['COMMENT_GIBDD_REPLY']) ?></commentgibddre>
		<pictures><? echo "\n\t\t"; foreach($hole['pictures'] as $type => $p): ?>
	<<?= $type ?>>
				<fresh><? if(sizeof($p['fresh'])) { echo "\n\t"; } foreach($p['fresh'] as $k => $v): ?>
				<src><?= $v ?></src>
				<? endforeach; ?></fresh>
				<gibddreply><? if(sizeof($p['gibddreply'])) { echo "\n\t"; } foreach($p['gibddreply'] as $k => $v): ?>
				<src><?= $v ?></src>
				<? endforeach; ?></gibddreply>
				<fixed><? if(sizeof($p['fixed'])) { echo "\n\t"; } foreach($p['fixed'] as $k => $v): ?>
				<src><?= $v ?></src>
				<? endforeach; ?></fixed>
			</<?= $type ?>>
		<? endforeach; ?></pictures>
	</hole>
<?
		$hole = ob_get_clean();
		$hole = str_replace("\n", "\n".str_repeat("\t", $xml_depth), $hole);
		return $hole;
	}
	
	/**
	 * Шаблон списка регионов
	 */
	public static function GetRegions()
	{
		echo "\t<regionslist>\n";
		foreach(CGreensightRFSubject::$_RF_SUBJECTS_FULL as $k => $v)
		{
			echo "\t\t".'<region id="'.$k.'">'.$v.'</region>'."\n";
		}
		echo "\t</regionslist>\n";
	}
	
	/**
	 * Сообщить о положительном или отрицательном (что вряд ли бывает, так как
	 * в этом случае должны торчать сообщения об ошибках) результате
	 * выполнения некой процедуры (любой).
	 */
	public static function ProcedureResult($bResult = true)
	{
		echo "\t".'<callresult result="'.(int)$bResult.'">'.($bResult ? 'ok' : 'fail').'</callresult>'."\n";
	}
	
	/**
	 * Отображение авторизационных и прочих данных пользователя.
	 */
	public static function UserAuthParams()
	{
		global $USER;
		$hash = str_replace('<', '&lt;', $USER->GetParam('PASSWORD_HASH'));
		$hash = str_replace('>', '&gt;', $hash);
		?>
	<user id="<?= $USER->GetID() ?>">
		<username full="<?= htmlspecialchars(trim($USER->GetParam('NAME').' '.$USER->GetParam('LAST_NAME').' '.$USER->GetParam('SECOND_NAME'))) ?>">
			<name><?= htmlspecialchars(trim($USER->GetParam('NAME'))) ?></name>
			<secondname><?= htmlspecialchars(trim($USER->GetParam('SECOND_NAME'))) ?></secondname>
			<lastname><?= htmlspecialchars(trim($USER->GetParam('LAST_NAME'))) ?></lastname>
		</username>
		<passwordhash><?= $hash ?></passwordhash>
	</user><? echo "\n";
	}
	
	/**
	 * Шаблон предупреждения.
	 */
	public static function Warning($code)
	{
		return "\t".'<warning code="'.htmlspecialchars($code).'">'.GetMessage('APIWARNING_'.$code).'</warning>'."\n";
	}
}

?>