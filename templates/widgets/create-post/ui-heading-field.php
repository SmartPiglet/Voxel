<script type="text/html" id="create-post-ui-heading-field">
	<div class="ts-form-group ui-heading-field">
		<label>
			{{ field.label }}
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>
	</div>
</script>
