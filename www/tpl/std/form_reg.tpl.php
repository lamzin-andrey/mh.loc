<noindex>
<div id="regFormWrapper" class="hide">
	<div id="regForm" class="popupouter">
		<div class="aformwrap">
				<div class="aphone">
					<label class="slabel" for="uname"><?=$lang['Uname']?></label><br>
					<input type="text" value="" id="uname" name="uname">
				</div>
				<div class="aphone">
					<label class="slabel" for="usname"><?=$lang['Usname']?></label><br>
					<input type="text" value="" id="usname" name="usname">
				</div>
				<div class="aphone">
					<label class="slabel" for="rlogin">Email</label><br>
					<input type="email" value="" id="rlogin" name="rlogin">
				</div>
				<div class="apwd">
					<label class="slabel" for="rpassword"><?=$lang['Password']?></label><span id="password_validate" class="password_no_equ hide"><?=$lang['easy_password']?></span><br>
					<input type="password" value="" id="rpassword" name="password">					
				</div>
				<div class="apwd">
					<label class="slabel" for="password_confirm"><?=$lang['Password_confirmation']?></label><span id="password_equ" class="password_no_equ hide"><?=$lang['password_not_match']?></span><br>
					<input type="password" value="" id="password_confirm" name="password_confirm">
				</div>

				<? if(isset($app->comm_captcha)): ?>
					<div class="tcenter regcap">
						<label class="slabel" for="str"><?=$lang['Captcha_reg_legend']?></label><br>
						<img id="refimg" src="<?=WEB_ROOT?>/img/random">
					</div>
					<div class="aphone">
						<input type="text" value="" id="regfstr" name="regfstr">
					</div>
				<? endif ?>
				
				<div class="right prmf">
					<input type="button" value="<?=$lang['SignUp']?>" class="btn" id="breg" name="breg">
				</div>
				<div class="clearfix"></div>
		</div>
	</div>
</div>
</noindex>
