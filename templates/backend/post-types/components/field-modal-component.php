<?php
/**
 * Field modal component.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-field-modal-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="save"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Field options</h2>
						<a href="#" @click.prevent="save" class="ts-button btn-shadow ts-save-settings"><i class="las la-check icon-sm"></i>Save</a>
					</div>
					<field-props ref="fieldProps" :field="field"></field-props>
				</div>
			</div>
		</div>
	</teleport>
</script>
