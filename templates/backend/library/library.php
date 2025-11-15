<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>


<div id="voxel-library" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>" v-cloak>
	<div class="sticky-top">
		<div class="vx-head x-container">
				<h2>Library</h2>
				<div class="vx-head-actions">
					<a href="#" class="ts-button ts-outline" @click.prevent="screen = 'library'; $event.shiftKey ? reloadLibrary() : '';">
						<i class="las la-layer-group icon-sm"></i> Library
					</a>
					<a href="#" class="ts-button ts-outline" @click.prevent="screen = 'export'">
						<i class="las la-download icon-sm"></i> Export
					</a>
					<a href="#" class="ts-button ts-outline" @click.prevent="screen = 'import'">
						<i class="las la-cloud-upload-alt icon-sm"></i> Import
					</a>
				</div>


		</div>
	</div>
	<div class="ts-spacer"></div>
	<div class="x-container">
		<div v-if="screen === 'export'">
			<?php require_once locate_template('templates/backend/library/export-screen.php') ?>
		</div>
		<div v-else-if="screen === 'import'">
			<?php require_once locate_template('templates/backend/library/import-screen.php') ?>
		</div>
		<div v-else-if="screen === 'install'">
			<?php require_once locate_template('templates/backend/library/install-screen.php') ?>
		</div>
		<div v-else-if="screen === 'library'">

			<div class="x-row">

				<div class="x-col-8 ts-content-head">
					<h1>Preset post types</h1>
					<p>Import post types from various Voxel demos.</p>
				</div>
			</div>

			<?php require_once locate_template('templates/backend/library/library-screen.php') ?>
		</div>
		<div v-else-if="screen === 'success'">
			<div class="x-row">
				<div class="x-col-12">
					<div class="ts-tab-heading">
						<h1>Package has been installed successfully.</h1>
					</div>

				</div>
				<div class="x-col-12">
					<div class="basic-ul">
						<a href="#" @click.prevent="screen = 'library'" class="ts-button ts-faded"><i class="las la-layer-group icon-sm"></i> Library</a>
						<a href="#" @click.prevent="screen = 'import'" class="ts-button ts-faded"><i class="las la-cloud-upload-alt icon-sm"></i> Import</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

