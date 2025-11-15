<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vxfeed__emoji-picker">
	<a href="#" class="vxf-icon vxf-emoji-picker" ref="target" @click.prevent @mousedown="open">
		<icon-emoji/>
	</a>
	<teleport to="body">
		<transition name="form-popup">
			<form-popup v-if="isOpen" :target="'#'+composer.uniqueId" class="ts-emoji-popup" @blur="isOpen = false">
				<div class="ts-sticky-top uib b-bottom">
					<div class="ts-input-icon flexify">
						<icon-search/>
						<input type="text" v-model="search.term" placeholder="<?= esc_attr( _x( 'Search emojis', 'messages', 'voxel' ) ) ?>" class="autofocus">
					</div>
				</div>
				<div class="ts-emoji-list">
					<template v-if="search.term.trim()">
						<div class="ts-form-group">
							<label v-if="search.list.length"><?= _x( 'Search results', 'emoji popup', 'voxel' ) ?></label>
							<label v-else><?= _x( 'No emojis found', 'emoji popup', 'voxel' ) ?></label>
							<ul class="flexify simplify-ul">
								<li v-for="emoji in search.list"><span @click.prevent="insert( emoji )">{{ emoji }}</span></li>
							</ul>
						</div>
					</template>
					<template v-else>
						<template v-if="recents.length">
							<div class="ts-form-group">
								<label><?= _x( 'Recently used', 'emoji popup', 'voxel' ) ?></label>
								<ul class="flexify simplify-ul">
									<li v-for="emoji in recents"><span @click.prevent="insert( emoji )">{{ emoji }}</span></li>
								</ul>
							</div>
						</template>
						<template v-if="!loading && list">
							<template v-for="group, label in list">
								<div class="ts-form-group">
								    <label>{{ $root.config.l10n.emoji_groups[label] || label }}</label>
									<ul class="flexify simplify-ul">
										<li v-for="emoji in group"><span @click.prevent="insert( emoji.emoji )">{{ emoji.emoji }}</span></li>
									</ul>
								</div>
							</template>
						</template>
					</template>
				</div>
				<template #controller><template></template></template>
			</form-popup>
		</transition>
	</teleport>
</script>