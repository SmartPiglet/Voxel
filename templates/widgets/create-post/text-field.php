<script type="text/html" id="create-post-text-field">
	<div class="ts-form-group vx-text-field">
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
		<div class="input-container">
			<input v-model="field.value" :placeholder="field.props.placeholder" type="text" class="ts-filter">
			<span v-if="field.props.suffix" class="input-suffix">{{ field.props.suffix }}</span>
		</div>
	</div>
</script>
