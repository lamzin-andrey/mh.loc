<div id="addResourceFormWrapper" class="hide">
	<form id="addResourceForm" class="upopup" enctype="multipart/form-data" method="POST">
		<fieldset>
			<p>
				<?=$lang['Load_image_or_sound']?>
			</p>
		</fieldset>
		<div class="uploadFormInputs">
			<p class="text-right">
				<label><?=$lang['Select_file']?></label>
				<span class="red">*</span><input type="file" id="resFile" name="resFile"/>
			</p>
			<p >
				<label for="resDisplayName"><?=$lang['Sens_name']?></label>
				<span class="red"></span><input type="text" value="" id="resDisplayName" name="resDisplayName"/>
			</p>
			<p class="text-right">
				<input type="submit" id="resFileSubmit" name="resFileSubmit"/>
				<?=FV::hid('res_edit_id')?>
			</p>
		</div>
	</form>
</div>
