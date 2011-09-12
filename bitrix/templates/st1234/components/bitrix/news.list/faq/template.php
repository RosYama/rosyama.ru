<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?foreach($arResult["ITEMS"] as $arItem):?>
	<table class="faq-item">
		<tr>
			<th>Q:</th>
			<td class="question"><?=$arItem['NAME']?></td>
		</tr>		
		<tr>
			<th>A:</th>
			<td><?=$arItem['DETAIL_TEXT']?></td>
		</tr>		
	</table>	
<?endforeach;?>





