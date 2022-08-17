<script setup>
	import { ref } from 'vue';
	import * as yup from 'yup';

	const props = defineProps(['rules', 'values']);
	const emits = defineEmits(['submit']);

	const error = ref([]);
	const errorElement = ref();
	const form = ref();

	function handleSubmit() {
		const rules = props.rules || [];
		const schema = yup.object().shape(rules);

		schema
			.validate(props.values, { abortEarly: false })
			.then((res) => {
				error.value = [];
				emits('submit');
			})
			.catch(({ errors }) => {
				error.value = errors;
				setTimeout(() => {
					errorElement.value.scrollIntoView();
				}, 100);
			});
	}
</script>

<script>
	export const rule = yup;
</script>

<template>
	<form @submit.prevent="handleSubmit" ref="form">
		<div v-if="error.length" class="py-4 -mt-4" ref="errorElement">
			<div class="bg-red-100 border border-red-500 text-red-500 p-3 rounded text-sm">
				<table>
					<tr v-for="item in error">
						<td class="align-top font-semibold pr-2">-</td>
						<td>{{ item }}</td>
					</tr>
				</table>
			</div>
		</div>
		<slot></slot>
	</form>
</template>
