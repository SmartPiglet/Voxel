<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-attributes">
	<div class="ts-form-group" ref="formGroup">
		<label>
			<?= _x( 'Product attributes', 'product field attributes', 'voxel' ) ?>
			<div class="vx-dialog">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p><?= _x( 'Create attributes which are used to generate variations for your product e.g Color, Size etc.', 'product field attributes', 'voxel' ) ?></p>
				</div>
			</div>
		</label>
		<draggable
			v-model="field.attributeList"
			:group="product.field.key+':attributes'"
			handle=".ts-repeater-head"
			filter=".no-drag"
			item-key="id"
			class="ts-repeater-container"
		>
			<template #item="{element: attribute, index: index}">
				<div class="ts-field-repeater" :class="{collapsed: active !== attribute}">
					<div class="ts-repeater-head" @click.prevent="active = attribute === active ? null : attribute">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('handle_icon') ) ?: \Voxel\svg( 'handle.svg' ) ?>
						<label>
							{{ getLabel(attribute) || <?= wp_json_encode( _x( 'Untitled', 'product field attributes', 'voxel' ) ) ?> }}
						</label>
						<template v-if="getChoiceCount(attribute)">
							<em>{{ getChoiceCount(attribute) }} <?= _x( 'values', 'product field attributes', 'voxel' ) ?></em>
						</template>
						<template v-else>
							<em><?= _x( 'No values', 'product field attributes', 'voxel' ) ?></em>
						</template>
						<div class="ts-repeater-controller">
							<a href="#" @click.stop.prevent="deleteAttribute(attribute)" class="ts-icon-btn ts-smaller no-drag">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
							<a href="#" class="ts-icon-btn ts-smaller no-drag" @click.prevent>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
							</a>
						</div>
					</div>
					<div v-if="active === attribute" class="form-field-grid medium">
						<attribute :product="product" :field="field" :attributes="this" :attribute="active" @save="active = null" ref="attribute"></attribute>
					</div>
				</div>
			</template>
		</draggable>
		<div v-if="field.field.props.attributes.length" class="flexify simplify-ul attribute-select">
			<template v-for="attribute in field.field.props.attributes">
				<a href="#" @click.prevent="useAttribute(attribute)" :class="{disabled: isUsed(attribute)}">{{ attribute.label }}</a>
			</template>
			<template v-if="field.field.props.custom_attributes.enabled">
				<a href="#" @click.prevent="createAttribute">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\get_svg( 'plus.svg' ) ?>
					<?= _x( 'Add custom attribute', 'product field attributes', 'voxel' ) ?>
				</a>
			</template>
		</div>
		<template v-else-if="field.field.props.custom_attributes.enabled">
			<a href="#" @click.prevent="createAttribute" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\get_svg( 'plus.svg' ) ?>
				<?= _x( 'Add custom attribute', 'product field attributes', 'voxel' ) ?>
			</a>
		</template>
	</div>
</script>
