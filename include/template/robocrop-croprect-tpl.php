<?php

if ( ! defined('ABSPATH') ) 
	die();

?>
	<script type="text/html" id="tmpl-croprect">
		<div class="croprect" style="padding-bottom:{{ (1 / data.ratio.ratio) * 100 }}%;">
			<div class="label">{{ data.label }}</div>
			<div class="label">{{ data.label }}</div>
		</div>
	</script>
