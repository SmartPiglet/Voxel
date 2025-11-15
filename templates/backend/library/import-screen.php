<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="x-row h-center">
	<div class="x-col-7 ts-content-head">
		<h1>Import package</h1>
		<p>Upload and install package</p>
	</div>

	<div class="x-col-7">
		<input class="ts-pick-file area-file" type="file" ref="manualImport">
	</div>
	<div class="x-col-7"></div>
	<div class="x-col-7">
		<a href="#" @click.prevent="manualImport" class="ts-button ts-save-settings full-width" :class="{'vx-disabled':state.processing_upload}">
			<i class="las la-cloud-upload-alt icon-sm"></i> Import
		</a>
	</div>
</div>
