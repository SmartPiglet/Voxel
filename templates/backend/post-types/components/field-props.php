<?php
/**
 * Repeater fields - component template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="post-type-field-props-template">
	<div class="ts-field-props">
		<div class="field-modal-tabs">
			<ul class="inner-tabs">
				<li :class="{'current-item': tab === 'general'}">
					<a href="#" @click.prevent="tab = 'general'">General</a>
				</li>
				<li v-if="field.type === 'repeater'" :class="{'current-item': tab === 'fields'}">
					<a href="#" @click.prevent="tab = 'fields'">Repeater fields</a>
				</li>
				<li :class="{'current-item': tab === 'conditions'}">
					<a href="#" @click.prevent="tab = 'conditions'">Conditional logic</a>
				</li>
				<li :class="{'current-item': tab === 'visibility'}">
					<a href="#" @click.prevent="tab = 'visibility'">Visibility rules</a>
				</li>
				<li v-if="$root.options.has_overridable_models[field.type]" :class="{'current-item': tab === 'overrides'}">
					<a href="#" @click.prevent="tab = 'overrides'">Overrides</a>
				</li>
			</ul>
		</div>

		<div class="field-modal-body">
			<div v-if="tab === 'general'" class="x-row">
				<?= $field_options_markup ?>
			</div>
			<div v-else-if="tab === 'conditions'" class="x-row">
				<field-conditions :field="field" :repeater="repeater"></field-conditions>
			</div>
			<div v-else-if="tab === 'visibility'" class="x-row">
				<field-visibility :field="field" :repeater="repeater"></field-visibility>
			</div>
			<div v-else-if="tab === 'overrides'" class="x-row">
				<field-model-overrides :field="field" :repeater="repeater"></field-model-overrides>
			</div>
			<div v-else-if="field.type === 'repeater' && tab === 'fields'" class="x-row">
				<repeater-fields :field="field"></repeater-fields>
			</div>
		</div>
	</div>
</script>
