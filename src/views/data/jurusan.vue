<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import { rule } from '../../components/Form.vue';
	import Modal from '../../components/Modal.vue';
	import { http, notify, formatMoney } from '../../lib';
	import Form from '../../components/Form.vue';

	const state = reactive({
		isEdit: false,
		modal: {
			save: false,
			delete: false,
		},
		data: {
			items: [],
		},
		req: {
			kode: '',
			nama: '',
			kategori: '',
			jumlah_spp: 0,
			uang_praktik: 0,
			tabungan_wajib: 0,
		},
	});

	const rules = {
		kode: rule.string().required(),
		nama: rule.string().required(),
		kategori: rule.string().required(),
		jumlah_spp: rule.number().required(),
	};

	function nullable() {
		state.req = {
			kode: '',
			nama: '',
			kategori: '',
			jumlah_spp: 0,
			uang_praktik: 0,
			tabungan_wajib: 0,
		};
	}

	function get() {
		nullable();
		http
			.get('/jurusan')
			.then((res) => res.json())
			.then((res) => (state.data.items = res))
			.catch((err) => {
				notify(err, 'bg-red-400');
			});
	}

	function save() {
		state.isEdit
			? http
					.put('/jurusan/' + state.req.old_kode, state.req)
					.then((res) => res.json())
					.then((res) => {
						notify('data berhasil diedit');
						state.modal.save = false;
						get();
					})
					.catch((err) => notify(err, 'bg-red-400'))
			: http
					.post('/jurusan', state.req)
					.then((res) => res.json())
					.then((res) => {
						notify('data berhasil ditambah');
						state.modal.save = false;
						get();
					})
					.catch((err) => notify(err, 'bg-red-400'));
	}

	function destroy() {
		http
			.delete('/jurusan/' + state.req.old_kode)
			.then((res) => res.json())
			.then((res) => {
				notify('data berhasil dihapus');
				state.modal.delete = false;
				get();
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	function handleChangeCategory() {
		state.req.jumlah_spp = 110000;
		state.req.tabungan_wajib = 20000;

		if (state.req.kategori == '1') {
			state.req.uang_praktik = 0;
		} else if (state.req.kategori == '2') {
			state.req.uang_praktik = 10000;
		}
	}

	onMounted(() => {
		get();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data Jurusan</div>

	<Card class="mb-4">
		<div class="form-field">
			<button
				@click="
					state.isEdit = false;
					nullable();
					state.modal.save = true;
				"
				class="mr-2"
			>
				+ Tambah
			</button>
		</div>
	</Card>
	<Card class="mb-4">
		<div class="grid lg:grid-cols-5 grid-cols-2 gap-3" v-if="state.data.items.length">
			<div class="border group border-gray-100 relative" v-for="item in state.data.items">
				<div class="absolute -top-1 right-1 bg-white shadow rounded-b px-2 text-sm z-3 uppercase">
					{{ item.kategori == '1' ? 'Bisnis' : 'Teknik' }}
				</div>
				<div
					class="text-2xl transform group-hover:bg-blue-500 bg-gray-50 group-hover:text-white transition text-blue-500 font-semibold text-center py-10"
				>
					<span
						style="
							font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande',
								'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
						"
						class="text-shadow"
					>
						{{ item.kode }}
					</span>
				</div>
				<div class="p-2 text-center">
					<div class="border-b mb-2 pb-1 text-xs font-semibold">
						SPP: {{ formatMoney(parseInt(item.jumlah_spp) + parseInt(item.tabungan_wajib) + parseInt(item.uang_praktik)) }}
					</div>
					<button
						@click="
							state.modal.save = true;
							state.isEdit = true;
							state.req = JSON.parse(JSON.stringify(item));
							state.req.old_kode = state.req.kode;
						"
						class="material-icons-outlined text-blue-500 !text-xl"
					>
						edit
					</button>
					<button
						@click="
							state.modal.delete = true;
							state.req = JSON.parse(JSON.stringify(item));
							state.req.old_kode = state.req.kode;
						"
						class="material-icons-outlined text-red-500 !text-xl"
					>
						delete
					</button>
				</div>
			</div>
		</div>
		<div v-else class="p-3 border border-gray-100 text-center">Data tidak tersedia.</div>
	</Card>

	<Modal v-model="state.modal.save">
		<Card :title="state.isEdit ? 'Edit Jurusan' : 'Tambah Jurusan'" class="w-550px max-w-full">
			<Form class="form-field" :values="state.req" :rules="rules" @submit="save">
				<label for="">Kode</label>
				<input type="text" placeholder="Masukkkan Kode" v-model="state.req.kode" />
				<label for="">Nama</label>
				<input type="text" placeholder="Masukkan Nama" v-model="state.req.nama" />
				<label for="">Kategori</label>
				<select v-model="state.req.kategori" v-on:change="handleChangeCategory">
					<option disabled value="">Pilih Kategori</option>
					<option value="1">Bisnis</option>
					<option value="2">Teknik</option>
				</select>
				<label for="">Jumlah SPP</label>
				<input type="number" placeholder="Masukkan Jumlah SPP" v-model="state.req.jumlah_spp" />
				<label for="">Uang Praktik</label>
				<input type="number" placeholder="Masukkan Uang Praktik" v-model="state.req.uang_praktik" />
				<label for="">Tabungan Wajib</label>
				<input type="number" placeholder="Tabungan Wajib" v-model="state.req.tabungan_wajib" />

				<div class="text-right mt-5">
					<button>Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus Jurusan" class="w-550px max-w-full">
			<form class="form-field" @submit.prevent="destroy">
				<p>
					Anda yakin menghapus jurusan <strong>{{ state.req.kode }} ({{ state.req.nama }})</strong>?
				</p>

				<div class="text-right mt-5">
					<button class="!bg-red-500">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
