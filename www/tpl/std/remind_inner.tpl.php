<div class="promo">
	<? if ($handler->_remind_message):?>
	<section class="success message_block"><?=$handler->_remind_message?></section>
	<? endif ?>
	<? if ($handler->_remindError):?>
	<section class="danger message_block"><?=$handler->_remindError?></section>
	<? endif ?>
	
	<div class="aformwrap remind_wrap">
		<form action="<?=WEB_ROOT?>/remind?action=getpwd" method="POST">
				<div class="aphone">
					<label class="slabel" for="email">Email</label><br>
					<input type="email" id="email" name="email" >
				</div>
			
				<div class="remindcap">
					<label class="slabel" for="str"><?=$lang['Captcha_remind_legend']?></label><br>
					<img id="refimg" src="<?=WEB_ROOT?>/img/random">
				</div>
				<div class="aphone">
					<input type="text" value="" id="regfstr" name="regfstr">
				</div>
				<div class="right prmf">
					<input type="hidden" name="action" value="sendmail">
					<input type="submit" value="<?=$lang['PasswordRecoveryLabel']?>" class="btn">
				</div>
				<div class="clearfix">&nbsp;</div>
		</div>
		</form>
	</div>
</div>
