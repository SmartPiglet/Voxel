<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-inputs">
	<template v-for="dataInput in field.props.data_inputs">
		<component
			:is="dataInput.component_key"
			:data-input="dataInput"
			:data-inputs="this"
		></component>
	</template>
</script>