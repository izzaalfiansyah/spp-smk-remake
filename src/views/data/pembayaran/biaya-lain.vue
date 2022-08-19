<script setup>
	import { onMounted, reactive, watchEffect } from 'vue';
	import Card from '../../../components/Card.vue';
	import { http, notify, formatMoney, formatDate, auth, blankPage } from '../../../lib';
	import Table from '../../../components/Table.vue';
	import Modal from '../../../components/Modal.vue';
	import Loading from '../../../components/Loading.vue';

	const props = defineProps(['siswa']);
	const state = reactive({
		isLoading: false,
		isDelete: false,
		data: {
			items: [],
		},
		biaya_lain: [],
		terbayar: [],
		modal: {
			save: false,
		},
		req: {
			id: '',
			siswa_nisn: '',
			user_id: auth.id,
			biaya_lain_id: '',
			jumlah_bayar: '',
		},
		delete: {},
		item: {},
	});

	function getPembayaran() {
		http
			.get('/pembayaran/biaya-lain', {
				_siswa_nisn: props.siswa.nisn,
				_biaya_lain_id: state.req.biaya_lain_id,
			})
			.then((res) => res.json())
			.then((res) => {
				state.data.items = res;
			});
	}

	function get() {
		http
			.get('/pembayaran/biaya-lain', {
				_siswa_nisn: props.siswa.nisn,
			})
			.then((res) => res.json())
			.then((res) => {
				state.terbayar = [];
				res.forEach((item) => {
					if (state.terbayar[item.biaya_lain_id]) {
						state.terbayar[item.biaya_lain_id] += item.jumlah_bayar;
					} else {
						state.terbayar[item.biaya_lain_id] = item.jumlah_bayar;
					}
				});
				state.isLoading = false;
			});
	}

	function getData() {
		state.isLoading = true;
		if (props.siswa.nisn) {
			http
				.get('/biaya-lain', {
					_jurusan_kode: props.siswa.jurusan.kode,
					_kelas: props.siswa.kelas,
				})
				.then((res) => res.json())
				.then((res) => {
					state.biaya_lain = res;
					get();
				})
				.catch((err) => {
					notify(err);
				});
		}
	}

	function store(e = {}, fn = false) {
		if (fn) {
			fn();
		}

		http
			.post('/pembayaran/biaya-lain', state.req)
			.then((res) => res.json())
			.then((res) => {
				new Promise((resolve) => {
					notify('pembayaran berhasil disimpan');
					state.req.max_jumlah_bayar -= state.req.jumlah_bayar;
					state.req.jumlah_bayar = '';
					if (e) {
						e[0].value = '';
					}
					resolve(true);
				}).then(() => {
					getData();
					getPembayaran();

					blankPage(res.print_url);
				});
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	function destroy() {
		const item = state.delete;

		http
			.delete('/pembayaran/biaya-lain/' + item.id)
			.then((res) => res.json())
			.then((res) => {
				notify('pembayaran berhasil dihapus');
				state.req.max_jumlah_bayar += item.jumlah_bayar;
				state.req.jumlah_bayar = '';
				state.isDelete = false;
				getData();
				getPembayaran();
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	watchEffect(() => {
		if (props.siswa) getData();
	});
</script>

<template>
	<Card title="Biaya Lain">
		<div class="relative">
			<Loading v-if="state.isLoading"></Loading>
			<Table
				class="mt-3"
				:keys="{
					Pembayaran: 'jenis',
					Jumlah: 'jumlah_bayar',
					Diskon: 'diskon',
					Total_Bayar: 'total_bayar',
					Terbayar: 'terbayar',
					Belum_Terbayar: 'belum_terbayar',
					Bayar: 'bayar',
					Opsi: 'opsi',
				}"
				:items="state.biaya_lain"
			>
				<!-- <template #jenis="{ item }">
					{{ item.jenis }} - {{ item.created_at.slice(0, 4) }}
				</template> -->

				<template #jumlah_bayar="{ item }">
					{{ formatMoney(item.jumlah_bayar) }}
				</template>

				<template #diskon> {{ props.siswa.diskon_biaya_lain }} % </template>

				<template #total_bayar="{ item }">
					{{
						formatMoney(
							item.jumlah_bayar - (item.jumlah_bayar * props.siswa.diskon_biaya_lain) / 100,
						)
					}}
				</template>

				<template #terbayar="{ item }">
					{{ formatMoney(state.terbayar[item.id] ? state.terbayar[item.id] : 0) }}
				</template>

				<template #belum_terbayar="{ item }">
					{{
						formatMoney(
							item.jumlah_bayar -
								(item.jumlah_bayar * props.siswa.diskon_biaya_lain) / 100 -
								(state.terbayar[item.id] ? state.terbayar[item.id] : 0),
						)
					}}
				</template>

				<template #bayar="{ item }">
					<form
						@submit.prevent="
							store($event.currentTarget, () => {
								state.req.biaya_lain_id = item.id;
								state.req.siswa_nisn = props.siswa.nisn;
								state.req.jumlah_bayar = $event.currentTarget[0].value;
							})
						"
						class="inline"
					>
						<input
							type="number"
							:placeholder="
								formatMoney(
									item.jumlah_bayar -
										(item.jumlah_bayar * props.siswa.diskon_biaya_lain) / 100 -
										(state.terbayar[item.id] ? state.terbayar[item.id] : 0),
								)
							"
							min="1"
							:max="
								item.jumlah_bayar -
								(item.jumlah_bayar * props.siswa.diskon_biaya_lain) / 100 -
								(state.terbayar[item.id] ? state.terbayar[item.id] : 0)
							"
							:disabled="
								item.jumlah_bayar -
									(item.jumlah_bayar * props.siswa.diskon_biaya_lain) / 100 -
									(state.terbayar[item.id] ? state.terbayar[item.id] : 0) <=
								0
							"
							required
							class="h-10 rounded border-gray-100 text-gray-500 shadow outline-none w-120px disabled:bg-gray-100 focus:invalid:(ring-2 ring-red-500)"
						/>
						<button type="submit" class="hidden"></button>
					</form>
				</template>

				<template #opsi="{ item }">
					<div class="bg-white rounded inline-block px-1">
						<button
							@click="
								state.item = JSON.parse(JSON.stringify(item));
								state.modal.save = true;

								state.req.jumlah_bayar = '';
								state.req.biaya_lain_id = item.id;
								state.req.siswa_nisn = props.siswa.nisn;
								state.req.max_jumlah_bayar =
									item.jumlah_bayar -
									(item.jumlah_bayar * props.siswa.diskon_biaya_lain) / 100 -
									(state.terbayar[item.id] ? state.terbayar[item.id] : 0);
								state.data.items = [];
								getPembayaran();
							"
							class="material-icons-outlined text-blue-500 !text-xl"
						>
							edit
						</button>
					</div>
				</template>
			</Table>
		</div>
	</Card>

	<Modal v-model="state.modal.save">
		<Card class="w-900px max-w-full" title="Data Pembayaran">
			<div class="mt-4">
				<div class="flex mb-4 pb-4 border-b items-center justify-center">
					<form @submit.prevent="store" class="rounded shadow w-full" lg="w-1/2">
						<div class="rounded-t p-4 bg-gray-100">
							<div class="form-field">
								<div class="oveflow-x-auto bg-white mb-4 rounded p-2 relative">
									<div
										class="absolute bottom-5 px-4 right-5 transform p-1 -rotate-12 border-2 border-purple-500 text-purple-500 rounded"
										v-if="state.req.max_jumlah_bayar <= 0"
									>
										LUNAS
									</div>
									<table>
										<tr>
											<td>Tanggungan</td>
											<td class="px-2">:</td>
											<td>{{ state.item.jenis }}</td>
										</tr>
										<tr>
											<td>Terbayar</td>
											<td class="px-2">:</td>
											<td class="text-green-500">
												{{
													formatMoney(
														state.terbayar[state.item.id] ? state.terbayar[state.item.id] : 0,
													)
												}}
											</td>
										</tr>
										<tr>
											<td>Kekurangan</td>
											<td class="px-2">:</td>
											<td class="text-red-500">{{ formatMoney(state.req.max_jumlah_bayar) }}</td>
										</tr>
									</table>
								</div>
								<label for="">Nominal</label>
								<input
									type="number"
									v-model="state.req.jumlah_bayar"
									placeholder="Masukkan Nominal"
									min="1"
									:max="state.req.max_jumlah_bayar"
									:disabled="state.req.max_jumlah_bayar <= 0"
									required
								/>
							</div>
						</div>
						<div class="rounded-b text-center py-1 px-4 text-blue-500">
							<button type="submit">+ TAMBAH</button>
						</div>
					</form>
				</div>
				<div
					class="bg-red-100 mb-4 border rounded border-red-500 text-red-500 p-3 form-field"
					v-if="state.isDelete"
				>
					<div class="lg:flex">
						<div class="w-full flex items-center">Anda yakin menghapus data terpilih?</div>
						<div class="w-full lg:text-right text-center lg:mt-0 mt-4">
							<button @click="state.isDelete = false" class="!bg-gray-500 mr-2">Batal</button>
							<button @click="destroy" class="!bg-red-500">Hapus</button>
						</div>
					</div>
				</div>
				<div class="flex flex-nowrap overflow-x-auto pb-3 gap-3" v-if="state.data.items.length">
					<div v-for="item in state.data.items" class="rounded shadow">
						<div class="rounded-t p-4 py-6 bg-blue-500 text-white lg:w-350px w-280px">
							<div text="2xl center" class="mb-3 border-b pb-3 border-white">
								{{ formatMoney(item.jumlah_bayar) }}
							</div>
							<div class="flex text-sm">
								<div class="flex-1">{{ item.user.nama }}</div>
								<div class="flex-1 text-right">{{ formatDate(item.created_at) }}</div>
							</div>
						</div>
						<div class="rounded-b p-1 text-center flex justify-between px-4 items-center h-10">
							<a :href="item.print_url" target="_blank" class="mr-2 text-sm">PRINT</a>
							<button
								@click="
									state.isDelete = true;
									state.delete = item;
								"
								class="material-icons-outlined text-red-500 !text-xl"
								v-if="item.user_id == auth.id ? true : auth.role == '1' ? true : false"
							>
								delete
							</button>
						</div>
					</div>
				</div>
				<div v-else>
					<div class="p-4 border border-gray-100 text-center">Data tidak tersedia.</div>
				</div>
			</div>
		</Card>
	</Modal>
</template>
