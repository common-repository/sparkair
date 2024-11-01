<script setup lang="ts">
import { useVModel } from "@vueuse/core";
import ATooltip from "./ATooltip.vue";
import AInputWrapper from "./AInputWrapper.vue";

interface ButtonType {
  value: string | number | boolean;
  text: string;
  tooltip?: string;
}

const props = defineProps<{
  modelValue: any;
  buttons: ButtonType[];
  label?: string;
  readonly?: boolean;
  loading?: boolean;
  hint?: string;
}>();

const emit = defineEmits(["update:modelValue"]);
const selectedValue = useVModel(props, "modelValue", emit);
</script>

<template>
  <AInputWrapper
    :label="label"
    :readonly="readonly"
    :loading="loading"
    :hint="hint"
  >
    <template #wrapper>
      <span class="isolate inline-flex rounded-md shadow-sm">
        <button
          v-for="(button, idx) in buttons"
          :key="idx"
          type="button"
          class="relative inline-flex items-center px-3 py-2 text-sm font-semibold leading-6 ring-1 ring-inset ring-gray-300 focus:z-10"
          :class="{
            'cursor-not-allowed opacity-40': readonly || loading,
            'hover:bg-aprimary-700':
              !readonly && !loading && button.value === selectedValue,
            'hover:bg-gray-50':
              !readonly && !loading && button.value !== selectedValue,
            'rounded-l-md': idx === 0,
            '-ml-px ': idx > 0 && idx < buttons.length - 1,
            '-ml-px rounded-r-md': idx === buttons.length - 1,
            'bg-aprimary-600 text-white ': button.value === selectedValue,
            'bg-white text-gray-900 ': button.value !== selectedValue,
          }"
          @click="selectedValue = button.value"
          :disabled="readonly || loading"
        >
          <ATooltip v-if="button.tooltip" :text="button.tooltip">
            {{ button.text }}
          </ATooltip>
          <template v-else>{{ button.text }} </template>
        </button>
      </span>
    </template>
  </AInputWrapper>
</template>
