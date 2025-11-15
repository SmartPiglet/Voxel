<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Emails</h3>
	</div>
	<div class="x-row">
		<div class="ts-form-group x-col-12">
			<label>Sender name</label>
			<input type="text" v-model="config.emails.from_name" placeholder="WordPress">
		</div>
		<div class="ts-form-group x-col-12">
			<label>Sender email</label>
			<input type="email" v-model="config.emails.from_email" placeholder="<?= esc_attr( \Voxel\get_default_from_email() ) ?>">
		</div>
		<div class="ts-form-group x-col-12">
			<label>Email footer text</label>
			<textarea
				v-model="config.emails.footer_text"
				readonly
				placeholder="<?= esc_attr( \Voxel\get_default_email_footer_text() ) ?>"
				@click.prevent="editFooterText"
				style="height: 120px;"
			></textarea>
		</div>
	</div>
</div>
