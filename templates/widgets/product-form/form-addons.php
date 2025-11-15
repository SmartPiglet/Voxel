<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-addons">
	<template v-for="addon in field.props.addons">
		<component
			:is="addon.component_key"
			:addon="addon"
			:addons="this"
			:ref="'addon:'+addon.key"
		></component>
	</template>

	<!-- <teleport to="body">
		<transition name="form-popup">
			<form-popup
				v-if="$root.externalChoice.active"
				:target="$root.externalChoice.el"
				@blur="$root.externalChoice.active = false"
				@save="$root.externalChoice.active = false"
				@clear="$refs.handler.onClear(); $root.externalChoice.active = false;"
			>
				<external-choice ref="handler"></external-choice>
			</form-popup>
		</transition>
	</teleport> -->
</script>
