<script type="text/html" id="create-post-email-field">
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
		<div class="ts-input-icon flexify">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_email_icon') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
			<input
				v-model="field.value"
				:placeholder="field.props.placeholder"
				type="email"
				class="ts-filter"
			>
		</div>
	</div>
</script>
