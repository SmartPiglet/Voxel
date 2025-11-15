<?php
/**
 * Field visibility component.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-field-visibility-template">
	<?php \Voxel\Utils\Form_Models\Select_Model::render( [
		'v-model' => 'field.visibility_behavior',
		'label' => 'Visibility',
		'classes' => 'x-col-6',
		'choices' => [
			'show' => 'Show this field if',
			'hide' => 'Hide this field if',
		],
	] ) ?>

	<div class="ts-form-group x-col-4">
		<label>&nbsp;</label>
		<a href="#" @click.prevent="editRules" class="ts-button ts-outline full-width"><i class="las la-code-branch icon-sm"></i>Edit rules</a>
	</div>
	<div class="ts-form-group x-col-12">
		<div class="vx-visibility-rules" v-html="displayRules()"></div>
	</div>

	<!-- <div class="ts-form-group ts-col-1-1">
		<pre debug>{{ field.visibility_behavior }}</pre>
		<pre debug>{{ field.visibility_rules }}</pre>
	</div> -->
</script>
