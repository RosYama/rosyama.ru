<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
	<?if($APPLICATION->GetCurPage()=="/" || $APPLICATION->GetCurPage() == '/personal/add.php' || defined("ERROR_404") || substr($APPLICATION->GetCurPage(), 0, 10) == '/personal/'):?>
	</div>
	<?else:?>
		</div>
	</div>
	<?endif;?>
</div>
<div class="footer">
	<div class="container">
		<p class="autochmo"><a target="_blank" href="http://autochmo.ru/" title="Доска позора водителей &aring;вточмо">&aring;utochmo</a><br>Доска позора водителей</p>
		<p class="copy">&copy; <a href="http://navalny.ru/">Алексей Навальный</a>, 2011<br />
		<a href="mailto:rossyama@gmail.com">rossyama@gmail.com</a><br />
		Сделано в <a href="http://greensight.ru" target="_blank">Greensight</a><br /><br />
		<? if(preg_match('/^\/+[0-9]+\//', $APPLICATION->GetCurPage()) || $APPLICATION->GetCurPage() == '/'){?>
		<a <? if(isset($_GET['ID'])){?>
				href="/<?=(int) $_GET['ID']?>/?print=Y"
			<?}else{?>
				href="/?print=Y
				<?=!empty($_REQUEST['p']) ? htmlspecialchars('&p='.$_REQUEST['p']) : ''?>
				<?=!empty($_REQUEST['filter_status']) ? htmlspecialchars('&filter_status='.$_REQUEST['filter_status']) : ''?>
				<?=!empty($_REQUEST['filter_rf_subject']) ? htmlspecialchars('&filter_rf_subject='.$_REQUEST['filter_rf_subject']) : ''?>
				<?=!empty($_REQUEST['filter_type']) ? htmlspecialchars('&filter_type='.$_REQUEST['filter_type']) : ''?>
				<?=!empty($_REQUEST['filter_city']) ? htmlspecialchars('&filter_city='.$_REQUEST['filter_city']) : ''?>"
			<?}?>
			target="_blank">
			Версия для печати
		</a>
		<?}?>
		<? $APPLICATION->IncludeComponent('greensight:holes.counter', 'footer'); ?>
		<p class="friends">Чиним ямы <a href="http://ukryama.com/">в Украине</a>, <a href="http://belyama.by/">Беларуси</a> и <a href="http://kazyama.kz/">Казахстане</a></p>
	</div>
</div>

<script type="text/javascript">
 var reformalOptions = {
  project_id: 43983,
  project_host: "rosyama.reformal.ru",
  force_new_window: false,
  tab_alignment: "left",
  tab_top: "316",
  tab_image_url: "http://reformal.ru/files/images/buttons/reformal_tab_orange.png"
 };
        
 (function() {
  if ('https:' == document.location.protocol) return;
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'http://media.reformal.ru/widgets/v1/reformal.js';
  document.getElementsByTagName('head')[0].appendChild(script);
 })();
</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-21943923-3']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<?global $fresh?>
<? if ($_GET["login"]=="yes"&&$fresh>0):?>
	<div id="addDiv">
		<div id="fon">
		</div>
		<div id="popupdiv">
		
			<h1>Добрый день, <?= strlen($USER->GetFirstName()) ? $USER->GetFirstName() : $USER->GetLogin() ?></h1>
			<p>Вы добавили на сайт <?= $fresh;?> ям, по которым не было подано заявление в ГИБДД. Обращаем внимание, что публикация здесь не влечет за собой автоматического исправления дефекта на дороге.</p>
			 <span class="filterBtn close">
				<i class="text">Продолжить</i>
			 </span>
		</div>
	</div>
	
	<script type="text/javascript">
	$(document).ready(function(){				
		$('.close').click(function(){
			$('#popupdiv').fadeOut(400);
			$('#fon').fadeOut(600);
			$('#addDiv').fadeOut(800);
		})
	})

	</script>
<?endif?>

</body>
</html>