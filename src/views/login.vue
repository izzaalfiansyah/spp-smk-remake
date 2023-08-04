<script setup>
	import { reactive } from 'vue';
	import Form, { rule } from '../components/Form.vue';
	import { http, notify } from '../lib';

	const state = reactive({
		req: {
			username: '',
			password: '',
		},
		showPassword: false,
	});

	const rules = {
		username: rule.string().required(),
		password: rule.string().required(),
	};

	function login() {
		http
			.post('/login', state.req)
			.then((res) => res.json())
			.then((res) => {
				if (res[0]) {
					notify(res, 'bg-red-400');
				} else {
					notify('berhasil login');
					state.req = {};

					localStorage.setItem('id', res.id);
					localStorage.setItem('role', res.role);

					setTimeout(() => {
						window.location.href = window.location.origin + window.location.pathname;
					}, 400);
				}
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}
</script>

<template>
	<div class="min-h-screen bg-[#006cea] flex">
		<div class="lg:w-1/2 min-h-screen w-full lg:flex items-center justify-center hidden animate-down">
			<img
				src="https://img.freepik.com/free-vector/mobile-login-concept-illustration_114360-83.jpg?w=2000"
				alt=""
				class="rounded w-500px block ripple"
			/>
		</div>
		<div
			class="lg:w-1/2 lg:bg-white min-h-screen w-full flex lg:items-center items-end justify-center animate-up"
		>
			<div class="bg-white rounded p-8 lg:py-8 py-16 rounded-t-3xl w-full max-w-500px">
				<div class="text-xl mb-6 border-b pb-3 font-semibold">Login</div>
				<Form @submit="login" class="form-field" :values="state.req" :rules="rules">
					<label for="">Username</label>
					<input type="text" placeholder="Masukkan Username" v-model="state.req.username" />
					<label for="">Password</label>
					<input
						:type="state.showPassword ? 'text' : 'password'"
						placeholder="Masukkan Password"
						v-model="state.req.password"
					/>
					<input type="checkbox" v-model="state.showPassword" /> Lihat Password

					<div class="mt-8">
						<button type="submit" class="w-full ripple">Login</button>
					</div>
				</Form>
			</div>
		</div>
	</div>
</template>

<style scoped>
	.animate-up {
		animation: animate-up .5s;
	}

	.animate-down {
		animation: animate-down .5s;
	}

	@keyframes animate-up {
		from {
			transform: translateY(100%);
		}
	}

	@keyframes animate-down {
		from {
			transform: translateY(-100%);
		}
	}
</style>