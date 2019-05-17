<div class="promo">
	<header>
		<h1><?=$lang['example_select_tree_component']?></h1>
	</header>
	<article>
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
	<?=csrf()?>
</div>
