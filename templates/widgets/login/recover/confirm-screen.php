<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<form @submit.prevent="submitRecoverConfirm">
	<div class="ts-login-head">
		<span class="vx-step-title"><?php echo $this->get_settings_for_display( 'confirm_code' ); ?></span>
	</div>
	<div class="login-section">

		<div class="ts-form-group">
			<label><?= _x( 'Email confirmation code', 'auth', 'voxel' ) ?>
				<div class="vx-dialog">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
					<div class="vx-dialog-content min-scroll">
						<p><?= _x( 'Please type the recovery code which was sent to your email', 'auth', 'voxel' ) ?></p>
					</div>
				</div>
			</label>
			<div class="ts-input-icon flexify">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
				<input class="ts-filter autofocus" type="text" v-model="recovery.code" placeholder="<?= esc_attr( _x( 'Confirmation code', 'auth', 'voxel' ) ) ?>">
			</div>
		</div>

		<div class="ts-form-group">
			<button type="submit" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-pending': pending}">
				<?= _x( 'Submit', 'auth', 'voxel' ) ?>
			</button>
		</div>

		<div class="ts-form-group">
			<p class="field-info">
				<?= _x( 'Didn\'t receive anything?', 'auth', 'voxel' ) ?>
				<a href="#" @click.prevent="recovery.code = null; screen = 'recover';"><?= _x( 'Send again', 'auth', 'voxel' ) ?></a>
			</p>
		</div>
	</div>
</form>
