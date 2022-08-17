<script setup>
	import { onMounted, onUnmounted, ref, watch, watchEffect } from 'vue';

	const props = defineProps(['items', 'modelValue']);
	const emits = defineEmits(['update:modelValue']);

	const open = ref(false);
	const text = ref('');
	const el = ref(null);
	const data = ref(props.items);

	function handleSelect(item) {
		emits('update:modelValue', item.value);
		text.value = item.text;
		open.value = false;
	}

	function handleClick(e) {
		if (e.path.indexOf(el.value) < 0) {
			open.value = false;
		}

		let valid = false;

		props.items.forEach((item) => {
			if (item.text == text.value) {
				valid = true;
			}
		});

		if (!valid) {
			emits('update:modelValue', '');
			text.value = '';
		}
	}

	function handleFocus(e) {
		open.value = true;
		e.target.select();
	}

	onMounted(() => {
		window.addEventListener('click', handleClick);
	});

	onUnmounted(() => {
		window.removeEventListener('click', handleClick);
	});

	watch(text, (val) => {
		data.value = props.items.filter(
			(item) => item.text.toLowerCase().indexOf(val.toLowerCase()) >= 0,
		);
	});

	watchEffect(() => {
		if (props.modelValue) {
			props.items.forEach((item) => {
				if (item.value == props.modelValue) {
					text.value = item.text;
				}
			});
		} else {
			text.value = '';
		}

		if (props.items) {
			data.value = props.items;
		}
	});
</script>

<script>
	export default {
		inheritAttrs: false,
	};
</script>

<template>
	<div class="relative" ref="el">
		<input type="text" v-bind="$attrs" @focus="handleFocus" v-model="text" />
		<div
			class="bg-white shadow absolute bottom-2 left-0 z-10 right-0 transform translate-y-full rounded py-1"
			:class="{ hidden: !open }"
		>
			<div class="overflow-y-auto max-h-200px">
				<div
					v-for="item in data.slice(0, 10)"
					class="hover:bg-blue-400 hover:text-white p-1.5 px-2 cursor-pointer transition"
					@click="handleSelect(item)"
				>
					<slot :item="item">{{ item.text }}</slot>
				</div>
			</div>
		</div>
	</div>
</template>
