<noindex>
	<div class="pt20">
		<div class="center w400p bordblock">
			<div class="mv10i mh5">
				<form class="aformwrap" action="<?=WEB_ROOT?>/nojslogin" method="POST">
						<div class="aphone">
							<label class="slabel" for="login">Email</label><br>
							<input type="email" value="" id="login" name="login">
						</div>
						<div class="apwd">
							<label class="slabel" for="password"><?=$lang['Password']?></label><br> 
							<input type="password" value="" id="password" name="password">					</div>
						<div class="left lpm1">
							<a target="_blank" href="<?=WEB_ROOT?>/remind?action=getpwd" class="smbr"><?=$lang['Password_recovery']?></a>
							<a href="<?=WEB_ROOT?>/jsoff_register" class="smbr"><?=$lang['Jssoff_register']?></a>
						</div>
						<div class="right prmf">
							<input type="submit" value="<?=$lang['Sign_in_button_label']?>" class="btn" id="aop" name="aop">					
						</div>
						<?= csrf()?>
						<?=FV::hid('action', 'login')?>
						<div class="clearfix"></div>
				</form>
			</div>
		</div>
	</div>
</noindex>
