<!doctype html>
<html lang="en">

<head>
	<?php wp_head(); ?>
	<script>
		var wptba_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
		var wptba_nonce = "<?php echo wp_create_nonce('wptba_nonce'); ?>";
	</script>
</head>

<body>
	<div class="wptodobyaavoya">
	</div>
	<?php wp_footer(); ?>
</body>

</html>