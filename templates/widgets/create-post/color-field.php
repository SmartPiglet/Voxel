<script type="text/html" id="create-post-color-field">
	<div class="ts-form-group">
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
		<div class="ts-cp-con">
			<input v-model="field.value" :placeholder="field.props.placeholder" type="color" class="ts-color-picker">
			<input type="text" v-model="field.value" class="color-picker-input" :placeholder="field.props.placeholder">
		</div>
	</div>
</script>
