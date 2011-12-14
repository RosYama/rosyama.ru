<?
global $USER;
$hole = $arResult['HOLE'];
?>
<div class="rCol">
	<div class="h">
		<div class="info">
			<p><span class="date"><?= $hole['~DATE_CREATED'] ?></span><?= htmlspecialcharsEx(strlen($arResult['AUTHOR']['NAME'].$arResult['AUTHOR']['LAST_NAME']) ? $arResult['AUTHOR']['NAME'].' '.$arResult['AUTHOR']['LAST_NAME'] : $arResult['AUTHOR']['LOGIN']) ?></p>
			<p class="type type_<?= $hole['TYPE'] ?>"><?= GetMessage('HOLE_TYPE_'.$hole['TYPE']) ?></p>
			<p class="address"><?= htmlspecialcharsEx($hole['ADDRESS']) ?></p>
			<p class="status">
				<span class="bull <?= $hole['STATE'] ?>">&bull;</span>
				<span class="state">
					<?= GetMessage('HOLE_STATE_'.$hole['STATE']) ?>
					<? if($hole['STATE'] == 'prosecutor' && $hole['DATE_STATUS']): ?>
						<?= $hole['~DATE_STATUS'] ?>
					<? elseif($hole['STATE'] != 'fixed' && $hole['~DATE_SENT']): ?>
						<?= $hole['~DATE_SENT'].' '.GetMessage('HOLE_REQUEST_SENT') ?>
					<? endif; ?>
					<? if($hole['STATE'] == 'fixed' && $hole['DATE_STATUS']): ?>
						<?= $hole['~DATE_STATUS'].' '.GetMessage('HOLE_FIXED') ?>
					<? endif; ?>
				</span>
			</p>
			<div class="control">
			<div class="progress">
			<? if($arResult['HOLE']['WAIT_DAYS']): ?>
			<div class="lc">
				<div class="wait">
					<p><?= GetMessage('WAIT') ?></p>
					<p class="days"><?= $arResult['HOLE']['WAIT_DAYS'] ?></p>
				</div>
			</div>
			<? elseif($arResult['HOLE']['PAST_DAYS']): ?>
			<div class="lc">
				<div class="wait">
					<p><?= GetMessage('PAST') ?></p>
					<p class="days"><?= $arResult['HOLE']['PAST_DAYS'] ?></p>
				</div>
			</div>
			<? endif; ?>
			<? if($USER->GetID() == $hole['USER_ID'] || $USER->IsAdmin()): ?>
				<?
				if($USER->IsAdmin())
				{
					ShowError('Вы обладаете административными полномочиями<br>');
				}
				if($arParams['PREMODERATION'] == 'Y' && !$hole['PREMODERATED'])
				{
					ShowError(GetMessage('PREMODRATION_WARNING'));
				}
				switch($hole['STATE'])
				{
					case 'fresh':
					{
						?>
						<div class="edit">
							<a href="/personal/edit.php?ID=<?= $hole['ID'] ?>"><?= GetMessage('HOLE_CART_ADMIN_TEXT_2') ?></a><a onclick="return confirm('<?= GetMessage('HOLE_CART_ADMIN_TEXT_11') ?>');" href="/personal/edit.php?DEL_ID=<?= $hole['ID'] ?>"><?= GetMessage('HOLE_CART_ADMIN_TEXT_10') ?></a>
						</div>
						<div class="progress">
							<div class="lc">
								<a href="#" onclick="var c=document.getElementById('pdf_form');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;" class="printDeclaration"><?= GetMessage('HOLE_CART_ADMIN_TEXT_4') ?></a>
							</div>
							<div class="cc">
								<p><a href="/personal/edit.php?SENT_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_6') ?></a></p>
								<p><a href="/personal/edit.php?FIX_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_8a') ?></a></p>
							</div>
							<div class="rc">
								Также можно отправить:<br />
								<span><ins>&mdash;</ins>с&nbsp;<a href="/about/112/">сайта 112.ru</a></span>
								<span><ins>&mdash;</ins>с&nbsp;официального сайта <a href="http://www.gibdd.ru/letter" target="_blank">ГИБДД&nbsp;МВД&nbsp;России</a></span>
							</div>
						</div>
						<?
						break;
					}
					case 'inprogress':
					{
						?>
							<div class="cc" style="width:150px">
								<p><?= GetMessage('HOLE_CART_ADMIN_TEXT_7') ?></p>
								<p><a href="/personal/edit.php?FIX_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_8') ?></a></p>
							</div>
							<div class="rc" style="width:145px;padding: 24px 0 24px 15px;">
								<p><a class="declarationBtn" href="#" onclick="var c=document.getElementById('pdf_form');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;"><?= GetMessage('HOLE_CART_ADMIN_TEXT_15') ?></a></p>
								<p><a href="/personal/edit.php?CANCEL_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_12') ?></a></p>
								<p><a href="/personal/edit.php?GIBDD_REPLY_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_GIBDD_REPLY_RECEIVED') ?></a></p>
							</div>
						<?
						break;
					}
					case 'gibddre':
					{
						?>
							<div class="lc" style="width:150px">
								<p><?= GetMessage('HOLE_CART_ADMIN_TEXT_7') ?></p>
								<p><a href="/personal/edit.php?FIX_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_8') ?></a></p>
							</div>
							<div class="cc"><a href="/personal/edit.php?GIBDD_REPLY_ID=<?= $hole['ID'] ?>">Ещё ответ из ГИБДД</a></div>
							<div class="rc" style="width:145px;padding: 24px 0 24px 15px;">
								<p>Если вас не устраивает ответ ГИБДД, то можно</p>
								<p><a href="#" onclick="var c=document.getElementById('prosecutor_form2');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;">подать Заявление в Прокуратуру</a></p>
								<div class="pdf_form" id="prosecutor_form2"<?= isset($_GET['show_prosecutor_form2']) ? ' style="display: block;"' : '' ?>>
									<a href="#" onclick="var c=document.getElementById('prosecutor_form2');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;" class="close">&times;</a>
									<form action="?pdf" method="post" onsubmit="document.getElementById('prosecutor_form2').style.display='none';">
										<input type="hidden" name="form_type" value="prosecutor2">
										<?= GetMessage('HOLE_PROSECUTOR_FORM2_PREFACE') ?>
										<table>
											<tr>
												<th><?= GetMessage('HOLE_PROSECUTOR_FORM_TO') ?></th>
												<td><textarea rows="3" cols="40" id="prosecurtor_form_to" name="to"><?= $arResult['PROSECUTOR_FORM_TO'] ?></textarea></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_PROSECUTOR_FORM_FROM') ?></th>
												<td><textarea rows="3" cols="40" id="presecutor_form_from" name="from"><?= htmlspecialcharsEx($arResult['AUTHOR']['LAST_NAME'].' '.$arResult['AUTHOR']['NAME'].' '.$arResult['AUTHOR']['SECOND_NAME']) ?></textarea></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_PROSECUTOR_FORM_POSTADDRESS') ?><span class="comment"><?= GetMessage('HOLE_PROSECUTOR_FORM_POSTADDRESS_COMMENT') ?></span></th>
												<td><textarea rows="3" cols="40" id="prosecutor_form_postaddress" name="postaddress"></textarea></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_PRESECUTOR_FORM_ADDRESS') ?></th>
												<td><textarea rows="3" cols="40" id="prosecutor_form_address" name="address"><?= htmlspecialcharsEx($hole['ESS']) ?></textarea></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_PRESECUTOR_FORM_GIBDD') ?><span class="comment"><?= GetMessage('HOLE_PRESECUTOR_FORM_GIBDD_COMMENT') ?></span></th>
												<td><textarea rows="3" cols="40" id="prosecutor_form_address" name="gibdd"><?= $arResult['PROSECUTOR_GIBDD'] ?></textarea></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_PRESECUTOR_FORM_GIBDD_REPLY') ?><span class="comment"><?= GetMessage('HOLE_PRESECUTOR_FORM_GIBDD_COMMENT2') ?></span></th>
												<td><textarea rows="3" cols="40" id="prosecutor_form_gibdd_reply" name="gibdd_reply"></textarea></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_PROSECUTOR_FORM_APPLICATION_DATA') ?></th>
												<td><input type="text" id="prosecutor_form_application" name="application_data"></td>
											</tr>
											<tr>
												<th><?= GetMessage('HOLE_REQUEST_FORM_SIGNATURE') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_SIGNATURE_COMMENT') ?></span></th>
												<td><input type="text" class="textInput" id="pdf_form_signature" name="signature" value="<?= htmlspecialcharsEx($arResult['AUTHOR']['LAST_NAME'].' '.substr($arResult['AUTHOR']['NAME'], 0, 1).($arResult['AUTHOR']['NAME'] ? '.' : '').' '.substr($arResult['AUTHOR']['SECOND_NAME'], 0, 1).($arResult['AUTHOR']['SECOND_NAME'] ? '.' : '')) ?>"></td>
											</tr>
											<tr>
												<th></th>
												<td>
													<input type="submit" class="submit" value="<?= GetMessage('HOLE_REQUEST_FORM_SUBMIT') ?>">
													<input type="submit" name="html" class="submit" value="<?= GetMessage('HOLE_REQUEST_FORM_SUBMIT2') ?>">
												</td>
											</tr>
										</table>
										<strong><?= htmlspecialcharsEx($arResult['PROSECUTOR_DATA']['NAME']) ?></strong>
										<p><?= strip_tags($arResult['PROSECUTOR_DATA']['PREVIEW_TEXT'], '<br>') ?></p>
									</form>
								</div>
							</div>
						<?
						break;
					}
					case 'achtung':
					{
						?>
						<div class="cc" style="width:150px">
							<p><?= GetMessage('HOLE_CART_ADMIN_TEXT_7') ?></p>
							<a href="/personal/edit.php?FIX_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_8') ?></a>
						</div>
						<div class="rc" style="width:184px;padding: 24px 0 24px 15px;">
							<p><?= GetMessage('HOLE_CART_ADMIN_TEXT_16') ?></p>
							<p><a href="#" onclick="var c=document.getElementById('prosecutor_form');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_14') ?></a></p>
							<p><a href="/personal/edit.php?PROSECUTOR_ID=<?= $hole['ID'] ?>" class="declarationBtn">Жалоба в прокуратуру подана</a></p>
							<p><a href="/personal/edit.php?GIBDD_REPLY_ID=<?= $hole['ID'] ?>" class="declarationBtn">Ответ из ГИБДД</a></p>
						</div>
						<div class="pdf_form" id="prosecutor_form"<?= isset($_GET['show_prosecutor_form']) ? ' style="display: block;"' : '' ?>>
							<a href="#" onclick="var c=document.getElementById('prosecutor_form');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;" class="close">&times;</a>
							<form action="?pdf" method="post" onsubmit="document.getElementById('prosecutor_form').style.display='none';">
								<input type="hidden" name="form_type" value="prosecutor">
								<?= GetMessage('HOLE_PROSECUTOR_FORM_PREFACE') ?>
								<table>
									<tr>
										<th><?= GetMessage('HOLE_PROSECUTOR_FORM_TO') ?></th>
										<td><textarea rows="3" cols="40" id="prosecurtor_form_to" name="to"><?= $arResult['PROSECUTOR_FORM_TO'] ?></textarea></td>
									</tr>
									<tr>
										<th><?= GetMessage('HOLE_PROSECUTOR_FORM_FROM') ?></th>
										<td><textarea rows="3" cols="40" id="presecutor_form_from" name="from"><?= htmlspecialcharsEx($arResult['AUTHOR']['LAST_NAME'].' '.$arResult['AUTHOR']['NAME'].' '.$arResult['AUTHOR']['SECOND_NAME']) ?></textarea></td>
									</tr>
									<tr>
										<th><?= GetMessage('HOLE_PROSECUTOR_FORM_POSTADDRESS') ?><span class="comment"><?= GetMessage('HOLE_PROSECUTOR_FORM_POSTADDRESS_COMMENT') ?></span></th>
										<td><textarea rows="3" cols="40" id="prosecutor_form_postaddress" name="postaddress"></textarea></td>
									</tr>
									<tr>
										<th><?= GetMessage('HOLE_PRESECUTOR_FORM_ADDRESS') ?></th>
										<td><textarea rows="3" cols="40" id="prosecutor_form_address" name="address"><?= htmlspecialcharsEx($hole['ADDRESS']) ?></textarea></td>
									</tr>
									<tr>
										<th><?= GetMessage('HOLE_PRESECUTOR_FORM_GIBDD') ?><span class="comment"><?= GetMessage('HOLE_PRESECUTOR_FORM_GIBDD_COMMENT') ?></span></th>
										<td><textarea rows="3" cols="40" id="prosecutor_form_address" name="gibdd"><?= $arResult['PROSECUTOR_GIBDD'] ?></textarea></td>
									</tr>
									<tr>
										<th><?= GetMessage('HOLE_PROSECUTOR_FORM_APPLICATION_DATA') ?><span class="comment"><?= GetMessage('HOLE_PROSECUTOR_FORM_APPLICATION_DATA_COMMENT') ?></span></th>
										<td><input type="text" id="prosecutor_form_application" name="application_data"></td>
									</tr>
									<tr>
										<th><?= GetMessage('HOLE_REQUEST_FORM_SIGNATURE') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_SIGNATURE_COMMENT') ?></span></th>
										<td><input type="text" class="textInput" id="pdf_form_signature" name="signature" value="<?= htmlspecialcharsEx($arResult['AUTHOR']['LAST_NAME'].' '.substr($arResult['AUTHOR']['NAME'], 0, 1).($arResult['AUTHOR']['NAME'] ? '.' : '').' '.substr($arResult['AUTHOR']['SECOND_NAME'], 0, 1).($arResult['AUTHOR']['SECOND_NAME'] ? '.' : '')) ?>"></td>
									</tr>
									<tr>
										<th></th>
										<td>
											<input type="submit" class="submit" value="<?= GetMessage('HOLE_REQUEST_FORM_SUBMIT') ?>">
											<input type="submit" name="html" class="submit" value="<?= GetMessage('HOLE_REQUEST_FORM_SUBMIT2') ?>">
										</td>
									</tr>
								</table>
								<strong><?= htmlspecialcharsEx($arResult['PROSECUTOR_DATA']['NAME']) ?></strong>
								<p><?= strip_tags($arResult['PROSECUTOR_DATA']['PREVIEW_TEXT'], '<br>') ?></p>
							</form>
						</div>
						<?
						break;
					}
					case 'prosecutor':
					{
						?>
						<div class="lc" style="width:150px">
							<p><?= GetMessage('HOLE_CART_ADMIN_TEXT_7') ?></p>
							<a href="/personal/edit.php?FIX_ID=<?= $hole['ID'] ?>" class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_8') ?></a>
						</div>
						<div class="cc">
							<a href="/personal/edit.php?REPROSECUTOR_ID=<?= $hole['ID'] ?>" class="declarationBtn">Аннулировать факт отправки заявления в прокуратуру</a>
						</div>
						<?
						break;
					}
					case 'fixed':
					default:
					{
						if($arResult['allow_cancel_fix'])
						{
							?>
							<a href="/personal/edit.php?REFIX_ID=<?= $hole['ID'] ?>"  class="declarationBtn"><?= GetMessage('HOLE_CART_ADMIN_TEXT_13') ?></a>
							<?
						}
						break;
					}
				}
				?>
				<div class="pdf_form" id="pdf_form"<?= isset($_GET['show_pdf_form']) ? ' style="display: block;"' : '' ?>>
					<a href="#" onclick="var c=document.getElementById('pdf_form');if(c){c.style.display=c.style.display=='block'?'none':'block';}return false;" class="close">&times;</a>
					Не исключена вероятность того, что на <a href="http://www.gosuslugi.ru/ru/chorg/index.php?ssid_4=4120&stab_4=4&rid=228&tid=2" target="_blank">сайте госуслуг</a> окажется немного полезной информации.
					<form action="?pdf" method="post" onsubmit="document.getElementById('pdf_form').style.display='none';">
						<h2><?= GetMessage('HOLE_REQUEST_FORM') ?></h2>
						<table>
							<tr>
								<th><?= GetMessage('HOLE_REQUEST_FORM_TO') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_TO_COMMENT') ?></span></th>
								<td><textarea rows="3" cols="40" id="pdf_form_to" name="to"><?= $arResult['PDF_FORM_TO'] ?></textarea></td>
							</tr>
							<tr>
								<th><?= GetMessage('HOLE_REQUEST_FORM_FROM') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_FROM_COMMENT') ?></span></th>
								<td><textarea rows="3" cols="40" id="pdf_form_from" name="from"><?= htmlspecialcharsEx($arResult['AUTHOR']['LAST_NAME'].' '.$arResult['AUTHOR']['NAME'].' '.$arResult['AUTHOR']['SECOND_NAME']) ?></textarea></td>
							</tr>
							<tr>
								<th><?= GetMessage('HOLE_REQUEST_FORM_POSTADDRESS') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_POSTADDRESS_COMMENT') ?></span></th>
								<td><textarea rows="3" cols="40" id="pdf_form_postaddress" name="postaddress"></textarea></td>
							</tr>
							<tr>
								<th><?= GetMessage('HOLE_REQUEST_FORM_ADDRESS') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_ADDRESS_COMMENT') ?></span></th>
								<td><textarea rows="3" cols="40" id="pdf_form_address" name="address"><?= htmlspecialcharsEx($hole['ADDRESS']) ?></textarea></td>
							</tr>
							<? if($hole['TYPE'] == 'light'): ?>
								<tr>
									<th><?= GetMessage('HOLE_REQUEST_FORM_COMMENT') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_COMMENT_COMMENT') ?></span></th>
									<td><textarea rows="3" cols="40" id="pdf_form_comment" name="comment"></textarea></td>
								</tr>
							<? endif; ?>
							<tr>
								<th><?= GetMessage('HOLE_REQUEST_FORM_SIGNATURE') ?><span class="comment"><?= GetMessage('HOLE_REQUEST_FORM_SIGNATURE_COMMENT') ?></span></th>
								<td><input type="text" class="textInput" id="pdf_form_signature" name="signature" value="<?= htmlspecialcharsEx($arResult['AUTHOR']['LAST_NAME'].' '.substr($arResult['AUTHOR']['NAME'], 0, 1).($arResult['AUTHOR']['NAME'] ? '.' : '').' '.substr($arResult['AUTHOR']['SECOND_NAME'], 0, 1).($arResult['AUTHOR']['SECOND_NAME'] ? '.' : '')) ?>"></td>
							</tr>
							<tr>
								<th></th>
								<td>
									<input type="submit" class="submit" value="<?= GetMessage('HOLE_REQUEST_FORM_SUBMIT') ?>">
									<input type="submit" name="html" class="submit" value="<?= GetMessage('HOLE_REQUEST_FORM_SUBMIT2') ?>">
								</td>
							</tr>
						</table>
					</form>
					<?= GetMessage('ST1234_INSTRUCTION') ?>
				</div>
			<? endif; ?>
			</div>
			</div>
		</div>
		<div class="social">
			<div class="like">
				<!-- Facebook like -->
				<div id="fb_like">
					<iframe src="http://www.facebook.com/plugins/like.php?href=http://<?=SITE_SERVER_NAME?><?=$APPLICATION->GetCurPage()?>&amp;layout=button_count&amp;show_faces=false&amp;width=180&amp;action=recommend&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:180px; height:21px;" allowTransparency="true"></iframe>
				</div>
				<!-- Vkontakte like -->
				<div id="vk_like"></div>
				<script type="text/javascript">VK.Widgets.Like("vk_like", {type: "button", verb: 1});</script>
			</div>
			<div class="share">
				<span>Поделиться</span>
				<a href="http://www.facebook.com/sharer.php?u=http://<?=SITE_SERVER_NAME?><?=$APPLICATION->GetCurPage()?>" class="fb" target="_blank">Facebook</a>
				<a href="http://vkontakte.ru/share.php?url=http://<?=SITE_SERVER_NAME?><?=$APPLICATION->GetCurPage()?>" class="vk" target="_blank">VK</a>
				<a href="http://twitter.com/share" class="twitter-share-button" data-text="Обнаружен дефект на дороге по адресу <?= htmlspecialcharsEx($hole['ADDRESS']) ?>" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
			</div>
		</div>
	</div>
</div>
<!-- CLOSE HEAD CONTAINER -->
</div>
<!-- CLOSE HEAD -->
</div>
<div class="mainCols" id="col">
	<div class="lCol">
		<script src="http://api-maps.yandex.ru/1.1/index.xml?key=<?= $arResult['YANDEX_MAP_KEY'] ?>&onerror=apifault" type="text/javascript"></script>
		<div id="ymapcontainer_big"><div align="right"><span class="close" onclick="document.getElementById('ymapcontainer_big').style.display='none';$('#col').css('marginBottom',0)">&times;</span></div><div id="ymapcontainer_big_map"></div></div>
		<?if($hole['LATITUDE'] && $hole['LONGITUDE']):?><div id="ymapcontainer" class="ymapcontainer"></div><?endif;?>
		<script type="text/javascript">
			var map_centery = <?= $hole['LATITUDE'] ?>;
			var map_centerx = <?= $hole['LONGITUDE'] ?>;
			var map = new YMaps.Map(YMaps.jQuery("#ymapcontainer")[0]);
			YMaps.Events.observe(map, map.Events.DblClick, function () { toggleMap(); } );
			map.enableScrollZoom();
			map.setCenter(new YMaps.GeoPoint(map_centerx, map_centery), 14);
			var s = new YMaps.Style();
			s.iconStyle = new YMaps.IconStyle();
			s.iconStyle.href = "/images/st1234/<?= $hole['TYPE']?>_<?= $hole['STATE'] ?>.png";
			s.iconStyle.size = new YMaps.Point(54, 61);
			s.iconStyle.offset = new YMaps.Point(-30, -61);
			var placemark = new YMaps.Placemark(new YMaps.GeoPoint(map_centerx, map_centery), { hideIcon: false, hasBalloon: false, style: s } );
			YMaps.Events.observe(placemark, placemark.Events.Click, function () { toggleMap(); } );
			map.addOverlay(placemark);
		</script>
		
		<div class="comment">
			<?= $hole['COMMENT1'] ?>
		</div>
		<div class="bbcode">
			<p><b>Ссылка на эту страницу:</b></p>
			<input onfocus="selectAll(this)" type="text" value="<?="<a href='http://".$_SERVER["SERVER_NAME"].$APPLICATION->GetCurPage()."'>РосЯма :: ".htmlspecialcharsEx($hole['ADDRESS'])."</a>"?>"/>
			<p><b>BBcode для форума:</b></p>
			<textarea onfocus="selectAll(this)" rows="3">[url=http://<?=$_SERVER["SERVER_NAME"].$APPLICATION->GetCurPage()?>][img]http://<?=$_SERVER["SERVER_NAME"].$hole['pictures']['medium']['fresh'][0]?>[/img][/url][url=http://<?=$_SERVER["SERVER_NAME"].$APPLICATION->GetCurPage()?>] 
			РосЯма :: <?=htmlspecialcharsEx($hole['ADDRESS'])?>[/url]</textarea>
		</div>
</div>
<div class="rCol">
	<div class="b">
		<div class="before">
			<? foreach($hole['pictures']['medium']['fresh'] as $src): ?>
				<img src="<?= $src ?>">
			<? endforeach; ?>
		</div>
		<? if(sizeof($hole['pictures']['medium']['gibddreply'])): ?>
			<div class="after">
				<? if($hole['COMMENT_GIBDD_REPLY']): ?>
				<div class="comment">
					<?= $hole['COMMENT_GIBDD_REPLY'] ?>
				</div>
				<? endif; ?>
				<h2><?= GetMessage('HOLE_GIBDDREPLY') ?></h2>
				<? foreach($hole['pictures']['medium']['gibddreply'] as $k => $src): ?>
					<?
					$img_id = explode('/', $src);
					$img_id = preg_replace('/\D/', '', $img_id[sizeof($img_id) - 1]);
					?>
					<br />
					<p id="gibddreimg_<?= $img_id ?>">
						<strong><?= date('Y.m.d', $hole['pictures']['filectime']['gibddreply'][$k]) ?></strong>
						<? if($USER->GetID() == $hole['USER_ID'] || $USER->IsAdmin()): ?>
							<a class="declarationBtn" onclick="gibddre_img_del('<?= $hole['ID'] ?>', '<?= $img_id ?>')">Удалить это изображение</a>
						<? endif; ?>
						<br />
						<img src="<?= $src ?>">
					</p>
				<? endforeach; ?>
			</div>
		<? endif; ?>
		<? if($hole['STATE'] == 'fixed'): ?>
			<div class="after">
				<? if(sizeof($hole['pictures']['medium']['fixed'])): ?>
					<h2><?= GetMessage('HOLE_ITBECAME') ?></h2>
					<? foreach($hole['pictures']['medium']['fixed'] as $src): ?>
						<img src="<?= $src ?>">
					<? endforeach; ?>
				<? endif; ?>
				<? if($hole['COMMENT2']): ?>
					<div class="comment">
						<?= $hole['COMMENT2'] ?>
					</div>
				<? endif; ?>
			</div>
		<? endif; ?>
	</div>
</div>