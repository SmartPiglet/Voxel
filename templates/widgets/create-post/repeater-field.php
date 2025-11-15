<script type="text/html" id="create-post-repeater-field">
	<div class="ts-form-group ts-repeater" ref="container">
		<label>
			{{ field.label }}
			<slot name="errors"></slot>
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>
		<draggable v-model="rows" :group="'rows:'+field.id" handle=".ts-repeater-head" item-key="id" class="ts-repeater-container" filter=".no-drag">
			<template #item="{element: row, index: index}">
				<div class="ts-field-repeater" :class="{collapsed: active !== row}">
					<div class="ts-repeater-head" @click.prevent="active = row === active ? null : row">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('handle_icon') ) ?: \Voxel\svg( 'handle.svg' ) ?>
						<label>
							{{ row['meta:state'].label }}
							<span class="ts-row-error" style="display: none;"></span>
						</label>
						<div class="ts-repeater-controller">
							<a href="#" @click.stop.prevent="rows.splice(index,1)" class="ts-icon-btn ts-smaller no-drag">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
							<a href="#" class="ts-icon-btn ts-smaller no-drag" @click.prevent>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
							</a>
						</div>
					</div>
					<div class="medium form-field-grid">
						<template v-for="subfield in row">
							<template v-if="subfield.key !== 'meta:state'">
								<component
									:field="subfield"
									:is="'field-'+subfield.type"
									:ref="'row#'+row['meta:state'].id+':'+subfield.key"
									:index="row['meta:state'].id"
									:key="row['meta:state'].id"
									v-if="$root.conditionsPass(subfield)"
									:class="[subfield.validation.errors.length >= 1 ? 'ts-has-errors' : '', subfield.css_class, subfield.hidden ? 'hidden' : '']"
								>
									<template #errors>
										<template v-if="subfield.validation.errors.length >= 1">
											<span class="is-required">{{ subfield.validation.errors[0] }}</span>
										</template>
										<template v-else>
											<span v-if="!subfield.required" class="is-required"><?= _x( 'Optional', 'create post', 'voxel' ) ?></span>
										</template>
									</template>
								</component>

								<?php if ( \Voxel\is_dev_mode() ): ?>
									<!-- <p style="text-align: right;" v-if="$root.conditionsPass(subfield)">
										<a href="#" @click.prevent="validate_subfield(subfield.key, row['meta:state'].id)">Check validity</a>
									</p> -->
								<?php endif ?>
							</template>
						</template>
					</div>
				</div>
			</template>
		</draggable>

		<a
			v-if="!field.props.max_rows || rows.length < field.props.max_rows"
			href="#"
			class="ts-repeater-add ts-btn ts-btn-4 form-btn"
			@click.prevent="addRow"
		>
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			{{ field.props.l10n.add_row }}
		</a>
	</div>
</script>
