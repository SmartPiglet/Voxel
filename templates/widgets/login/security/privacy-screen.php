<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>


<div class="login-section">
	<div class="ts-login-head">
		<span class="vx-step-title"><?= _x( 'Privacy', 'auth', 'voxel' ) ?></span>
	</div>
	<div class="ts-form-group">
		<label><?= _x( 'Download personal data', 'auth', 'voxel' ) ?>
			<div class="vx-dialog">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p><?= _x( 'Request a copy of all your personal data stored on this site.', 'auth', 'voxel' ) ?></p>
				</div>
			</div>

		</label>
		<a
			href="<?= esc_url( home_url('/') ) ?>"
			:class="{'vx-pending': privacy.export_data.pending}"
			@click.prevent="requestPersonalData"
			class="ts-btn ts-btn-1 ts-btn-large"
		>
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
			<?= _x( 'Request download', 'auth', 'voxel' ) ?>
		</a>
	</div>
	<div class="ts-form-group">
		<label><?= _x( 'Delete account', 'auth', 'voxel' ) ?>
			<div class="vx-dialog">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p><?= _x( 'Delete your account and all associated data.', 'auth', 'voxel' ) ?></p>
				</div>
			</div>
		</label>
		
		<a href="<?= esc_url( home_url('/') ) ?>" @click.prevent="screen = 'security_delete_account'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_trash') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
			<?= _x( 'Delete my account', 'auth', 'voxel' ) ?>
		</a>
	</div>

</div>
<br>
<div class="login-section">
	<div class="ts-form-group">
		<a href="#" @click.prevent="screen = 'security'" class="ts-btn ts-btn-1 ts-btn-large">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			<?= __( 'Go back', 'voxel' ) ?>
		</a>
	</div>
</div>

