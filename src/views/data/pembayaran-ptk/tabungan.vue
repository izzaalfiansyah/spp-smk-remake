<script setup>
	import { computed, onMounted, reactive, watchEffect } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { auth, http, notify, formatMoney, formatDate } from '../../../lib';
	import Modal from '../../../components/Modal.vue';
	import Form, { rule } from '../../../components/Form.vue';

	const props = defineProps(['ptk']);

	const state = reactive({
		isMinus: false,
		data: {
			items: [],
		},
		saldo: 0,
		modal: {
			save: false,
			delete: false,
		},
		req: {
			id: '',
			ptk_id: props.ptk.id,
			user_id: auth.id,
			nominal: '',
		},
	});

	const total_saldo = computed(() => {
		const total =
			state.saldo +
			(state.isMinus ? 0 - state.req.nominal : state.req.nominal);

		return total;
	});

	const rules = computed(() => {
		let data = {
			ptk_id: rule.string().required(),
			user_id: rule.mixed().required(),
			nominal: state.isMinus
				? rule.number().min(1).max(state.saldo).required()
				: rule.number().min(1).required(),
		};

		return data;
	});

	const requests = computed(() => {
		let data = JSON.parse(JSON.stringify(state.req));
		if (state.isMinus) {
			data.nominal = 0 - state.req.nominal;
		}

		return data;
	});

	function nullable() {
		state.req = {
			ptk_id: props.ptk.id,
			user_id: auth.id,
			nominal: '',
		};
	}

	function get() {
		if (props.ptk.id) {
			http
				.get('/ptk/tabungan', {
					_ptk_id: props.ptk.id,
				})
				.then((res) => {
          state.saldo = 0;
					return res.json();
				})
				.then((res) => {
					state.data.items = res;

					res.forEach((item) => {
            state.saldo += item.nominal;
					});
				})
				.catch((err) => {
					notify(err, 'bg-red-400');
				});
		}
	}

	function save() {
		http
			.post('/ptk/tabungan', requests.value)
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
			.delete('/ptk/tabungan/' + state.req.id)
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
		if (props.ptk) {
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
			<div class="mb-2 font-semibold">SALDO PTK</div>
			<div>
				Total : {{ formatMoney(state.saldo) }}
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
				{{ item.nominal > 0 ? '1' : '2' }}
			</template>

			<template #kredit="{ item }">
				{{ item.nominal > 0 ? formatMoney(item.nominal) : '-' }}
			</template>

			<template #debit="{ item }">
				{{ item.nominal < 0 ? formatMoney(item.nominal) : '-' }}
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
				<div class="bg-gray-50 text-sm p-2 font-semibold mb-3">
					Saldo: {{ formatMoney(state.saldo) }}
				</div>

				<label for="">Jumlah Nominal</label>
				<input
					type="number"
					placeholder="Masukkan Jumlah Nominal"
					v-model="state.req.nominal"
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
