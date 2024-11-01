<template>
  <div
    class="flex items-center justify-between space-x-2 px-4 py-4 sm:px-6 sm:py-6"
  >
    <div class="overflow-hidden">
      <h3
        class="truncate font-semibold text-gray-900"
        :class="{ 'text-md': small, 'text-lg ': !small }"
      >
        <slot></slot>
      </h3>
      <h4 class="truncate text-sm font-medium text-gray-500">
        <slot name="subheader"></slot>
      </h4>
    </div>
    <div class="flex items-center space-x-2">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  small?: boolean;
}>();
</script>
