<noindex>
	<div style="position:absolute;width:100%;z-index:10;" class="hide" id="authForm">
		<div id="alayer" class="popupouter">
			<div class="aformwrap">
					<div class="aphone">
						<label class="slabel" for="login">Email</label><br>
						<input type="email" value="" id="login" name="login">
					</div>
					<div class="apwd">
						<label class="slabel" for="password"><?=$lang['Password']?></label><br> 
						<input type="password" value="" id="password" name="password">					</div>
					<div class="left lpm1">
						<a target="_blank" href="<?=WEB_ROOT?>/remind?action=getpwd" class="smbr"><?=$lang['Password_recovery']?></a>
					</div>
					<div class="right prmf">
						<input type="button" value="<?=$lang['Sign_in_button_label']?>" class="btn" id="aop" name="aop">					
					</div>
			</div>
		</div>
	</div>
</noindex>
