<script setup>
  const props = defineProps(['title']);
</script>

<template>
  <div class="bg-white rounded p-4 shadow">
    <div v-if="props.title" class="mb-3 pb-3 border-b text-xl">
      {{ props.title }}
    </div>
    <slot></slot>
  </div>
</template>