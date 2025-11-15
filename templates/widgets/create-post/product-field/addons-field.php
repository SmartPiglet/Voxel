<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-addons">
	<template v-for="addon in field.props.addons">
		<component
			:is="addon.component_key"
			:addon="addon"
			:product="product"
			:field="this"
			:ref="'addon:'+addon.key"
		></component>
	</template>
</script>
