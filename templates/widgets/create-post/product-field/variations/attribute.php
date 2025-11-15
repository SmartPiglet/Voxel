<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-attribute">
	<div class="ts-form-group _variation-box" ref="formGroup">
		<div class="medium form-field-grid">
			<template v-if="attribute.type === 'custom'">

				<div class="ts-form-group ts-attribute-label vx-1-2">
					<label><?= _x( 'Label', 'product field attributes', 'voxel' ) ?></label>
					<div class="input-container">
						<input type="text" v-model="attribute.label" class="ts-filter">
					</div>
				</div>
				<div class="ts-form-group vx-1-2">
					<label><?= _x( 'Display mode', 'product field attributes', 'voxel' ) ?></label>
					<div class="ts-filter">
						<select v-model="attribute.display_mode">
							<option value="dropdown"><?= _x( 'Dropdown', 'product field attributes', 'voxel' ) ?></option>
							<option value="buttons"><?= _x( 'Buttons', 'product field attributes', 'voxel' ) ?></option>
							<option value="radio"><?= _x( 'Radio', 'product field attributes', 'voxel' ) ?></option>
							<option value="colors"><?= _x( 'Colors', 'product field attributes', 'voxel' ) ?></option>
							<option value="cards"><?= _x( 'Cards', 'product field attributes', 'voxel' ) ?></option>
							<option value="images"><?= _x( 'Images', 'product field attributes', 'voxel' ) ?></option>
						</select>
						<div class="ts-down-icon"></div>
					</div>
				</div>
				<div class="ts-form-group vx-1-1">
					<label><?= _x( 'Values', 'product field attributes', 'voxel' ) ?></label>
					<draggable
						v-model="attribute.choices"
						:group="product.field.key+':custom-attributes:'+attribute.id+':choices'"
						handle=".ts-repeater-head"
						filter=".no-drag"
						item-key="id"
						class="ts-repeater-container"
					>
						<template #item="{element: choice, index: index}">
							<div class="ts-field-repeater" :class="{collapsed: activeChoice !== choice}">
								<div class="ts-repeater-head" @click.prevent="activeChoice = choice === activeChoice ? null : choice">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('handle_icon') ) ?: \Voxel\svg( 'handle.svg' ) ?>
									<label>
										{{ choice.label || <?= wp_json_encode( _x( 'Untitled', 'product field attributes', 'voxel' ) ) ?> }}
									</label>
									<div class="ts-repeater-controller">
										<a href="#" @click.prevent="deleteChoice(choice)" class="ts-icon-btn ts-smaller no-drag">
											<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
										</a>
										<a href="#" class="ts-icon-btn ts-smaller no-drag" @click.prevent>
											<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
										</a>
									</div>
								</div>
								<div class="medium form-field-grid">
									<div class="ts-form-group ts-choice-label" :class="{'vx-2-3': attribute.display_mode === 'colors'}">
										<label><?= _x( 'Label', 'product field attributes', 'voxel' ) ?></label>
										<div class="input-container">
											<input type="text" v-model="choice.label" class="ts-filter" @keyup.enter="choice.label?.length && createChoice()">
										</div>
									</div>
									<div v-if="attribute.display_mode === 'colors'" class="ts-form-group vx-1-3">
										<label><?= _x( 'Color', 'product field attributes', 'voxel' ) ?></label>
										<div class="ts-cp-con">
											<input v-model="choice.color" type="color" class="ts-color-picker">
											<input type="text" placeholder="<?= _x( 'Pick color', 'product field attributes', 'voxel' ) ?>" v-model="choice.color" class="color-picker-input">
										</div>
									</div>
									<div v-if="attribute.display_mode === 'cards'" class="ts-form-group">
										<label><?= _x( 'Subheading', 'product field attributes', 'voxel' ) ?></label>
										<div class="input-container">
											<input type="text" v-model="choice.subheading" class="ts-filter">
										</div>
									</div>
									<div v-if="attribute.display_mode === 'cards' || attribute.display_mode === 'images'" class="ts-form-group">
										<label><?= _x( 'Image', 'product field attributes', 'voxel' ) ?></label>
										<file-upload :key="choice" v-model="choice.image" allowed-file-types="image/jpeg,image/png,image/webp"></file-upload>
									</div>
								</div>
							</div>
						</template>
					</draggable>

					<a href="#" @click.prevent="createChoice" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
						<?= _x( 'Add value', 'product field attributes', 'voxel' ) ?>
					</a>
				</div>
			</template>
			<template v-else>

				<div class="ts-form-group vx-1-1">
					<label><?= _x( 'Select items', 'product field attributes', 'voxel' ) ?></label>
					<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown inline-multilevel min-scroll">
						<ul class="simplify-ul ts-term-dropdown-list">
							<li v-for="choice in attributes.getPreset(attribute).choices" :class="{'ts-selected': attribute.choices[choice.value]}">
								<a href="#" class="flexify" @click.prevent="toggleChoice(choice)">
									<div class="ts-checkbox-container">
										<label class="container-checkbox">
											<input
												type="checkbox"
												:value="choice.value"
												:checked="attribute.choices[choice.value]"
												disabled
												hidden
											>
											<span class="checkmark"></span>
										</label>
									</div>
									<span>{{ choice.label }}</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</template>
		</div>
	</div>
</script>
