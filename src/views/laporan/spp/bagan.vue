<script setup>
	import { computed, onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { formatMoney, http, notify, nowDate } from '../../../lib';
	import AutoComplete from '../../../components/AutoComplete.vue';
	import { bulan } from '../../data/pembayaran/spp.vue';

	const state = reactive({
		filter: {
			_kelas_jurusan_rombel: '',
		},
		data: [],
		kelas_rombel_jurusan: [],
		excel_url: '',
		print_url: '',
	});

	const keys = computed(() => {
		let key = {
			No: 'no',
			Nisn: 'nisn',
			Nama_Siswa: 'nama',
		};

		bulan.forEach((item) => {
			key[item.slice(0, 3)] = item;
		});

		key['total'] = 'total';

		return key;
	});

	function get() {
		http
			.get('/laporan/spp/bagan', state.filter)
			.then((res) => {
				state.excel_url = res.headers.get('X-Excel-Url');
				state.print_url = res.headers.get('X-Print-Url');
				return res.json();
			})
			.then((res) => {
				state.data = res;
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
	<Card title="Laporan Bagan">
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
		<div class="overflow-x-auto">
			<table class="w-full whitespace-nowrap">
				<thead>
					<tr class="text-sm font-semibold">
						<td class="text-center uppercase p-3">NO</td>
						<td class="text-center uppercase p-3">NISN</td>
						<td class="text-center uppercase p-3">Nama Siswa</td>
						<td v-for="item in bulan" class="text-center uppercase p-3">
							{{ item.slice(0, 3) }}
						</td>
						<td class="text-center uppercase p-3">Total</td>
					</tr>
				</thead>
				<tbody>
					<template v-if="state.data.length" v-for="(item, i) in state.data">
						<tr class="border border-gray-100 transition hover:bg-blue-500 hover:text-white">
							<td class="p-3">{{ i + 1 }}</td>
							<td class="p-3">{{ item.nisn }}</td>
							<td class="p-3">{{ item.nama }}</td>
							<template v-for="b in bulan">
								<td class="p-3">
									{{ JSON.stringify(item.spp).indexOf(b) >= 0 ? '1' : '0' }}
								</td>
							</template>
							<td class="p-3">{{ item.spp.length }}</td>
						</tr>
						<tr class="h-3"></tr>
					</template>
					<tr v-else class="border border-gray-100">
						<td colspan="99" class="p-3 text-center">Data tidak tersedia.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</Card>
</template>
