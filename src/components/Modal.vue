<script setup>
  import { ref } from 'vue';

  const props = defineProps(['modelValue']);
  const emits = defineEmits(['update:modelValue']);

  const el = ref(null);

  function handleClick(e) {
    if (e.path.indexOf(el.value) < 0) {
      emits('update:modelValue', false);
    }
  }
</script>

<template>
  <div class="fixed top-0 left-0 right-0 bottom-0 z-20 bg-black bg-opacity-25 p-3 flex justify-center items-center animate-none" v-if="props.modelValue" @click="handleClick">
    <div ref="el" class="max-h-full overflow-y-auto relative custom-modal">
      <button type="button" @click="emits('update:modelValue', false)" class="material-icons absolute top-0 right-0 mr-4 mt-4">close</button>
      <slot></slot>
    </div>
  </div>
</template>

<style>
  .custom-modal {
    animation: animate-custom-modal .5s;
  }

  @keyframes animate-custom-modal {
    from {
      transform: translateY(-100%);
    }
  }
</style>