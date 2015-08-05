<?php if(count($handler->errors)):?>
	<div class="danger">
	<?php foreach( $handler->errors as $err ):?>
		<div> <?=$err?> </div>
	<?php endforeach ?>
	</div>
<?php endif?>