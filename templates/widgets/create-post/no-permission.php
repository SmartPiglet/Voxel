<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$content = apply_filters( 'voxel/create_post/no_permission_screen/content', [
	'title' => _x( 'Your account doesn’t support this feature yet.', 'create post', 'voxel' ),
	'icon' => \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\get_svg( 'info.svg' ),
	'actions' => [],
], $post_type, $user );

?>
<div class="ts-form ts-create-post no-vue ts-ready create-post-form">
	<div class="ts-edit-success flexify">
		<?= $content['icon'] ?? '' ?>
		<h4><?= $content['title'] ?></h4>
		<?php if ( ! empty( $content['actions'] ) ): ?>
			<div class="es-buttons flexify">
				<?php foreach ( $content['actions'] as $action ): ?>
					<a href="<?= esc_url( $action['link'] ?? '' ) ?>" class="ts-btn ts-btn-1 ts-btn-large form-btn">
						<?= $action['icon'] ?? '' ?>
						<?= $action['text'] ?? '' ?>
					</a>
				<?php endforeach ?>
			</div>
		<?php endif ?>
	</div>
</div>
