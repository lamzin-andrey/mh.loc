<script type="text/javascript">
<?
$list = get_defined_constants();
foreach($list as $name => $value){
	$print = 1;
	foreach ($STD_CONST_PREFIXES as $exclude){
		if (strpos($name, $exclude) === 0 || strpos($name, 'apc_register_serializer-') !== false) {
			$print = 0;
			continue;
		}
		foreach ($PRIVATE_CONSTANTS as $excl) {
			if ($name == $excl) {
				$print = 0;
				continue;
			}
		}
	}
	if ($print == 1) {
		?>		var <?=$name?> = '<?=$value?>';
<?
	}
}?>
	</script>
