<? foreach($arResult['ITEMS'] as $item): ?>
	<span class="item" onclick="<?= $item['onclick'] ?>"><?= $item['text'] ?></span>
<? endforeach; ?>