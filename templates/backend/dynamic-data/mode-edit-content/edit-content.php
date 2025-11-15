<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-mode-edit-content">
	<div class="nvx-editor">
		<div class="nvx-topbar">
			<div class="nvx-topbar__title nvx-flex nvx-v-center">
				<h2>Dynamic data</h2>
			</div>

			<div class="nvx-topbar__buttons nvx-flex nvx-v-center">
				<button @click.prevent="discard" type="button" class="ts-button ts-outline">Discard</button>
				<button @click.prevent="save" type="button" class="ts-button btn-shadow ts-save-settings"><?= \Voxel\get_svg( 'floppy-disk.svg' ) ?> Save</button>
			</div>
		</div>
		<div class="nvx-editor-body">
			<div class="nvx-left-sidebar nvx-scrollable" @keydown="searchNavigateTags" ref="sideTags">
				<div class="ts-form-group">
					<input type="text" @beforeinput="searchBeforeInput($event)" placeholder="Search tags" v-model="search" @blur="searchBlurred">
				</div>
				<group-list ref="mainGroupList" :search="search" @select-tag="searchTagSelected($event)"></group-list>
			</div>
			<div class="nvx-main">
				<div class="nvx-visual-editor">
					<code-editor v-model="content" ref="codeEditor" @token-focus="onTokenFocus"></code-editor>
				</div>
			</div>

			<template v-if="editTag.token">
				<edit-tag :token="editTag.token" @tag-updated="onTagUpdate"></edit-tag>
			</template>
			<template v-else>
				<div class="nvx-right-sidebar nvx-scrollable" >
					<div class="nvx-placeholder placeholder-all-center"><?= \Voxel\get_svg( 'wrench.svg' ) ?>Click on a tag to view options</div>
				</div>
			</template>
		</div>
	</div>
</script>