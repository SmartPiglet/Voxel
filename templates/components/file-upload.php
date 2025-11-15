<script type="text/html" id="vx-file-upload">
	<div class="ts-form-group ts-file-upload inline-file-field" @dragenter="dragActive = true">
		<div class="drop-mask" v-show="dragActive && !reordering" @dragleave.prevent="dragActive = false" @drop.prevent="onDrop" @dragenter.prevent @dragover.prevent></div>
		<div class="ts-file-list">
			<div class="pick-file-input">
				<a href="#" @click.prevent="$refs.input.click()">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_upload_ico') ) ?: \Voxel\svg( 'upload.svg' ) ?>
					<?= _x( 'Upload', 'file field', 'voxel' ) ?>
				</a>
			</div>
			<template v-if="sortable">
				<draggable
					v-model="value"
					:group="id"
					handle=".ts-file"
					item-key="id"
					@start="reordering = true; dragActive = false;"
					@end="reordering = false; dragActive = false; update();"
					tag="inline-template"
				>
					<template #item="{element: file, index: index}">
						<div class="ts-file" :style="getStyle(file)" :class="{'ts-file-img': file.type.startsWith('image/')}">
							<div class="ts-file-info">
								<?= \Voxel\get_svg( 'cloud-upload' ) ?>
								<code>{{ file.name }}</code>
							</div>
							<a href="#" @click.prevent="value.splice(index,1)" class="ts-remove-file flexify">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
						</div>
					</template>
				</draggable>
			</template>
			<template v-else>
				<template v-for="file, index in value">
					<div class="ts-file" :style="getStyle(file)" :class="{'ts-file-img': file.type.startsWith('image/')}">
						<div class="ts-file-info">
							<?= \Voxel\get_svg( 'cloud-upload' ) ?>
							<code>{{ file.name }}</code>
						</div>
						<a href="#" @click.prevent="value.splice(index,1)" class="ts-remove-file flexify">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
						</a>
					</div>
				</template>
			</template>
		</div>

		<media-popup
			@save="onMediaPopupSave"
			:multiple="maxFileCount > 1"
		></media-popup>

		<input ref="input" type="file" class="hidden" :multiple="maxFileCount > 1" :accept="allowedFileTypes">
	</div>
</script>
