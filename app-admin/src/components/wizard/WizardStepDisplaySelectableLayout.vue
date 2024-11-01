<script setup lang="ts">
import { useVModel } from "@vueuse/core";

const props = defineProps<{
  modelValue: string[];
  hook: string;
}>();
const emit = defineEmits(["update:modelValue"]);

const selected = useVModel(props, "modelValue", emit);
</script>

<template>
  <div
    :class="[
      selected.includes(hook) ? 'bg-aprimary-500' : 'bg-aprimary-200',
      'h-full w-full rounded-sm',
    ]"
  ></div>
</template>
