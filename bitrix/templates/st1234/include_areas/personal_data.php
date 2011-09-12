<?php
global $USER;
$user_id = $USER->GetID();
if(!$user_id)
{
	return;
}
if(!CModule::IncludeModule('st1234holes'))
{
	return;
}
IncludeTemplateLangFile(__FILE__);
$_user = $USER->GetByID($user_id);
$_user = $_user->Fetch();
if($_user['PERSONAL_PHOTO'])
{
	$_user['PERSONAL_PHOTO'] = CFile::GetById($_user['PERSONAL_PHOTO']);
	$_user['PERSONAL_PHOTO'] = $_user['PERSONAL_PHOTO']->Fetch();
}
$_user_holes = C1234Hole::GetList(array(), array('USER_ID' => $user_id), array('nopicts' => true));
$fixed = 0;
$holes = 0;
global $fresh;
$fresh = 0;
foreach($_user_holes as $hole)
{
	$holes++;
	if($hole['STATE'] == 'fixed')
	{
		$fixed++;
	}
	if($hole['STATE'] == 'fresh')
	{
		$fresh++;
	}


}
$holes = (string)$holes;
if(substr($holes, strlen($holes) - 2, 1) == '1') // 10...19
{
	$holes .= ' '.GetMessage('GS_HOLES10');
}
elseif(substr($holes, strlen($holes) - 1, 1) == 1) // 1
{
	$holes .= ' '.GetMessage('GS_HOLES1');
}
elseif(substr($holes, strlen($holes) - 1, 1) < 5 && $holes) // 2...4
{
	$holes .= ' '.GetMessage('GS_HOLES2');
}
else // 5...9, 0
{
	$holes .= ' '.GetMessage('GS_HOLES10');
}
$fixed = (string)$fixed;
if(substr($fixed, strlen($fixed) - 2, 1) == '1' && $fixed > 1) // 10...19
{
	$fixed .= ' '.GetMessage('GS_FIXED10');
}
elseif(substr($fixed, strlen($fixed) - 1, 1) == 1) // 1
{
	$fixed .= ' '.GetMessage('GS_FIXED1');
}
elseif(substr($fixed, strlen($fixed) - 1, 1) < 5 && $fixed) // 2...4
{
	$fixed .= ' '.GetMessage('GS_FIXED2');
}
else // 5...9, 0
{
	$fixed .= ' '.GetMessage('GS_FIXED10');
}
unset($_user_holes);
?>
<div id="head_user_info">
	<div class="photo">
		<? if($_user['PERSONAL_PHOTO']): ?><img src="/upload/<?= $_user['PERSONAL_PHOTO']['SUBDIR'].'/'.$_user['PERSONAL_PHOTO']['FILE_NAME'] ?>"><? endif; ?>
	</div>
	<div class="info">
		<div class="buttons">
			<?
			switch($APPLICATION->GetCurPage())
			{
				case '/personal/holes.php':
				{
					?><a href="add.php" class="profileBtn"><?= GetMessage('GS_RMENU_ADD') ?></a><a href="index.php" class="profileBtn"><?= GetMessage('GS_RMENU_PROFILE') ?></a><?
					break;
				}
				case '/personal/add.php':
				{
					?><a href="holes.php" class="profileBtn"><?= GetMessage('GS_RMENU_LIST') ?></a><a href="index.php" class="profileBtn"><?= GetMessage('GS_RMENU_PROFILE') ?></a><?
					break;
				}
				case '/personal/index.php':
				default:
				{
					?><a href="add.php" class="profileBtn"><?= GetMessage('GS_RMENU_ADD') ?></a><a href="holes.php" class="profileBtn"><?= GetMessage('GS_RMENU_LIST') ?></a><?
					break;
				}
			}
			?>
		</div>
		<h1><?= htmlspecialcharsEx($_user['NAME'].' '.$_user['LAST_NAME']) ?></h1>
		<div class="www">
			<a target="_blank" href="<?= (substr(ToLower($_user['PERSONAL_WWW']), 0, 7) == 'http://' ? '' : 'http://').htmlspecialcharsEx($_user['PERSONAL_WWW']) ?>"><?= htmlspecialcharsEx($_user['PERSONAL_WWW']) ?></a>
		</div>
	</div>
	<div class="counter">
		<?= $holes ?> / <?= $fixed ?>
		
	</div>
</div>