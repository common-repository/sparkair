<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    inverted?: boolean;
  }>(),
  {
    inverted: false,
  }
);
</script>

<template>
  <div
    class="rounded-lg border shadow-sm focus:outline-none sm:rounded-md"
    :class="{
      'bg-white': !inverted,
      'bg-aprimary-700': inverted,
    }"
  >
    <slot></slot>
  </div>
</template>
