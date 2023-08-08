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
	<div class="fixed top-0 left-0 right-0 pointer-events-none" style="z-index: 9999999999">
		<div class="h-3px bg-blue-500 opacity-0" id="top-loading" style="width: 0%; transition: 500ms ease 0ms;"></div>
	</div>
	<Admin v-if="auth.id"></Admin>
	<Login v-else></Login>
</template>
