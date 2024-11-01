<script setup lang="ts">
import Popper from "vue3-popper";

const props = withDefaults(
  defineProps<{
    text?: string;
    popperOptions?: any;
  }>(),
  {
    popperOptions: {},
  }
);

const combinedPopperOptions = {
  hover: true,
  placement: "top",
  offsetDistance: "0",
  ...props.popperOptions,
};
</script>
<template>
  <template v-if="!text && !$slots.text"><slot name="default"></slot></template>
  <Popper
    v-else
    v-bind="combinedPopperOptions"
    :content="text"
    class="whitespace-pre-wrap"
  >
    <span><slot name="default"></slot></span>
    <template #content v-if="$slots.text">
      <slot name="text"></slot>
    </template>
  </Popper>
</template>
