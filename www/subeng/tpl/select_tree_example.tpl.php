<div class="promo">
	<header>
		<h1><?=$lang['example_select_tree_component']?></h1>
	</header>
	<article>
		<header>
			<h3><?=$lang['example_location_select_component']?></h3>
		</header>
		<fieldset>
			<legend>
				Location example
			</legend>
			<?=$handler->location_inputs->block() ?>
		</fieldset>
		<header>
			<h3><?=$lang['example_select_tree_component']?></h3>
		</header>
		<fieldset>
			<legend>
				Small catalog categories
			</legend>
			<?=$handler->small_categories_list->block() ?>
		</fieldset>
		<fieldset>
			<legend>
				Big catalog categories
			</legend>
			<?=$handler->big_categories_list->block() ?>
		</fieldset>
	</article>
	<?php for ($i = 0; $i < 50; $i++) {?>
	<fieldset>
			<legend>
				Big catalog categories
			</legend>
			<?=$handler->big_categories_list->block() ?>
		</fieldset>
	<?php }?>
	<?=csrf()?>
</div>
