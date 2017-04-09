<div class="promo">
	<header>
		<h1>Test mapping post</h1>
	</header>
	<article>
		Here will Form
		<form method="post" action="<?=WEB_ROOT?>/testdbmapping">
			<?=FV::labinp('intf', 'Integer', '100dsadsd')?><br>
			<?=FV::labinp('floatf', 'Float', '1.14dsadasdasdsd')?><br>
			<?=FV::labinp('doublef', 'Double', '3.15dsad ad ')?><br>
			<?=FV::labinp('money_f', 'Decimal', '1.25dsad ad ')?><br>
			<?=FV::labinp('str_4', 'String(4)', 'dsadEE ad ')?><br>
			<?=FV::labinp('text_long', 'Text Long', 'd\'sadE"E" ad ')?><br>
			<?=FV::labinp('text_small', 'Text Small', 'd\'sadE"E" ad ')?><br>
			<?=FV::labinp('bf', 'Blob', 'd\'sadE"E" ad ')?><br>
			<?=FV::labinp('datetimef', 'Datetime', '2015-06-04 12:00:00')?><br>
			<?=FV::labinp('booleanf', 'Boolean', '1true')?><br>
			
			<?=FV::hid('action', 'add_test_user')?>
			<?=FV::sub('', 'Add user')?>
		</form>
	</article>
</div>
