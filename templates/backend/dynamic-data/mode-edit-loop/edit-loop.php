<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-mode-edit-loop">
	<div class="nvx-editor nvx-editor-loop">
		<div class="nvx-topbar">
			<div class="nvx-topbar__title nvx-flex nvx-v-center">
				<h2>Select loop source</h2>
			</div>

			<div class="nvx-topbar__buttons nvx-flex nvx-v-center">
				<button @click.prevent="discard" type="button" class="ts-button ts-outline">Discard</button>
			</div>
		</div>
		<div class="nvx-editor-body">
			<div class="nvx-scrollable nvx-loops">
				<div class="nvx-loops-container">
					<div class="nvx-mod-list">
					<template v-for="group, groupKey in groups">
					<template v-if="group._has_loopables">
						<div class="nvx-mod" :class="{'mod-open': activeGroup === group}">
							<div class="nvx-mod-title" @click.prevent="activeGroup = ( activeGroup === group ? null : group )">
								{{ group.label }}
								<div class="nvx-mod-actions">
									<a href="#" class="ts-button ts-outline icon-only">
										<?= \Voxel\get_svg( 'arrow-down.svg' ) ?>
									</a>
								</div>
							</div>
							<div v-if="activeGroup === group" class="nvx-mod-content">
								<loopable-property-list
									:properties="group.group.exports"
									:path="[]"
									:groupKey="groupKey"
									@select="onSelect($event)"
									:container="this"
									:ref="'propList:'+groupKey"
								></loopable-property-list>
							</div>
						</div>
					</template>
					</template>
				</div>
				</div>
			</div>
			<!-- <pre debug>{{ $data }}</pre> -->
		</div>
	</div>
</script>

<script type="text/html" id="vx-dynamic-loopable-property-list">
	<template v-for="property, propertyKey in properties">
		<template v-if="property._has_loopables || property.type === 'object-list'">
			<div class="nvx-mod" :class="{'mod-open': activeProperty === property, 'mod-active': isSelected( property, propertyKey )}">
				<div class="nvx-mod-title" @click.prevent="activeProperty = ( activeProperty === property ? null : property )">
					{{ property.label }}
					<div class="nvx-mod-actions">
						<template v-if="property._has_loopables">
							<a href="#" class="ts-button ts-outline icon-only">
								<?= \Voxel\get_svg( 'arrow-down.svg' ) ?>
							</a>
						</template>
						<template v-if="property.type === 'object-list'">
							<a class="ts-button ts-outline" style="width: auto;" @click.prevent="useLoopItem(property, propertyKey)" href="#">Use loop</a>
						</template>
					</div>
				</div>
				<div v-if="activeProperty === property && property._has_loopables" class="nvx-mod-content">
					<loopable-property-list
						:properties="property.exports"
						:path="path.concat([propertyKey])"
						:groupKey="groupKey"
						@select="$emit('select', $event)"
						:container="container"
						ref="propList"
					></loopable-property-list>
				</div>
			</div>
		</template>
	</template>
</script>
