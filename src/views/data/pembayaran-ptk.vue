<script setup>
	import { onMounted, reactive, ref, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import AutoComplete from '../../components/AutoComplete.vue';
	import { http, notify } from '../../lib';
	import { useRoute, useRouter } from 'vue-router';
  import PembayaranTabunganPTK from './pembayaran-ptk/tabungan.vue';

	const route = useRoute();
	const router = useRouter();

	const ptk = ref(route.query.ptk_kode);

	const state = reactive({
		ptk: {
			items: [],
			item: {},
		},
		ptk_kode: ptk.value,
	});

	function get() {
		http
			.get('/ptk')
			.then((res) => res.json())
			.then((res) => (state.ptk.items = res))
			.catch((err) => notify(err));
	}

	function getItem() {
    http.get('/ptk/by-kode?kode=' + ptk.value).then(res => res.json()).then((res) => state.ptk.item = res);
	}

	function handleSubmit() {
		router.push('/data/pembayaran-ptk?ptk_kode=' + state.ptk_kode);
	}

	watch(
		() => route.query,
		(val) => {
			ptk.value = state.ptk_kode = val.ptk_kode ? val.ptk_kode : '';
			if (val) getItem();
		},
	);

	onMounted(() => {
		get();
		if (ptk.value) getItem();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Pembayaran PTK</div>
	<div class="flex -m-2 flex-wrap">
		<div class="lg:w-2/5 p-2 w-full">
			<Card>
				<form @submit.prevent="handleSubmit" class="form-field">
					<!-- <input type="text" required placeholder="Masukkan Kode PTK" v-model="state.ptk_kode" /> -->
					<AutoComplete
						placeholder="Masukkan Kode PTK"
						:items="
							state.ptk.items.map((item) => ({
								value: item.kode,
								text: item.kode,
							}))
						"
						v-model="state.ptk_kode"
						required
						v-slot="{ item }"
					>
					</AutoComplete>
					<button type="submit" class="w-full">Cari</button>
				</form>
			</Card>
		</div>
		<div class="lg:w-3/5 p-2 w-full" v-if="state.ptk.item.kode">
			<Card title="Detail Siswa" class="!bg-gradient-to-bl from-blue-400 to-blue-500 !text-white">
				<div class="overflow-x-auto">
					<table class="whitespace-nowrap">
						<tr>
							<td>Kode</td>
							<td class="px-2">:</td>
							<td>{{ decodeURIComponent(state.ptk.item.kode) }}</td>
						</tr>
						<tr>
							<td>Nama</td>
							<td class="px-2">:</td>
							<td>{{ state.ptk.item.nama }}</td>
						</tr>
						<tr>
							<td>Jabatan</td>
							<td class="px-2">:</td>
							<td>
								{{ state.ptk.item.jabatan }}
							</td>
						</tr>
					</table>
				</div>
			</Card>
		</div>
		<div class="lg:w-3/5 p-2 w-full" v-else>
			<Card title="Detail Siswa" class="!bg-blue-100">
				<div class="text-center">PTK tidak ditemukan.</div>
			</Card>
		</div>
	</div>
	<div v-if="state.ptk.item.kode">
		<div class="pb-4 border-b mb-4"></div>
		<div class="custom-main-admin">
      <PembayaranTabunganPTK :ptk="state.ptk.item" />
			<!-- <component :siswa="state.siswa.item" :is="componentPembayaran[state.tanggungan]"></component> -->
		</div>
	</div>
</template>
