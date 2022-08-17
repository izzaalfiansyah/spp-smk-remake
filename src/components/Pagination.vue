<script setup>
	import { ref, watchEffect } from 'vue';

	const props = defineProps(['totalCount', 'modelValue', 'limit']);
	const emits = defineEmits(['update:modelValue']);

	const totalPage = ref(0);

	watchEffect(() => {
		if (props.totalCount || props.limit) {
			totalPage.value = Math.ceil(props.totalCount / props.limit);
		}
	});
</script>

<template>
	<button
		type="button"
		:disabled="props.modelValue <= 1"
    @click="emits('update:modelValue', props.modelValue - 1)"
		class="material-icons disabled:text-gray-300"
	>
		chevron_left
	</button>
	<span class="mx-2"> {{ props.modelValue }} - {{ totalPage }} <span class="mx-2">|</span> {{ totalCount }} </span>
	<button
		type="button"
		:disabled="props.modelValue >= totalPage"
    @click="emits('update:modelValue', props.modelValue + 1)"
		class="material-icons disabled:text-gray-300"
	>
		chevron_right
	</button>
</template>
