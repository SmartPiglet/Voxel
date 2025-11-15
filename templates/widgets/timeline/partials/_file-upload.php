<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__file-upload">
	<div v-if="value.length" class="ts-form-group ts-file-upload vxf-create-section">
		<div class="ts-file-list">
			<template v-for="file, index in value">
				<div class="ts-file" :style="getStyle(file)" :class="{'ts-file-img': file.type.startsWith('image/')}">
					<div class="ts-file-info">
						<icon-upload/>
						<code>{{ file.name }}</code>
					</div>
					<a href="#" @click.prevent="value.splice(index,1)" class="ts-remove-file flexify">
						<icon-trash/>
					</a>
				</div>
			</template>
		</div>
	</div>
	<input ref="input" type="file" class="hidden" :multiple="maxFileCount > 1" :accept="allowedFileTypes">
	<media-popup
		@save="onMediaPopupSave"
		:multiple="maxFileCount > 1"
		ref="mediaLibrary"
		:custom-target="'#'+context.uniqueId"
	><template></template></media-popup>
</script>