<script setup="">
	import { reactive } from 'vue';
	import baganVue from './spp/bagan.vue';
	import kekuranganVue from './spp/kekurangan.vue';
	import perbulanVue from './spp/perbulan.vue';
	import perhariVue from './spp/perhari.vue';
	import perkelasVue from './spp/perkelas.vue';

	const state = reactive({
		laporan: 'perhari',
	});

	const component = {
		perhari: perhariVue,
		perbulan: perbulanVue,
		perkelas: perkelasVue,
		bagan: baganVue,
		kekurangan: kekuranganVue,
	};
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Laporan SPP</div>
	<div class="flex">
		<div class="w-full form-field" lg="w-1/2">
			<select v-model="state.laporan">
				<option disabled value="">Pilih Laporan SPP</option>
				<option value="perhari">Laporan Perhari</option>
				<option value="perbulan">Laporan Perbulan</option>
				<option value="perkelas">Laporan Perkelas</option>
				<option value="bagan">Laporan Bagan</option>
				<option value="kekurangan">Laporan Kekurangan</option>
			</select>
		</div>
	</div>
	<div class="pt-3 border-t"></div>
	<component :is="component[state.laporan]"></component>
</template>
