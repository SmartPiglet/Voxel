<?php
/**
 * Search form widget template.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
}

$deferred_templates = [];
$required_scripts = [];
?>

<?= sprintf( '<script type="text/json" class="vxconfig">%s</script>', wp_specialchars_decode( wp_json_encode( [
	'general' => $general_config,
	'postTypes' => $post_type_config,
] ) ) ) ?>
<div class="ts-form ts-search-widget">
	<div class="ts-filter-wrapper flexify
		<?= $this->get_settings_for_display('form_toggle_desktop') === 'yes' ? '' : 'vx-hidden-desktop' ?>
		<?= $this->get_settings_for_display('form_toggle_tablet') === 'yes' ? '' : 'vx-hidden-tablet' ?>
		<?= $this->get_settings_for_display('form_toggle_mobile') === 'yes' ? '' : 'vx-hidden-mobile' ?>
	">
		<a href="#" @click.prevent @mousedown="portal.active = !portal.active" :class="{'ts-filled': activeFilterCount}" class="ts-filter-toggle ts-btn ts-btn-1">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_toggle_icon') ) ?: \Voxel\svg( 'filterslider.svg' ) ?>
			<div class="ts-toggle-text"><?= _x( 'Filter results', 'search form', 'voxel' ) ?></div>
			<span class="ts-filter-count" v-if="activeFilterCount" v-cloak="hide">{{ activeFilterCount }}</span>
		</a>
	</div>

	<form method="GET" ref="form" @submit.prevent onsubmit="return false;" v-cloak>
		<teleport to="body">
			<div class="ts-search-portal vx-popup elementor ts-search-portal-<?= esc_attr( $this->get_id() ) ?>" :class="['elementor-'+post_id, !(portal.active && portal.enabled[breakpoint])?'hidden':'']" data-elementor-type="empty" data-elementor-id="empty">
				<div class="ts-popup-root elementor-element elementor-element-<?= esc_attr( $this->get_id() ) ?>-wrap">
					<div class="ts-form ts-search-widget no-render elementor-element elementor-element-<?= esc_attr( $this->get_id() ) ?>">
						<div class="ts-field-popup-container">
							<div class="ts-field-popup triggers-blur">
								<div class="ts-popup-content-wrapper min-scroll">
									<div class="ts-form-group">
										<div class="elementor-element elementor-element-<?= esc_attr( $this->get_id() ) ?>">
											<div class="ts-form ts-search-widget no-render toggle-sf"></div>
										</div>
									</div>
								</div>
								<div class="ts-popup-controller">
									<ul class="flexify simplify-ul">
										<li class="flexify ts-popup-close">
											<a @click.prevent="portal.active = false;" href="#" class="ts-icon-btn" role="button">
												<?= \Voxel\svg( 'close.svg' ) ?>
											</a>
										</li>
										<li class="flexify hide-d" @click.prevent="$emit('clear')">
											<a href="#" @click.prevent="clearAll(true)" class="ts-icon-btn">
												<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_reset_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
											</a>
										</li>
										<li class="flexify hide-m">
											<a @click.prevent="clearAll(true)" href="#" class="ts-btn ts-btn-1"><?= _x( 'Clear', 'search form', 'voxel' ) ?></a>
										</li>
										<li class="flexify">
											<a href="#" @click.prevent="submit(); portal.active = false;" class="ts-btn ts-btn-2"><?= _x( 'Search', 'search form', 'voxel' ) ?></a>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="ts-switcher-btn-<?= esc_attr( $this->get_id() ) ?> elementor
				<?= ! $switchable_desktop ? 'vx-hidden-desktop' : '' ?>
				<?= ! $switchable_tablet ? 'vx-hidden-tablet' : '' ?>
				<?= ! $switchable_mobile ? 'vx-hidden-mobile' : '' ?>
			" :class="'elementor-'+post_id" data-elementor-type="empty" data-elementor-id="empty">
				<div class="elementor-element elementor-element-<?= esc_attr( $this->get_id() ) ?>"></div>
			</div>
		</teleport>

		<teleport to=".ts-search-portal .elementor-element-<?= esc_attr( $this->get_id() ) ?> .ts-search-widget" :disabled="!portal.enabled[breakpoint]">
			<div class="ts-filter-wrapper flexify">
				<?php if ( $this->get_settings_for_display('cpt_filter_show') === 'yes' ): ?>
					<filter-post-types></filter-post-types>
					<?php $deferred_templates[] = locate_template( 'templates/widgets/search-form/post-types-filter.php' ) ?>
				<?php endif ?>

				<?php
				foreach ( $post_types as $post_type ):
					$filter_list = (array) $this->get_settings_for_display(
						sprintf( 'ts_filter_list__%s', $post_type->get_key() )
					); ?>

					<template v-if="post_type.key === <?= esc_attr( wp_json_encode( $post_type->get_key() ) ) ?>">
						<?php
						foreach ( $filter_list as $filter_config ):
							$filter = $post_type->get_filter( $filter_config['ts_choose_filter'] ?? '' );
							if ( ! $filter ) {
								continue;
							}

							if ( $filter_template = locate_template(
								sprintf( 'templates/widgets/search-form/%s-filter.php', $filter->get_type() )
							) ) {
								$deferred_templates[] = $filter_template;
							}

							foreach ( $filter->get_required_scripts() as $script_handle ) {
								$required_scripts[ $script_handle ] = true;
							}

							$filter_object = sprintf(
								'$root.post_types[%s].filters[%s]',
								esc_attr( wp_json_encode( $post_type->get_key() ) ),
								esc_attr( wp_json_encode( $filter->get_key() ) )
							);
							?>
							<filter-<?= $filter->get_type() ?>
								class="elementor-repeater-item-<?= $filter_config['_id'] ?>"
								:class="{hidden: !conditionsPass(<?= $filter_object ?>)}"
								repeater-id="elementor-repeater-item-<?= $filter_config['_id'] ?>"
								:filter="<?= $filter_object ?>"
								ref="<?= esc_attr( $post_type->get_key().':'.$filter->get_key() ) ?>"
							></filter-<?= $filter->get_type() ?>>
							<?php
						endforeach; ?>
					</template>
				<?php endforeach ?>
				<?php if ( $this->get_settings_for_display('ts_show_search_btn') === 'true' ): ?>		
					<div v-if="!(portal.active && portal.enabled[breakpoint])" class="ts-form-group flexify ts-form-submit" id="sf_submit" :class="{'': loading}">
							<button ref="submitButton" type="button" @click.prevent="submit" class="ts-btn ts-btn-2 ts-btn-large ts-search-btn" :class="{'ts-loading-btn': loading && !resetting}">
								<div v-if="loading && !resetting" class="ts-loader-wrapper">
									<span class="ts-loader"></span>
								</div>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
								<?= $this->get_settings_for_display('ts_search_text') ?>
							</button>
					</div>
				<?php endif ?>
				<?php if ( $this->get_settings_for_display('ts_show_reset_btn') === 'true' ): ?>
					<div v-if="!(portal.active && portal.enabled[breakpoint])" class="ts-form-group flexify ts-form-reset"  :class="{'': loading}">
						
							<a @click.prevent="clearAll" ref="resetBtn" href="#" class="ts-filter ">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_reset_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
								
								<?php if ( $this->get_settings_for_display('ts_reset_text') ) : ?>
									<div class="ts-filter-text">
										<?= $this->get_settings_for_display('ts_reset_text') ?>
									</div>
								<?php endif; ?>
							</a>
					</div>
				<?php endif ?>
			</div>
		</teleport>
	</form>

	<form v-if="false" method="GET" ref="form" onsubmit="return false;" class="
		<?= $this->get_settings_for_display('form_toggle_desktop') === 'yes' ? 'vx-hidden-desktop' : '' ?>
		<?= $this->get_settings_for_display('form_toggle_tablet') === 'yes' ? 'vx-hidden-tablet' : '' ?>
		<?= $this->get_settings_for_display('form_toggle_mobile') === 'yes' ? 'vx-hidden-mobile' : '' ?>
	">
		<div class="ts-filter-wrapper flexify">
			<?php if ( ! empty( $post_types ) ): ?>
				<?php $this->_ssr_filters( [
					'general_config' => &$general_config,
					'post_type_config' => &$post_type_config,
				] ) ?>
			<?php endif ?>
			<?php if ( $this->get_settings_for_display('ts_show_search_btn') === 'true' ): ?>
				<div class="ts-form-group flexify ts-form-submit" id="sf_submit">
					
						<button type="button" class="ts-btn ts-btn-2 ts-btn-large ts-search-btn">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
							<?= $this->get_settings_for_display('ts_search_text') ?>
						</button>
				</div>
			<?php endif ?>
			<?php if ( $this->get_settings_for_display('ts_show_reset_btn') === 'true' ): ?>
				<div class="ts-form-group flexify ts-form-reset">
						<a href="#" class="ts-filter ">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_sf_form_btn_reset_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
							<?php if ( $this->get_settings_for_display('ts_reset_text') ) : ?>
								<div class="ts-filter-text">
									<?= $this->get_settings_for_display('ts_reset_text') ?>
								</div>
							<?php endif; ?>
						</a>
				</div>
			<?php endif ?>
		</div>
	</form>

	<?php if ( $switchable_desktop || $switchable_tablet || $switchable_mobile ): ?>
		<teleport to=".ts-switcher-btn-<?= esc_attr( $this->get_id() ) ?> .elementor-element" class="
			<?= ! $switchable_desktop ? 'vx-hidden-desktop' : '' ?>
			<?= ! $switchable_tablet ? 'vx-hidden-tablet' : '' ?>
			<?= ! $switchable_mobile ? 'vx-hidden-mobile' : '' ?>
		">
			<div class="ts-switcher-btn">
				<a href="#" class="ts-btn ts-btn-1
					<?= $desktop_default === 'map' ? '' : 'vx-hidden-desktop' ?>
					<?= $tablet_default === 'map' ? '' : 'vx-hidden-tablet' ?>
					<?= $mobile_default === 'map' ? '' : 'vx-hidden-mobile' ?>"
					@click.prevent="toggleListView"
					ref="listViewToggle"
				>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_list_icon') ) ?: \Voxel\svg( 'grid.svg' ) ?>
					<?= _x( 'List view', 'search form', 'voxel' ) ?>
				</a>
				<a href="#" class="ts-btn ts-btn-1
					<?= $desktop_default === 'map' ? 'vx-hidden-desktop' : '' ?>
					<?= $tablet_default === 'map' ? 'vx-hidden-tablet' : '' ?>
					<?= $mobile_default === 'map' ? 'vx-hidden-mobile' : '' ?>"
					@click.prevent="toggleMapView"
					ref="mapViewToggle"
				>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_map_icon') ) ?: \Voxel\svg( 'marker.svg' ) ?>
					<?= _x( 'Map view', 'search form', 'voxel' ) ?>
				</a>
			</div>
		</teleport>
	<?php endif ?>

	<div class="hidden" ref="mapNavTemplate">
		<div class="ts-map-nav">
			<a href="#" class="ts-map-prev ts-icon-btn"><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?></a>
			<a href="#" class="ts-map-next ts-icon-btn"><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?></a>
		</div>
	</div>
</div>

<?php foreach ( $deferred_templates as $template_path ): ?>
	<?php require_once $template_path ?>
<?php endforeach ?>

<?php

$main_script = wp_scripts()->registered['vx:search-form.js'] ?? null;
if ( $main_script !== null ) {
	if ( ! is_array( $main_script->deps ) ) {
		$main_script->deps = [];
	}

	$required_script_handles = array_keys( $required_scripts );
	array_push( $main_script->deps, ...$required_script_handles );
}
?>