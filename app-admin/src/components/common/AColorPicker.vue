<script setup lang="ts">
import { useVModel } from "@vueuse/core";
import { ColorPicker } from "vue-color-kit";
import "vue-color-kit/dist/vue-color-kit.css";
import ATextField from "./ATextField.vue";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import {
  EyeDropperIcon,
  PaintBrushIcon,
  StopIcon,
} from "@heroicons/vue/20/solid";

const props = withDefaults(
  defineProps<{
    modelValue?: string | null;
    disabled?: boolean;
    label: string;
  }>(),
  {
    disabled: false,
  }
);
const emit = defineEmits(["update:modelValue", "close"]);

const value = useVModel(props, "modelValue", emit, { defaultValue: null });

const changeColor = (color: any) => {
  value.value = color.hex;
};
</script>

<template>
  <ATextField
    v-model="value"
    :label="label"
    type="text"
    :show-button="!disabled"
    :show-prefix="!!value"
    :closeable="!disabled"
    @close="value = null"
    placeholder="default color..."
    readonly
  >
    <template #prefix v-if="value">
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
      >
        <StopIcon
          class="h-5 w-5 text-gray-400"
          aria-hidden="true"
          :style="{
            color: String(value),
          }"
        />
      </div>
    </template>
    <template #append v-if="!disabled">
      <Popover class="relative">
        <PopoverButton
          class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md border-l-2 px-3 py-2.5 text-sm font-semibold"
        >
          <EyeDropperIcon
            class="-ml-0.5 h-5 w-5 text-gray-400"
            aria-hidden="true"
          />
          Pick</PopoverButton
        >
        <PopoverPanel class="absolute z-50">
          <ColorPicker
            class="box-content"
            theme="light"
            @changeColor="($event: any) => changeColor($event)"
          ></ColorPicker>
        </PopoverPanel>
      </Popover>
    </template>
  </ATextField>
</template>
