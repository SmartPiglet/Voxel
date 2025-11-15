<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Notifications</h3>
	</div>
	<div class="x-row">
		<?php \Voxel\Utils\Form_Models\Select_Model::render( [
			'v-model' => 'config.notifications.admin_user',
			'label' => 'Admin user',
			'classes' => 'x-col-12',
			'description' => 'This user will receive all notifications that are set to "Notify admin"',
			'choices' => array_column( array_map( function( $wp_user ) {
				return [
					'id' => $wp_user->ID,
					'login' => $wp_user->user_login,
				];
			}, get_users( [ 'role' => 'administrator' ] ) ), 'login', 'id'),
		] ) ?>

		<?php \Voxel\Utils\Form_Models\Number_Model::render( [
			'v-model' => 'config.notifications.inapp_persist_days',
			'label' => 'Keep in-app notifications in the database for up to (days)',
			'classes' => 'x-col-12',
		] ) ?>
	</div>
</div>
