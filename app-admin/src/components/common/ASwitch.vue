<template>
  <div>
    <SwitchGroup :as="as" class="flex items-center">
      <Switch
        :model-value="getValue"
        @update:model-value="setValue"
        :class="[
          getValue ? 'bg-aprimary-600' : 'bg-gray-200',
          disabled
            ? ' cursor-not-allowed opacity-40'
            : 'cursor-pointer opacity-100',
          'relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-aprimary-500 focus:ring-offset-2',
        ]"
        :disabled="disabled"
        :readonly="disabled"
      >
        <span
          aria-hidden="true"
          :class="[
            getValue ? 'translate-x-5' : 'translate-x-0',
            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
          ]"
        >
        </span>
      </Switch>
      <SwitchLabel v-if="label" as="span" class="ml-3">
        <span
          :class="[
            disabled
              ? ' cursor-not-allowed opacity-40'
              : 'cursor-pointer opacity-100',
            'text-sm font-medium text-gray-900 transition duration-200 ease-in-out',
          ]"
          >{{ label }}</span
        >
        <!-- <span class="text-sm text-gray-500">(Save 10%)</span> -->
      </SwitchLabel>
    </SwitchGroup>
    <div v-if="hint" class="mt-2 text-xs text-gray-500">
      {{ hint }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { Switch, SwitchGroup, SwitchLabel } from "@headlessui/vue";
import { useVModel } from "@vueuse/core";
import { useField } from "vee-validate";
import { computed, ref } from "vue";

const props = withDefaults(
  defineProps<{
    modelValue?: boolean;
    validationName?: string;
    label?: string;
    as?: string;
    disabled?: boolean;
    hint?: string;
  }>(),
  { as: "div" }
);

const { value: validationValue, errorMessage } = props.validationName
  ? useField(() => props.validationName ?? "")
  : { value: ref(false), errorMessage: ref(false) };

const emit = defineEmits(["update:modelValue"]);

const value = useVModel(props, "modelValue", emit);

const setValue = (v: boolean) => {
  if (props.validationName) {
    validationValue.value = v;
  } else {
    value.value = v;
  }
};

const getValue = computed(() => {
  if (props.validationName) {
    return !!validationValue.value;
  }
  return !!value.value;
});
</script>
