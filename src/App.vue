<script setup>
	import Admin from './layouts/Admin.vue';
	import { auth } from './lib';
	import Login from './views/login.vue';
</script>

<template>
	<Admin v-if="auth.id"></Admin>
	<Login v-else></Login>
</template>
