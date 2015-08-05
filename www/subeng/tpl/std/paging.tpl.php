<ul id="paglist" class="inline">
<?php foreach ($handler->paging as $p) {?>
	<li <? if (isset($p->active)) {?>class="active"<?}?> ><? if (!isset($p->active)) {?><a href="<?=utils_setUrlVar("page", $p->n); ?>"><?
		print (isset($p->text) ? $p->text : $p->n);
	 ?></a><?} else { 
	 	?><span><?=$p->n ?></span><?php
         }?></li>
<?php }?>
</ul>
