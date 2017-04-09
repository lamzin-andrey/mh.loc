<?php if(count($handler->messages)):?>
	<div class="success">
	<?php foreach( $handler->messages as $s ):?>
		<div> <?=$s?> </div>
	<?php endforeach ?>
	</div>
<?php endif?>