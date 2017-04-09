<div class="promo">
	<? if ($handler->_remind_message):?>
	<section class="success message_block"><?=$handler->_remind_message?></section>
	<? endif ?>
	<? if ($handler->_remindError):?>
	<section class="danger message_block"><?=$handler->_remindError?></section>
	<? endif ?>
</div>
