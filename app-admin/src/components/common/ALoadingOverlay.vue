<script setup lang="ts">
import ALoadingIcon from "@/components/common/ALoadingIcon.vue";
import { useVModel } from "@vueuse/core";
import AOverlay from "./AOverlay.vue";

const props = defineProps<{
  modelValue: boolean;
}>();

const emit = defineEmits(["update:modelValue"]);

const loading = useVModel(props, "modelValue", emit);
</script>

<template>
  <AOverlay persistent absolute v-model="loading">
    <ALoadingIcon size="w-10 h-10"></ALoadingIcon>
  </AOverlay>
</template>
