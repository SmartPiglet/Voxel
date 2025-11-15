<script type="text/javascript">
	window.Voxel_Icon_Picker_Config = <?= wp_json_encode( $config ) ?>;
</script>

<script type="text/html" id="voxel-icon-picker-template">
	<div class="icon-picker-modal" v-if="opened" v-cloak>
		<div class="ts-modal-backdrop" @click="close"></div>
		<div class="icons-modal ts-theme-options">
			<div v-if="activePack?.list?.length" class="x-row">
				<div class="inner-tab x-col-4">
					<div class="x-row">
						<div class="ts-form-group x-col-12">
							<label>Search icons</label>
							<input type="text" v-model="search" @input="filter" placeholder="Search icon">
						</div>
						<div class="x-col-12">
							<ul class="inner-tabs vertical-tabs">
								<li v-for="pack, pack_key in config" :class="{'current-item': pack.name === activePack.name}">
									<a href="#" @click.prevent="setPack( pack_key )">
										<i :class="pack.labelIcon"></i>
										{{ pack.label }}
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="icon-list-wrapper min-scroll x-col-8">
					<div v-if="search.length" class="filtered-icons">
						<div v-for="icons, pack_key in searchResults">
							<code>{{ config[pack_key].label }}</code>
							<div class="icon-list">
								<div v-for="icon in icons" class="single-icon" @click.prevent="selectIcon(icon, pack_key)">
									<i :class="[ config[pack_key].displayPrefix, config[pack_key].prefix+icon ]"></i>
									<span>{{ icon }}</span>
								</div>
							</div>
						</div>
					</div>
					<div v-else class="icon-list">
						<div v-for="icon in activePack.list" class="single-icon" @click.prevent="selectIcon(icon, activePack.name)">
							<i :class="[ activePack.displayPrefix, activePack.prefix+icon ]"></i>
							<span>{{ icon }}</span>
						</div>
					</div>
					<!-- <pre>{{ activePack }}</pre> -->
				</div>
			</div>
			<div v-else class="x-row">
				<div class="x-col-12" style="text-align: center;">
					<label>No icon packs available.</label>
				</div>
			</div>
		</div>
	</div>
</script>

<div id="voxel-icon-picker"></div>
