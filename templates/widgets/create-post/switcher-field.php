<script type="text/html" id="create-post-switcher-field">
	<div class="ts-form-group switcher-label">

		<label>
			<div class="switch-slider">
				<div class="onoffswitch">
					<input v-model="field.value" :id="switcherId" type="checkbox" class="onoffswitch-checkbox">
					<label class="onoffswitch-label" :for="switcherId"></label>
				</div>
			</div>
			{{ field.label }}
			<slot name="errors"></slot>
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>

	</div>
</script>
