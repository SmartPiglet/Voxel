<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-switcher">
	<div class="ts-form-group switcher-label">
		<label>
			<div class="switch-slider">
				<div class="onoffswitch">
					<input v-model="value.enabled" type="checkbox" class="onoffswitch-checkbox">
					<label class="onoffswitch-label" @click.prevent="value.enabled = !value.enabled"></label>
				</div>
			</div>{{ addon.label }}
		</label>
	</div>
</script>
