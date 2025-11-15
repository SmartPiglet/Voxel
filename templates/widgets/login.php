<?php
/**
 * Auth widget template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div class="ts-auth hidden" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<div v-if="screen === 'login'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/login-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'recover'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/recover/recover-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'recover_confirm'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/recover/confirm-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'recover_set_password'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/recover/set-password-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'register'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/register-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'confirm_account'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/confirm-registration-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'security'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/security/security-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'security_update_password'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/security/update-password-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'security_update_email'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/security/update-email-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'security_privacy'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/security/privacy-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'security_delete_account'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/security/delete-account-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'security_delete_account_confirm'" class="ts-form ts-login">
		<?php require locate_template( 'templates/widgets/login/security/delete-account-confirm-screen.php' ) ?>
	</div>
	<div v-else-if="screen === 'welcome'" class="ts-form ts-login ts-welcome">
		<?php require locate_template( 'templates/widgets/login/welcome-screen.php' ) ?>
	</div>
</div>

<?php require_once locate_template( 'templates/widgets/login/register/date-field.php' ) ?>
<?php require_once locate_template( 'templates/widgets/login/register/taxonomy-field.php' ) ?>
<?php require_once locate_template( 'templates/widgets/login/register/file-field.php' ) ?>
<?php require_once locate_template( 'templates/widgets/login/register/select-field.php' ) ?>
<?php require_once locate_template( 'templates/widgets/login/register/multiselect-field.php' ) ?>
