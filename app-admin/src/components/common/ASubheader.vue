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
  <span
    class="block pt-4 text-xs uppercase"
    :class="{
      'text-slate-500': !inverted,
      'text-white/80': inverted,
    }"
  >
    <slot></slot>
  </span>
</template>
