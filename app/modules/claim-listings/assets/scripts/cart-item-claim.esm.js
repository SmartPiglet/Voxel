export default {
	template: `<!--minify-->
		<div v-if="data.proof_of_ownership.status === 'optional'" class="tos-checkbox ts-form-group vx-1-1 switcher-label">
			<label @click.prevent="data.proof_of_ownership.enabled = !data.proof_of_ownership.enabled">
				<div class="ts-checkbox-container">
					<label class="container-checkbox">
						<input :checked="data.proof_of_ownership.enabled" type="checkbox" tabindex="0" class="hidden">
						<span class="checkmark"></span>
					</label>
				</div>
				{{ data.l10n.switcher_label }}
			</label>
		</div>
		<div v-if="data.proof_of_ownership.status === 'required' || data.proof_of_ownership.enabled" class="ts-form-group vx-1-1">
			<label>
				{{ data.l10n.file_field_label }}
				<div class="vx-dialog">
					<icon-info/>
					<div class="vx-dialog-content min-scroll">
						<p>{{ data.l10n.file_field_tooltip }}</p>
					</div>
				</div>
			</label>
			<file-upload
				v-model="data.proof_of_ownership.files"
				:sortable="false"
				:allowed-file-types="data.allowed_file_types.join(',')"
				:max-file-count="data.max_count"
			></file-upload>
		</div>
	`,
	props: {
		cartItem: Object,
		parent: Object,
		data: Object,
	},

	created() {
		this.$root.eventBus.addEventListener( 'before-submit', e => {
			const { formData } = e.detail;

			if ( this.data.proof_of_ownership.status === 'required' || this.data.proof_of_ownership.enabled ) {
				let files = this.data.proof_of_ownership.files;
				let fileData = [];
				let formKey = `files[proof_of_ownership][]`;
				files.forEach( file => {
					if ( file.source === 'new_upload' ) {
						formData.append( formKey, file.item );
						fileData.push( 'uploaded_file' );
					} else if ( file.source === 'existing' ) {
						fileData.push( file.id );
					}
				} );

				formData.append( 'proof_of_ownership', JSON.stringify( fileData ) );
			}
		} );
	},
};
