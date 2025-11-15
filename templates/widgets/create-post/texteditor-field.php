<script type="text/html" id="create-post-texteditor-field">
	<div v-if="field.props.editorType === 'plain-text'" class="ts-form-group">
		<label>
			{{ field.label }}
			<template v-if="field.validation.errors.length >= 1">
				<span class="is-required">{{ field.validation.errors[0] }}</span>
			</template>
			<template v-else>
				<span v-if="!field.required && content_length === 0" class="is-required"><?= _x( 'Optional', 'create post', 'voxel' ) ?></span>
				<span v-if="field.props.maxlength && content_length > 0" class="is-required ts-char-counter">{{ content_length }}/{{ field.props.maxlength }}</span>
			</template>
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>
		<textarea
			ref="composer"
			:value="field.value"
			@input="field.value = $event.target.value; resizeComposer();"
			:placeholder="field.props.placeholder"
			class="ts-filter"
		></textarea>
		<textarea ref="_composer" class="ts-filter" disabled style="height:5px;position:fixed;top:-9999px;left:-9999px;visibility:hidden !important;"></textarea>
	</div>
	<div v-else class="ts-form-group">
		<label>
			{{ field.label }}
			<template v-if="field.validation.errors.length >= 1">
				<span class="is-required">{{ field.validation.errors[0] }}</span>
			</template>
			<template v-else>
				<span v-if="!field.required && content_length === 0" class="is-required"><?= _x( 'Optional', 'create post', 'voxel' ) ?></span>
				<span v-if="field.props.maxlength && content_length > 0" class="is-required ts-char-counter">{{ content_length }}/{{ field.props.maxlength }}</span>
			</template>
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>
		<div ref="editor" class="editor-container mce-content-body" :id="editor.id"></div>
	</div>
</script>
