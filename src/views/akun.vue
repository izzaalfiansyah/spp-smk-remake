<script setup="">
	import { onMounted, reactive } from 'vue';
	import Card from '../components/Card.vue';
	import Form, { rule } from '../components/Form.vue';
	import { auth, http, notify } from '../lib';

	const state = reactive({
		req: {
			id: '',
			username: '',
			password: '',
			nama: '',
			role: '',
		},
	});

	const rules = {
		username: rule.string().min(3).required(),
		password: rule.string().optional(),
		nama: rule.string().required(),
		role: rule.string().required(),
	};

	function nullable() {
		state.req = {
			id: '',
			username: '',
			password: '',
			nama: '',
			role: '',
		};
	}

	function get() {
		nullable();
		http
			.get('/user/' + auth.id)
			.then((res) => res.json())
			.then((res) => {
				state.req = res;
        state.req.password = '';
			})
			.catch((err) => {
				notify(err, 'bg-red-400');
			});
	}

	function update() {
		http
			.put('/user/' + state.req.id, state.req)
			.then((res) => res.json())
			.then((res) => {
				notify('data berhasil diedit');
				get();
			})
			.catch((res) => {
				notify(res, 'bg-red-400');
			});
	}

	onMounted(() => {
		get();
	});
</script>

<template>
	<Card title="Akun User">
		<Form class="form-field" @submit="update" :values="state.req" :rules="rules">
			<label for="">Username</label>
			<input type="text" placeholder="Masukkan Username" v-model="state.req.username" />

			<label for="">Password</label>
			<input type="password" placeholder="Masukkan Password" v-model="state.req.password" />
			<div v-if="state.req.id" class="hint mb-3">Kosongkan jika tidak ingin mengganti password</div>

			<label for="">Nama</label>
			<input type="text" placeholder="Masukkan Nama" v-model="state.req.nama" />

			<label for="">Role</label>
			<input type="text" placeholder="Masukkan Role" v-model="state.req.role_detail" disabled />

			<div class="text-right mt-5">
				<button type="submit">Simpan</button>
			</div>
		</Form>
	</Card>
</template>
