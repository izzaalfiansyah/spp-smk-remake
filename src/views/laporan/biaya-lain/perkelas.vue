<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import { formatMoney, http, notify } from '../../../lib';
	import AutoComplete from '../../../components/AutoComplete.vue';

	const state = reactive({
		filter: {
			_kelas_jurusan_rombel: '',
			_biaya_lain_id: '',
		},
		total: 0,
		total_kekuragan: 0,
		data: [],
		biaya_lain: [],
		kelas_rombel_jurusan: [],
		excel_url: '',
		print_url: '',
	});

	function get() {
		if (state.filter._kelas_jurusan_rombel) {
			http
				.get('/laporan/biaya-lain/perkelas', state.filter)
				.then((res) => {
					state.excel_url = res.headers.get('X-Excel-Url');
					state.print_url = res.headers.get('X-Print-Url');
					return res.json();
				})
				.then((res) => {
					state.total = 0;
					state.data = res;

					res.forEach((item) => {
						item.biaya_lain.forEach((biaya) => {
							state.total += biaya.terbayar;
							state.total_kekuragan += biaya.jumlah_bayar - biaya.terbayar;
						});
					});
				})
				.catch((err) => notify(err, 'bg-red-400'));
		}
	}

	function getKelasJurusanRombel() {
		http
			.get('/kelas-jurusan-rombel')
			.then((res) => res.json())
			.then((res) => (state.kelas_rombel_jurusan = res));
	}

	function getBiayaLain() {
		const kelas_jurusan_kode = state.filter._kelas_jurusan_rombel.split('-');
		const _kelas = kelas_jurusan_kode[0];
		const _jurusan_kode = kelas_jurusan_kode[1];

		http
			.get('/biaya-lain', (_kelas, _jurusan_kode) ? { _kelas, _jurusan_kode } : {})
			.then((res) => res.json())
			.then((res) => (state.biaya_lain = res));
	}

	watch(state.filter, () => {
		get();
	});

	watch(
		() => state.filter._kelas_jurusan_rombel,
		() => {
			getBiayaLain();
		},
	);

	onMounted(() => {
		getKelasJurusanRombel();
		getBiayaLain();
	});
</script>

<template>
	<Card title="Laporan Perkelas">
		<div class="form-field">
			<div class="lg:flex gap-3">
				<div class="w-full" lg="w-1/2">
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
				<div class="w-full" lg="w-1/2">
					<select v-model="state.filter._biaya_lain_id">
						<option value="">Semua Biaya Lain</option>
						<option v-for="item in state.biaya_lain" :value="item.id">{{ item.jenis }}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="p-4 bg-gray-50 rounded mb-4">
			Kelas: {{ state.filter._kelas_jurusan_rombel.replace(/-/gi, ' ') }}
		</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" v-show="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" v-show="state.print_url" class="text-red-500">PRINT</a>
		</div>

		<div class="overflow-x-auto">
			<table class="w-full overflow-hidden whitespace-nowrap">
				<thead>
					<tr class="text-sm">
						<td rowspan="2" class="text-center border border-gray-100 p-3 font-semibold">NO</td>
						<td rowspan="2" class="text-center border border-gray-100 p-3 font-semibold">NISN</td>
						<td rowspan="2" class="text-center border border-gray-100 p-3 font-semibold">NAMA</td>
						<td
							v-if="state.data[0]"
							v-for="item in state.data[0].biaya_lain"
							colspan="2"
							class="text-center border border-gray-100 p-3 font-semibold uppercase"
						>
							{{ item.jenis }}
						</td>
					</tr>
					<tr class="text-sm">
						<template v-if="state.data[0]" v-for="item in state.data[0].biaya_lain">
							<td class="text-center border border-gray-100 p-3 font-semibold uppercase">
								Terbayar
							</td>
							<td class="text-center border border-gray-100 p-3 font-semibold uppercase">
								Kekurangan
							</td>
						</template>
					</tr>
					<tr class="h-3"></tr>
				</thead>
				<tbody>
					<template v-if="state.data.length" v-for="(item, i) in state.data">
						<tr class="border border-gray-100 transition hover:bg-blue-500 hover:text-white">
							<td class="p-3">{{ i + 1 }}</td>
							<td class="p-3">{{ item.nisn }}</td>
							<td class="p-3">{{ item.nama }}</td>
							<template v-for="bl in item.biaya_lain" :set>
								<td class="p-3">
									{{ formatMoney(bl.terbayar) }}
								</td>
								<td class="p-3">
									{{ formatMoney(bl.jumlah_bayar - bl.terbayar) }}
								</td>
							</template>
						</tr>
						<tr class="h-3"></tr>
					</template>
					<template v-else>
						<tr class="border border-gray-100">
							<td colspan="99" class="text-center p-3">Data tidak tersedia.</td>
						</tr>
						<tr class="h-3"></tr>
					</template>
				</tbody>
			</table>
		</div>

		<div class="p-4 bg-gray-50 rounded mb-4">
			<div>Total Terbayar: {{ formatMoney(state.total) }}</div>
			<div>Total Kekurangan: {{ formatMoney(state.total_kekuragan) }}</div>
		</div>
	</Card>
</template>
