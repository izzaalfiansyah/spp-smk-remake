<script setup>
	import { onMounted, provide, ref } from 'vue';
	import Admin from './layouts/Admin.vue';
	import { auth, http } from './lib';
	import Login from './views/login.vue';

	const userAuth = ref({});

	async function Authenticate() {
		const res = await http.get('/user/' + auth.id).then((res) => res.json());
		userAuth.value = res;
	}

	provide('user-auth', { userAuth });

	onMounted(() => {
		Authenticate();
	});
</script>

<template>
	<Admin v-if="auth.id"></Admin>
	<Login v-else></Login>
</template>
