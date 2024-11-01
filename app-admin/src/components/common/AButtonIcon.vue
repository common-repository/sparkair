<template>
  <button
    type="button"
    class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-aprimary-500 focus:ring-offset-2"
  >
    <component :is="icon" class="h-6 w-6" aria-hidden="true" />
  </button>
</template>

<script setup lang="ts">
defineProps<{
  icon: any;
}>();
</script>
