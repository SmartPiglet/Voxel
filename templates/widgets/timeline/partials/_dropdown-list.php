<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__dd-list">
	<teleport to="body">
		<transition name="form-popup">
			<form-popup class="xs-width" :target="target" @blur="$emit('blur')">
				<div class="ts-term-dropdown ts-md-group">
					<ul class="simplify-ul ts-term-dropdown-list min-scroll">
						<slot/>
					</ul>
				</div>
				<template #controller><template/></template>
			</form-popup>
		</transition>
	</teleport>
</script>