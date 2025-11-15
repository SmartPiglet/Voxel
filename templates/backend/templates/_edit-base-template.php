<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="ts-edit-base-template">
	<teleport to="body">
		<div class="ts-field-modal ts-theme-options">
			<div class="ts-modal-backdrop" @click="template.editSettings = false"></div>
			<div class="ts-modal-content min-scroll">
				<div class="x-container">
					<div class="field-modal-head">
						<h2>Template options</h2>
						<div>
							<a class="ts-button ts-outline" href="#" @click.prevent="template.editSettings = false">Discard</a>
								&nbsp;
							<a href="#" @click.prevent="template.editSettings = false" class="ts-button btn-shadow ts-save-settings">
								<i class="las la-check icon-sm"></i>Done
							</a>
						</div>
					</div>
					<div class="ts-field-props">
						<div class="field-modal-body">
							<div class="x-row">
								<div v-if="modifyId" class="ts-form-group x-col-12" :class="{'vx-disabled': updating}">
									<label>{{ template.type === 'page' ? 'Enter new page template id' : 'Enter new template id' }}</label>
									<input type="number" v-model="newId">
									<br><br>
									<div class="x-row">
										<div class="x-col-12">
											<a href="#" @click.prevent="modifyId = false" class="ts-button ts-outline">Cancel</a>&nbsp;
											<a href="#" @click.prevent="saveId" class="ts-button ts-save-settings">Submit</a>
										</div>
									</div>

								</div>
								<div v-else class="ts-form-group x-col-12">
									<label>Template ID</label>
									<input type="number" disabled v-model="template.id">
									<br><br>
									<a href="#" @click.prevent="modifyId = true" class="ts-button ts-outline">Switch template</a>

								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</teleport>
</script>
