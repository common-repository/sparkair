<template>
  <span
    :class="`inline-flex items-center ${size} rounded-full font-medium bg-${color}-100 text-${color}-800`"
  >
    <span class="inline-block w-0 opacity-0">&nbsp;</span>
    <component
      :class="[large ? 'h-5 w-5' : 'h-4 w-4', '-ml-0.5 mr-1.5 inline']"
      v-if="prependIcon"
      :is="prependIcon"
    ></component>
    <slot></slot>
    <component
      :class="[large ? 'h-5 w-5' : 'h-4 w-4', '-mr-0.5 ml-1.5 inline']"
      v-if="appendIcon"
      :is="appendIcon"
    ></component>
  </span>
</template>

<script setup lang="ts">
const props = defineProps<{
  color: string;
  large?: boolean;
  prependIcon?: any;
  appendIcon?: any;
}>();

const size = props.large ? "px-3 py-1 text-sm" : "px-2.5 py-0.5 text-xs";
</script>
