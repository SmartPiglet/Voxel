<script type="text/html" id="create-post-file-field">
	<div class="ts-form-group ts-file-upload inline-file-field" @dragenter="dragActive = true">
		<div class="drop-mask" v-show="dragActive && !reordering" @dragleave.prevent="dragActive = false" @drop.prevent="onDrop" @dragenter.prevent @dragover.prevent></div>
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

		<div class="ts-file-list">
			<div class="pick-file-input">
				<a href="#" @click.prevent="$refs.input.click()">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?>
					<?= _x( 'Upload', 'file field', 'voxel' ) ?>
				</a>
			</div>
			<template v-if="sortable && field.props.sortable">
				<draggable
					v-model="field.value"
					:group="'files:'+field.id"
					handle=".ts-file"
					filter=".no-drag"
					item-key="id"
					@start="reordering = true; dragActive = false;"
					@end="reordering = false; dragActive = false;"
					tag="inline-template"
				>
					<template #item="{element: file, index: index}">
						<div class="ts-file" :style="getStyle(file)" :class="{'ts-file-img': previewImages && file.type.startsWith('image/')}">
							<div class="ts-file-info">
								<?= \Voxel\get_svg( 'cloud-upload' ) ?>
								<code>{{ file.name }}</code>
							</div>
							<a href="#" @click.prevent="field.value.splice(index,1)" class="ts-remove-file flexify no-drag">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
						</div>
					</template>
				</draggable>
			</template>
			<template v-else>
				<template v-for="file, index in field.value">
					<div class="ts-file" :style="getStyle(file)" :class="{'ts-file-img': previewImages && file.type.startsWith('image/')}">
						<div class="ts-file-info">
							<?= \Voxel\get_svg( 'cloud-upload' ) ?>
							<code>{{ file.name }}</code>
						</div>
						<a href="#" @click.prevent="field.value.splice(index,1)" class="ts-remove-file flexify">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
						</a>
					</div>
				</template>
			</template>
		</div>

		<media-popup
			v-if="showLibrary"
			@save="onMediaPopupSave"
			:multiple="field.props.maxCount > 1"
			:custom-target="mediaTarget"
		></media-popup>

		<input ref="input" type="file" class="hidden" :multiple="field.props.maxCount > 1" :accept="accepts">
	</div>
</script>
