<script setup>
	import { computed, onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { formatMoney, http, notify } from '../../../lib';
	import AutoComplete from '../../../components/AutoComplete.vue';

	const state = reactive({
		filter: {
			_kelas_jurusan_rombel: '',
		},
		data: [],
		kelas_rombel_jurusan: [],
		total_kekurangan: 0,
		excel_url: '',
		print_url: '',
	});

	const keys = computed(() => {
		let key = {
			No: 'no',
			Nisn: 'nisn',
			Nama_Siswa: 'nama',
			Kekurangan: 'kekurangan',
			Jumlah_kekurangan: 'jumlah_kekurangan',
		};

		return key;
	});

	function get() {
		http
			.get('/laporan/spp/kekurangan', state.filter)
			.then((res) => {
				state.excel_url = res.headers.get('X-Excel-Url');
				state.print_url = res.headers.get('X-Print-Url');
				return res.json();
			})
			.then((res) => {
				state.total_kekurangan = 0;
				state.data = res;
				res.forEach((item) => {
					state.total_kekurangan +=
						item.kekurangan *
						(item.jurusan.jumlah_spp - (item.jurusan.jumlah_spp * item.diskon_spp) / 100);
				});
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	function getData() {
		http
			.get('/kelas-jurusan-rombel')
			.then((res) => res.json())
			.then((res) => (state.kelas_rombel_jurusan = res));
	}

	watch(state.filter, () => {
		get();
	});

	onMounted(() => {
		getData();
	});
</script>

<template>
	<Card title="Laporan Kekurangan">
		<div class="form-field">
			<AutoComplete
				v-model="state.filter._kelas_jurusan_rombel"
				:items="
					state.kelas_rombel_jurusan.map((item) => ({
						value: item.kelas + '-' + item.jurusan_kode + '-' + item.rombel,
						text: item.kelas + ' ' + item.jurusan_kode + ' ' + item.rombel,
					}))
				"
				placeholder="Pilih Kelas"
			></AutoComplete>
		</div>
		<div class="p-4 bg-gray-50 rounded mb-4">
			Kelas: {{ state.filter._kelas_jurusan_rombel.replace(/-/gi, ' ') }}
		</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table :keys="keys" :items="state.data">
			<template #no="{ index }">
				{{ index + 1 }}
			</template>

			<template #kekurangan="{ item }">
				{{ item.kekurangan }}
			</template>

			<template #jumlah_kekurangan="{ item }">
				{{
					formatMoney(
						item.kekurangan *
							(item.jurusan.jumlah_spp - (item.jurusan.jumlah_spp * item.diskon_spp) / 100),
					)
				}}
			</template>
		</Table>
		<div class="p-4 bg-gray-50 rounded mb-4">Total: {{ formatMoney(state.total_kekurangan) }}</div>
	</Card>
</template>
