<div class="promo">
	<? if ($handler->_remind_message):?>
	<section class="success message_block"><?=$handler->_remind_message?></section>
	<? endif ?>
	<? if ($handler->_remindError):?>
	<section class="danger message_block"><?=$handler->_remindError?></section>
	<? endif ?>
	<div><?=$lang['enter_new_password']?></div>
	<div class="aformwrap remind_wrap">
		<form action="/remind?action=getpwd" method="POST">
				<div class="apwd">
					<label class="slabel" for="remindpassword"><?=$lang['Password']?></label><span id="password_validate" class="password_no_equ hide"><?=$lang['easy_password']?></span><br>
					<input type="password" value="" id="remindpassword" name="remindpassword">
				</div>
				<div class="apwd">
					<label class="slabel" for="remind_password_confirm"><?=$lang['Password_confirmation']?></label><span id="password_equ" class="password_no_equ hide"><?=$lang['password_not_match']?></span><br>
					<input type="password" value="" id="remind_password_confirm" name="remind_password_confirm">
				</div>
				<div class="right prmf">
					<input type="hidden" name="action" value="recovery">
					<input type="submit" value="<?=$lang['PasswordResetLabel']?>" class="btn">
				</div>
				<div class="clearfix">&nbsp;</div>
		</div>
		</form>
	</div>
</div>
