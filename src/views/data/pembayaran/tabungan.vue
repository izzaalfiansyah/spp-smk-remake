<script setup>
	import { computed, onMounted, reactive, watchEffect } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { auth, http, notify, formatMoney, formatDate } from '../../../lib';
	import Modal from '../../../components/Modal.vue';
	import Form, { rule } from '../../../components/Form.vue';

	const props = defineProps(['siswa']);

	const state = reactive({
		isMinus: false,
		data: {
			items: [],
		},
		saldo: {
			tabungan_wajib: 0,
			tabungan_pribadi: 0,
		},
		modal: {
			save: false,
			delete: false,
		},
		req: {
			id: '',
			siswa_nisn: props.siswa.nisn,
			user_id: auth.id,
			jumlah_bayar: '',
			status_tabungan: '2',
		},
	});

	const tabungan_pilihan = computed(() => {
		return state.req.status_tabungan == '1'
			? state.saldo.tabungan_wajib
			: state.saldo.tabungan_pribadi;
	});

	const total_saldo = computed(() => {
		const total =
			tabungan_pilihan.value +
			(state.isMinus ? 0 - state.req.jumlah_bayar : state.req.jumlah_bayar);

		return total;
	});

	const rules = computed(() => {
		let data = {
			siswa_nisn: rule.string().required(),
			user_id: rule.mixed().required(),
			jumlah_bayar: state.isMinus
				? rule.number().min(1).max(tabungan_pilihan.value).required()
				: rule.number().min(1).required(),
			status_tabungan: rule.string().oneOf(['1', '2']).required(),
		};

		return data;
	});

	const requests = computed(() => {
		let data = JSON.parse(JSON.stringify(state.req));
		if (state.isMinus) {
			data.jumlah_bayar = 0 - state.req.jumlah_bayar;
		}

		return data;
	});

	function nullable() {
		state.req = {
			siswa_nisn: props.siswa.nisn,
			user_id: auth.id,
			jumlah_bayar: '',
			status_tabungan: '2',
		};
	}

	function get() {
		if (props.siswa.nisn) {
			http
				.get('/pembayaran/tabungan', {
					_siswa_nisn: props.siswa.nisn,
				})
				.then((res) => {
					state.saldo.tabungan_pribadi = state.saldo.tabungan_wajib = 0;
					state.saldo.tabungan_wajib = parseInt(res.headers.get('X-Tabungan-Wajib'));
					return res.json();
				})
				.then((res) => {
					state.data.items = res;

					res.forEach((item) => {
						if (item.status_tabungan == '1') {
							state.saldo.tabungan_wajib += item.jumlah_bayar;
						} else {
							state.saldo.tabungan_pribadi += item.jumlah_bayar;
						}
					});
				})
				.catch((err) => {
					notify(err, 'bg-red-400');
				});
		}
	}

	function save() {
		http
			.post('/pembayaran/tabungan', requests.value)
			.then((res) => res.json())
			.then((res) => {
				state.modal.save = false;
				notify('data berhasil disimpan');
				get();
			})
			.catch((err) => {
				notify(err, 'bg-red-400');
			});
	}

	function destroy() {
		http
			.delete('/pembayaran/tabungan/' + state.req.id)
			.then((res) => res.json())
			.then((res) => {
				state.modal.delete = false;
				notify('data berhasil dihapus');
				get();
			})
			.catch((err) => {
				notify(err, 'bg-red-400');
			});
	}

	watchEffect(() => {
		if (props.siswa) {
			get();
		}
	});
</script>

<template>
	<Card title="Tabungan">
		<div class="mb-4 form-field flex justify-center">
			<button
				@click="
					state.modal.save = true;
					state.isMinus = false;
					nullable();
				"
				class="mr-2"
			>
				Tambah
			</button>
			<button
				@click="
					state.modal.save = true;
					state.isMinus = true;
					nullable();
				"
				class="!bg-red-500"
			>
				Ambil
			</button>
		</div>
		<div
			class="border-white border-2 p-2 text-center border-dashed bg-blue-500 text-sm mb-4 rounded text-white"
		>
			<div class="mb-2 font-semibold">SALDO TABUNGAN</div>
			<div>Tabungan Pribadi : {{ formatMoney(state.saldo.tabungan_pribadi) }}</div>
			<div>Tabungan Wajib : {{ formatMoney(state.saldo.tabungan_wajib) }}</div>
			<div>
				Total : {{ formatMoney(state.saldo.tabungan_pribadi + state.saldo.tabungan_wajib) }}
			</div>
		</div>
		<Table
			:keys="{
				Tanggal: 'tanggal',
				Status: 'status',
				Kredit: 'kredit',
				Debit: 'debit',
				Operator: 'operator',
				Opsi: 'opsi',
			}"
			:items="state.data.items"
		>
			<template #tanggal="{ item }">
				{{ formatDate(item.created_at) }}
			</template>

			<template #status="{ item }">
				{{ item.jumlah_bayar > 0 ? '1' : '0' }}
			</template>

			<template #kredit="{ item }">
				{{ item.jumlah_bayar > 0 ? formatMoney(item.jumlah_bayar) : '-' }}
			</template>

			<template #debit="{ item }">
				{{ item.jumlah_bayar < 0 ? formatMoney(item.jumlah_bayar) : '-' }}
			</template>

			<template #operator="{ item }">
				{{ item.user.nama }}
			</template>

			<template #opsi="{ item }">
				<div
					class="bg-white rounded inline-block px-1"
					v-if="item.user_id == auth.id ? true : auth.role == '1' ? true : false"
				>
					<button
						@click="
							state.req = JSON.parse(JSON.stringify(item));
							state.modal.delete = true;
						"
						class="material-icons-outlined text-red-500 !text-xl"
					>
						delete
					</button>
				</div>
				<span v-else>-</span>
			</template>
		</Table>
	</Card>

	<Modal v-model="state.modal.save">
		<Card :title="state.isMinus ? 'Ambil Tabungan' : 'Tambah Tabungan'" class="w-500px max-w-full">
			<Form @submit="save" class="form-field" :values="state.req" :rules="rules">
				<div>
					<label for="">Pilih Tabungan</label>
					<select v-model="state.req.status_tabungan">
						<option value="1">Tabungan Wajib</option>
						<option value="2">Tabungan Pribadi</option>
					</select>
				</div>

				<div class="bg-gray-50 text-sm p-2 font-semibold mb-3">
					Saldo: {{ formatMoney(tabungan_pilihan) }}
				</div>

				<label for="">Jumlah Transaksi</label>
				<input
					type="number"
					placeholder="Masukkan Jumlah Transaksi"
					v-model="state.req.jumlah_bayar"
				/>

				<div class="bg-gray-50 text-sm p-2 font-semibold mb-3">
					Perubahan Saldo: {{ formatMoney(total_saldo) }}
				</div>

				<div class="mt-5 text-right">
					<button type="submit">Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus Tabungan" class="max-w-full w-550px">
			<form @submit.prevent="destroy" class="form-field">
				<p>Anda yakin akan menghapus data terpilih?</p>

				<div class="mt-5 text-right">
					<button class="!bg-red-500" type="submit">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
