<script setup>
	import { reactive, watchEffect } from 'vue';
	import Card from '../../../components/Card.vue';
	import { http, notify, auth, formatDate } from '../../../lib';
	import Loading from '../../../components/Loading.vue';

	const props = defineProps(['siswa']);

	const state = reactive({
		req: [],
		item: [],
		isLoading: false,
		totalKekurangan: 12 * 3,
		kelas: ['XII', 'XI', 'X'],
	});

	function get() {
		state.isLoading = true;
		if (props.siswa.nisn)
			http
				.get('/pembayaran/spp', {
					_siswa_nisn: props.siswa.nisn,
				})
				.then((res) => res.json())
				.then((res) => {
					state.req = res.map((item) =>
						JSON.stringify({
							bulan: item.bulan,
							status_kelas: item.status_kelas,
						}),
					);

					state.item = [];
					state.totalKekurangan = 12 * ([...state.kelas].reverse().indexOf(props.siswa.kelas) + 1);
					res.forEach((item) => {
						state.totalKekurangan -= 1;
						state.item[item.status_kelas + '-' + item.bulan] = item;
					});

					state.isLoading = false;
				});
	}

	function handleChange(el) {
		state.isLoading = true;
		if (el.checked) {
			const jumlah_bayar =
				props.siswa.jurusan.jumlah_spp -
				(props.siswa.jurusan.jumlah_spp * props.siswa.diskon_spp) / 100 +
				(props.siswa.jurusan.kategori == '2'
					? props.siswa.jurusan.diskon_spp > 50
						? 10000
						: 0
					: 0);

			const kelas = el.dataset.kelas;
			const bulan = el.dataset.bulan;

			http
				.post('/pembayaran/spp', {
					siswa_nisn: props.siswa.nisn,
					user_id: auth.id,
					bulan: bulan,
					jumlah_bayar: jumlah_bayar,
					tabungan_wajib: props.siswa.jurusan.tabungan_wajib,
					status_kelas: kelas,
				})
				.then((res) => res.json())
				.then((res) => {
					notify('pembayaran berhasil disimpan');
					get();
				})
				.catch((err) => {
					notify(err, 'bg-red-400');
				});
		} else {
			const id = el.dataset.id;

			http
				.delete('/pembayaran/spp/' + id)
				.then((res) => res.json())
				.then(() => {
					notify('pembayaran berhasil dihapus');
					get();
				})
				.catch((err) => notify(err, 'bg-red-400'));
		}
	}

	function insertBatch(e) {
		state.isLoading = true;

		const input = e.target[0];
		const totalInsert = input.value;
		const jumlah_bayar =
			props.siswa.jurusan.jumlah_spp -
			(props.siswa.jurusan.jumlah_spp * props.siswa.diskon_spp) / 100 +
			(props.siswa.jurusan.kategori == '2' ? (props.siswa.jurusan.diskon_spp > 50 ? 10000 : 0) : 0);

		let noData = [];
		[...state.kelas].reverse().forEach((kelas) => {
			bulan.forEach((bulan) => {
				if (!state.item[kelas + '-' + bulan]) {
					noData = [
						...noData,
						{
							siswa_nisn: props.siswa.nisn,
							user_id: auth.id,
							bulan: bulan,
							jumlah_bayar: jumlah_bayar,
							tabungan_wajib: props.siswa.jurusan.tabungan_wajib,
							status_kelas: kelas,
						},
					];
				}
			});
		});

		const data = noData.slice(0, totalInsert);

		http
			.post('/pembayaran/spp/batch', { data })
			.then((res) => res.json())
			.then((res) => {
				input.value = '';
				notify(res);
				get();
			})
			.catch((err) => notify(err))
			.finally(() => {
				state.isLoading = false;
			});
	}

	watchEffect(() => {
		if (props.siswa) get();
	});
</script>

<script>
	export const bulan = [
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember',
		'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
	];
</script>

<template>
	<Card title="SPP">
		<div class="relative">
			<div class="p-4 bg-gray-100 rounded">
				<form class="relative" @submit.prevent="insertBatch">
					<input
						type="number"
						class="w-full rounded-full p-3 border border-gray-100 outline-none shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 focus:invalid:border-red-400 focus:invalid:ring-red-300"
						:placeholder="'Bayar Langsung 1 - ' + state.totalKekurangan + ' Bulan'"
						:max="state.totalKekurangan"
						min="1"
						required
					/>
					<button
						type="submit"
						class="absolute top-0 bottom-0 right-0 rounded-full p-3 bg-blue-500 px-5 shadow-sm text-white"
					>
						Bayar
					</button>
				</form>
			</div>
			<Loading v-if="state.isLoading"></Loading>
			<div v-for="kelas in state.kelas.slice(state.kelas.indexOf(props.siswa.kelas))">
				<div class="my-3 text-lg">Kelas {{ kelas }}</div>
				<div class="grid grid-flow-col lg:grid-rows-4 grid-rows-6 grid-rows-12 gap-3">
					<div
						v-for="item in bulan"
						class="p-2 relative border border-gray-100 form-field transition hover:bg-gray-50"
						:class="{ 'bg-gray-50': state.item[kelas + '-' + item] ? true : false }"
					>
						<table>
							<tr>
								<td class="pr-2">
									<input
										class="mb-1"
										type="checkbox"
										@change="handleChange($event.currentTarget)"
										v-model="state.req"
										:value="
											JSON.stringify({
												bulan: item,
												status_kelas: kelas,
											})
										"
										:data-id="
											state.item[kelas + '-' + item] ? state.item[kelas + '-' + item].id : ''
										"
										:data-kelas="kelas"
										:data-bulan="item"
										:disabled="
											state.item[kelas + '-' + item] ? (auth.role == '1' ? false : true) : false
										"
									/>
								</td>
								<td>
									{{ item }}
								</td>
							</tr>
						</table>
						<div
							v-if="state.item[kelas + '-' + item]"
							class="absolute bottom-0 right-0 text-[10px] text-white bg-green-500 shadow p-0.5 px-2 rounded-tl"
						>
							{{ state.item[kelas + '-' + item].user.nama }},
							{{ formatDate(state.item[kelas + '-' + item].created_at.slice(0, 10)) }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</Card>
</template>
