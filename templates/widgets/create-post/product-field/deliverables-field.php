<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/widgets/create-post/file-field.php' );
?>
<script type="text/html" id="product-deliverables">
	<div class="ts-form-group">
		<label><?= _x( 'Upload files', 'product field downloads', 'voxel' ) ?></label>
		<file-upload
			v-model="files"
			:allowed-file-types="field.props.allowed_file_types.join(',')"
			:max-file-count="field.props.max_count"
		></file-upload>
	</div>
</script>
