<script setup lang="ts">
import ALoadingIcon from "./ALoadingIcon.vue";

defineProps<{
  loading: boolean;
}>();
</script>

<template>
  <div
    v-if="loading"
    class="flex items-center justify-center space-x-2 px-4 py-4 text-gray-600"
  >
    <ALoadingIcon class="mr-2" size="w-4 h-4"></ALoadingIcon>
    <slot>Loading</slot>...
  </div>
</template>
