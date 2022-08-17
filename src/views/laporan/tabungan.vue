<script setup="">
	import { reactive } from 'vue';
	import perhariVue from './tabungan/perhari.vue';
	import perbulanVue from './tabungan/perbulan.vue';
	import perkelasVue from './tabungan/perkelas.vue';

	const state = reactive({
		laporan: 'perhari',
	});

	const component = {
		perhari: perhariVue,
		perbulan: perbulanVue,
		perkelas: perkelasVue,
	};
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Laporan Tabungan</div>
	<div class="flex">
		<div class="w-full form-field" lg="w-1/2">
			<select v-model="state.laporan">
				<option disabled value="">Pilih Laporan Tabungan</option>
				<option value="perhari">Laporan Perhari</option>
				<option value="perbulan">Laporan Perbulan</option>
				<option value="perkelas">Laporan Perkelas</option>
			</select>
		</div>
	</div>
	<div class="pt-3 border-t"></div>
	<component :is="component[state.laporan]"></component>
</template>
