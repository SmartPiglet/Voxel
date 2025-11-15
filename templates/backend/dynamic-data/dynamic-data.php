<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

wp_enqueue_script('vue');
wp_enqueue_script('sortable');
wp_enqueue_script('vue-draggable');
wp_enqueue_script('vx:dynamic-data.js');

require_once locate_template('templates/backend/dynamic-data/mode-edit-content/edit-content.php');
require_once locate_template('templates/backend/dynamic-data/mode-edit-content/_edit-tag.php');
require_once locate_template('templates/backend/dynamic-data/mode-edit-visibility/edit-visibility.php');
require_once locate_template('templates/backend/dynamic-data/mode-edit-loop/edit-loop.php');
require_once locate_template('templates/backend/dynamic-data/_code-editor.php');
require_once locate_template('templates/backend/dynamic-data/_tag-autocomplete.php');
require_once locate_template('templates/backend/dynamic-data/_mod-autocomplete.php');
require_once locate_template('templates/backend/dynamic-data/_group-list.php');

$exporter = \Voxel\Dynamic_Data\Exporter::get();

// always export common groups
$exporter->add_group_by_key('user');
$exporter->add_group_by_key('site');
$exporter->add_group_by_key('simple-post');
$exporter->add_group_by_key('term');
$exporter->add_group_by_key('value');

$exports = $exporter->export();
?>

<script type="text/javascript">
	window.Dynamic_Data_Store = <?= wp_json_encode( [
		'groups' => $exports['groups'],
		'modifiers' => $exports['modifiers'],
		'visibility_rules' => $exports['visibility_rules'],
	] ) ?>
</script>

<div id="vx-dynamic-data">
	<template v-if="visible">
		<template v-if="mode === 'edit-content'">
			<mode-edit-content v-model="state.editContent.content" ref="editContent"></mode-edit-content>
		</template>
		<template v-else-if="mode === 'edit-visibility'">
			<mode-edit-visibility v-model="state.editVisibility.rules" ref="editVisibility"></mode-edit-visibility>
		</template>
		<template v-else-if="mode === 'edit-loop'">
			<mode-edit-loop v-model="state.editLoop.selected" ref="editLoop"></mode-edit-loop>
		</template>
	</template>
</div>
