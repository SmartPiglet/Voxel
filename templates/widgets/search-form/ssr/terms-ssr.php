<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$value = $this->_get_selected_terms();
$config = $this->get_frontend_config();

$flat_terms = [];
$is_adaptive = $this->is_adaptive() && ( $args['config']['general_config']['supports_adaptive_filters'] ?? false );
$taxonomy_key = $config['props']['taxonomy']['key'] ?? null;
$narrowed_values = (array) ( $args['config']['post_type_config'][ $this->post_type->get_key() ]['narrowed_values']['terms'] ?? [] );
$post_counts = $narrowed_values[ $taxonomy_key ] ?? [];
$should_show_popup = false;

$use_term = function( &$term, &$parent = null ) use ( &$use_term, &$flat_terms, &$post_counts, &$value, &$should_show_popup, $is_adaptive ) {
	$term['depth'] = $parent ? ( $parent['depth'] + 1 ) : 0;

	if ( $is_adaptive ) {
		if ( is_numeric( $post_counts[ $term['id'] ] ?? null ) ) {
			$term['post_count'] = (int) $post_counts[ $term['id'] ];

			if ( $parent === null ) {
				$should_show_popup = true;
			}
		} else {
			$term['post_count'] = 0;
		}
	} else {
		$term['post_count'] = null;
		$should_show_popup = true;
	}

	if ( $is_adaptive ) {
		if ( $term['post_count'] > 0 || isset( $value[ $term['slug'] ] ) ) {
			$flat_terms[] = $term;
		}
	} else {
		$flat_terms[] = $term;
	}

	if ( ! empty( $term['children'] ) ) {
		foreach ( $term['children'] as &$child_term ) {
			$use_term( $child_term, $term );
		}
	}
};

foreach ( $config['props']['terms'] as &$term ) {
	$use_term( $term );
}
?>

<?php if ( ( $this->elementor_config['display_as'] ?? 'popup' ) === 'inline' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> inline-terms-wrapper ts-inline-filter min-scroll <?= count( $flat_terms ) ? '' : 'hidden' ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-term-dropdown ts-multilevel-dropdown inline-multilevel">
			<ul class="simplify-ul ts-term-dropdown-list">
				<?php foreach ( array_slice( $flat_terms, 0, $config['props']['per_page'] ) as $term ): ?>
					<?php $is_selected = is_array( $value ) && isset( $value[ $term['slug'] ] ); ?>
					<li class="<?= $is_selected ? 'ts-selected' : '' ?>">
						<a href="#" class="flexify">
							<div class="ts-checkbox-container">
								<label class="container-<?= $config['props']['multiple'] ? 'checkbox' : 'radio' ?>">
									<input type="<?= $config['props']['multiple'] ? 'checkbox' : 'radio' ?>" <?= $is_selected ? 'checked="checked"' : '' ?>>
									<span class="checkmark"></span>
								</label>
							</div>
							<span><?= str_repeat( '— ', $term['depth'] ) . esc_attr( $term['label'] ) ?></span>
							<?php if ( $term['post_count'] !== null ): ?>
								<div class="ts-term-count">
									<?= $term['post_count'] ?>
								</div>
							<?php endif ?>
							<?php if ( ! $is_adaptive && ! empty( $term['icon'] ) ): ?>
								<div class="ts-term-icon">
									<span><?= $term['icon'] ?></span>
								</div>
							<?php endif ?>
						</a>
					</li>
				<?php endforeach ?>
				<?php if ( count( $flat_terms ) > $config['props']['per_page'] ): ?>
					<li class="ts-term-centered">
						<a href="#" class="flexify">
							<div class="ts-term-icon">
								<?= \Voxel\get_icon_markup( $args['widget']->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							</div>
							<span><?= __( 'Load more', 'voxel' ) ?></span>
						</a>
					</li>
				<?php endif ?>
			</ul>
		</div>
	</div>
<?php elseif ( ( $this->elementor_config['display_as'] ?? 'popup' ) === 'buttons' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> inline-terms-wrapper ts-inline-filter <?= count( $flat_terms ) ? '' : 'hidden' ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<ul class="simplify-ul addon-buttons flexify">
			<?php foreach ( $flat_terms as $term ):
				$is_selected = is_array( $value ) && isset( $value[ $term['slug'] ] ); ?>
				<li class="flexify <?= $is_selected ? 'adb-selected' : '' ?>">
					<?= esc_attr( $term['label'] ) ?>
					<?php if ( $term['post_count'] !== null ): ?>
						<div class="ts-term-count">
							<?= $term['post_count'] ?>
						</div>
					<?php endif ?>
				</li>
			<?php endforeach ?>
		</ul>
	</div>
<?php else: ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> <?= ( $should_show_popup || ! empty( $value ) ) ? '' : 'hidden' ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<div class="ts-filter-text">
				<?= $value ? array_values( $value )[0]['label'] : ( $this->props['placeholder'] ?: $this->props['label'] ) ?>
				<?php if ( $value && count( $value ) > 1 ): ?>
					<span class="term-count">+<?= number_format_i18n( count( $value ) - 1 ) ?></span>
				<?php endif ?>
			</div>
			<div class="ts-down-icon"></div>
		</div>
	</div>
<?php endif ?>
