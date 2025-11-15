<?php
/**
 * Edit taxonomy form in WP Admin.
 *
 * @since 1.0
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<div id="voxel-edit-taxonomy" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" @submit="prepareSubmission">
		<div class="sticky-top">
			<div class="x-container vx-head">
				<h2><?= esc_html( $taxonomy->get_label() ) ?></h2>

				<div>
					<input type="hidden" name="action" value="voxel_save_taxonomy_settings">
					<input type="hidden" name="taxonomy_config" :value="submit_config">
					<?php wp_nonce_field( 'voxel_save_taxonomy_settings' ) ?>

					<button type="button" name="remove_taxonomy" value="yes" class="ts-button ts-transparent"
						onclick="return confirm('Are you sure?') ? ( this.type = 'submit' ) && true : false">
						<?= $taxonomy->is_created_by_voxel() ? 'Delete' : 'Stop managing with Voxel' ?>
					</button>
					&nbsp;&nbsp;

					<button type="submit" class="ts-button ts-save-settings btn-shadow">Save changes</button>
				</div>
			</div>
		</div>
		<div class="ts-spacer"></div>
		<div class="x-container">
			<div class="ts-tab-content">
				<div class="x-row">
					<div class="x-col-12 ts-content-head">
						<h1>General</h1>
						<p>General settings related to this taxonomy</p>
					</div>
				</div>

				<div class="x-row h-center">
					<div class="x-col-4">
						<ul class="inner-tabs vertical-tabs">
							<li :class="{'current-item': $root.subtab === 'base'}">
								<a href="#" @click.prevent="$root.setTab('general', 'base')">Basic</a>
							</li>
							<li :class="{'current-item': $root.subtab === 'permalinks'}">
								<a href="#" @click.prevent="$root.setTab('general', 'permalinks')">Permalinks</a>
							</li>
							<li :class="{'current-item': $root.subtab === 'other'}">
								<a href="#" @click.prevent="$root.setTab('general', 'other')">Advanced</a>
							</li>
						</ul>
					</div>

					<div class="inner-tab x-col-8">
						<template v-if="$root.subtab === 'base'">
							<div class="ts-group">
								<div class="ts-group-head">
									<h3>Basic</h3>
								</div>
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'label' => 'Singular name',
										'v-model' => 'taxonomy.settings.singular',
										'classes' => 'x-col-4',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'label' => 'Plural name',
										'v-model' => 'taxonomy.settings.plural',
										'classes' => 'x-col-4',
									] ) ?>

									<div class="ts-form-group x-col-4">
										<label>Taxonomy key</label>
										<input :value="taxonomy.settings.key" type="text" disabled><br>
									</div>
								</div>
							</div>

							<div class="ts-group">
								<div class="ts-group-head">
									<h3>Post type(s)</h3>
								</div>
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Checkboxes_Model::render( [
										// 'label' => 'Post type(s)',
										'v-model' => 'taxonomy.settings.post_type',
										'classes' => 'x-col-12',
										'columns' => 'three',
										'choices' => array_map( function( $post_type ) {
											return $post_type->get_label();
										}, \Voxel\Post_Type::get_voxel_types() ) + array_map( function( $post_type ) {
											return $post_type->get_label();
										}, \Voxel\Post_Type::get_other_types() ),
									] ) ?>
								</div>
							</div>
						</template>
						<template v-if="$root.subtab === 'permalinks'">
							<div class="ts-group">
								<div class="ts-group-head">
									<h3>Permalinks</h3>
								</div>
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
										'label' => 'Custom permalink base',
										'v-model' => 'taxonomy.settings.permalinks.custom',
										'classes' => 'x-col-12',
									] ) ?>

									<template v-if="taxonomy.settings.permalinks.custom">
										<?php \Voxel\Utils\Form_Models\Text_Model::render( [
											'label' => 'Permalink base',
											'v-model' => 'taxonomy.settings.permalinks.slug',
											'classes' => 'x-col-12',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
											'v-if' => 'taxonomy.settings.key !== "post_tag"',
											'label' => 'Hierarchical',
											'v-model' => 'taxonomy.settings.permalinks.hierarchical',
											'classes' => 'x-col-12',
											'description' => 'If enabled, ancestor term slugs will be prepended to the term permalink',
										] ) ?>

										<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
											'label' => 'With front',
											'v-model' => 'taxonomy.settings.permalinks.with_front',
											'v-if' => 'config.permalink_front !== "/"',
											'classes' => 'x-col-12',
											'description' => 'If enabled, the static permalink front configured in WP Admin > Settings > Permalinks will be prepended to the term permalink',
										] ) ?>

										<div class="ts-form-group x-col-12">
											<p>
												<?= home_url('/') ?>{{
													(taxonomy.settings.permalinks.with_front && config.permalink_front !== '/' ? config.permalink_front.substr(1) : '')
													+taxonomy.settings.permalinks.slug+'/'
													+(taxonomy.settings.permalinks.hierarchical ? 'parent-term/' : '')
													+'sample-term'
												}}
											</p>
										</div>
									</template>
								</div>
							</div>
						</template>
						<template v-else-if="$root.subtab === 'other'">
							<div class="ts-group">
								<div class="ts-group-head">
									<h3>Advanced options</h3>
								</div>
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'label' => 'Enable hierarchical structure',
										'v-model' => 'taxonomy.settings.hierarchical',
										'classes' => 'x-col-12',
										'description' => 'Hierarchical structure lets you arrange terms into parent-child groups.',
										'choices' => [
											'auto' => 'Auto',
											'yes' => 'Yes',
											'no' => 'No',
										],
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'label' => 'Is this taxonomy publicly queryable',
										'description' => 'Enable if this taxonomy is important to your SEO structure. If enabled, you can design the Single term templates and the taxonomy appears in your sitemap',
										'v-model' => 'taxonomy.settings.publicly_queryable',
										'classes' => 'x-col-12',
										'choices' => [
											'auto' => 'Auto',
											'yes' => 'Yes',
											'no' => 'No',
										],
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'label' => 'Show this taxonomy in admin quick edit form for posts',
										'description' => 'Make this taxonomy available for bulk-editing through the post list table in WP Admin',
										'v-model' => 'taxonomy.settings.show_in_quick_edit',
										'classes' => 'x-col-12',
										'choices' => [
											'auto' => 'Auto',
											'yes' => 'Yes',
											'no' => 'No',
										],
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'label' => 'Show this taxonomy in REST API',
										'description' => 'Whether to include the taxonomy in the REST API. Set this to Yes for the taxonomy to be available in the block editor.',
										'v-model' => 'taxonomy.settings.show_in_rest',
										'classes' => 'x-col-12',
										'choices' => [
											'auto' => 'Auto',
											'yes' => 'Yes',
											'no' => 'No',
										],
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'label' => 'Display a column for this taxonomy in the admin post list table',
										'v-model' => 'taxonomy.settings.show_admin_column',
										'classes' => 'x-col-12',
										'choices' => [
											'auto' => 'Auto',
											'yes' => 'Yes',
											'no' => 'No',
										],
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Select_Model::render( [
										'v-model' => 'taxonomy.settings.default_archive_query',
										'label' => 'Native archive query',
										'description' => <<<TEXT
											If enabled, the native WordPress post query will run in the single term page.

											This query is only necessary if you intend to display posts using the "WP default archive" mode of the Post feed (VX) widget.

											If you're instead using one of the "Search form", "Filters", or "Manual" modes, this query should be disabled to avoid any performance impact.
											TEXT,
										'classes' => 'x-col-12',
										'choices' => [
											'disabled' => 'Disabled (recommended)',
											'enabled' => 'Enabled',
										],
									] ) ?>

									<div class="ts-form-group x-col-12">
										<p>
											<br>
											<strong>Note:</strong> The value "Auto" instructs Voxel not to modify the original behavior for that setting.<br>
											For taxonomies registered manually (through the child theme or a 3rd-party plugin), "Auto" preserves the original configuration used during the registration of the taxonomy.
										</p>
									</div>
								</div>
							</div>
						</template>
					</div>
				</div>
			</div>
		</div>
	</form>
	<!-- <pre debug>{{ taxonomy.settings }}</pre> -->
</div>
