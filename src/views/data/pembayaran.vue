<script setup>
	import { onMounted, reactive, ref, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import AutoComplete from '../../components/AutoComplete.vue';
	import { http, notify } from '../../lib';
	import { useRoute, useRouter } from 'vue-router';
	import PembayaranSpp from './pembayaran/spp.vue';
	import PembayaranTabungan from './pembayaran/tabungan.vue';
	import PembayaranBiayaLain from './pembayaran/biaya-lain.vue';

	const route = useRoute();
	const router = useRouter();

	const nisn = ref(route.query.siswa_nisn);

	const state = reactive({
		siswa: {
			items: [],
			item: {
				jurusan: {},
			},
		},
		siswa_nisn: nisn.value,
		tanggungan: 'spp',
	});

	const componentPembayaran = {
		spp: PembayaranSpp,
		biayaLain: PembayaranBiayaLain,
		tabungan: PembayaranTabungan,
	};

	function get() {
		http
			.get('/siswa')
			.then((res) => res.json())
			.then((res) => (state.siswa.items = res))
			.catch((err) => notify(err));
	}

	function getItem() {
		http
			.get('/siswa/' + nisn.value)
			.then((res) => res.json())
			.then((res) => (state.siswa.item = res));
	}

	function handleSubmit() {
		router.push('/data/pembayaran?siswa_nisn=' + state.siswa_nisn);
	}

	watch(
		() => route.query.siswa_nisn,
		(val) => {
			nisn.value = state.siswa_nisn = val ? val : '';
			if (val) getItem();
		},
	);

	onMounted(() => {
		get();
		if (nisn.value) getItem();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data Pembayaran</div>
	<div class="flex -m-2 flex-wrap">
		<div class="lg:w-2/5 p-2 w-full">
			<Card>
				<form @submit.prevent="handleSubmit" class="form-field">
					<AutoComplete
						placeholder="Pilih Siswa"
						:items="
							state.siswa.items.map((item) => ({
								value: item.nisn,
								text: item.nisn + ' - ' + item.nama,
								nisn: item.nisn,
								nama: item.nama,
							}))
						"
						v-model="state.siswa_nisn"
						required
						v-slot="{ item }"
					>
						<span class="font-semibold">{{ item.nisn }}</span>
						<br />
						{{ item.nama }}
					</AutoComplete>
					<button type="submit" class="w-full">Cari</button>
				</form>
			</Card>
		</div>
		<div class="lg:w-3/5 p-2 w-full" v-if="nisn">
			<Card title="Detail Siswa" class="!bg-gradient-to-bl from-blue-400 to-blue-500 !text-white">
				<div class="overflow-x-auto">
					<table class="whitespace-nowrap">
						<tr>
							<td>NISN</td>
							<td class="px-2">:</td>
							<td>{{ state.siswa.item.nisn }}</td>
						</tr>
						<tr>
							<td>Nama</td>
							<td class="px-2">:</td>
							<td>{{ state.siswa.item.nama }}</td>
						</tr>
						<tr>
							<td>Kelas</td>
							<td class="px-2">:</td>
							<td>
								{{ state.siswa.item.kelas }} {{ state.siswa.item.jurusan.kode }}
								{{ state.siswa.item.rombel }}
							</td>
						</tr>
					</table>
				</div>
			</Card>
		</div>
	</div>
	<div v-if="nisn">
		<div class="pb-4 border-b mb-4"></div>
		<div class="w-full form-field" lg="w-1/2">
			<select v-model="state.tanggungan">
				<option disabled value="">Pilih Tanggungan</option>
				<option value="spp">SPP</option>
				<option value="biayaLain">Biaya Lain</option>
				<option value="tabungan">Tabungan</option>
			</select>
		</div>
		
		<div class="custom-main-admin">
			<component :siswa="state.siswa.item" :is="componentPembayaran[state.tanggungan]"></component>
		</div>
	</div>
</template>
