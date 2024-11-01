<template>
  <div class="relative flex items-start">
    <div class="text-sm">
      <label class="flex h-5 items-center text-gray-700">
        <input
          v-if="!loading"
          :value="modelValue"
          @input="update"
          type="checkbox"
          class="my-0 mr-2 h-4 w-4 rounded border-gray-300 text-aprimary-600 focus:ring-aprimary-500"
          :checked="isChecked"
        />
        <span v-else class="mdi mdi-loading mdi-spin text-aprimary-600"> </span>
        <span class="flex items-center">
          <slot name="label">{{ label }}</slot>
        </span>
      </label>
      <em class="mt-1 text-gray-400" v-if="hint || $slots.hint"
        ><slot name="hint">{{ hint }}</slot></em
      >
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
  modelValue: { type: [Array, Boolean] },
  label: { type: String },
  value: { type: [String, Number] },
  trueValue: { type: Boolean, default: true },
  falseValue: { type: Boolean, default: false },
  hint: { type: String, default: null },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed(() => {
  if (props.modelValue instanceof Array) {
    return props.modelValue.includes(props.value);
  }
  return props.modelValue === props.trueValue;
});

const update = (event: any) => {
  const isChecked = event.target.checked;
  if (props.modelValue instanceof Array) {
    const newValue = [...props.modelValue];
    if (isChecked) {
      newValue.push(props.value);
    } else {
      newValue.splice(newValue.indexOf(props.value), 1);
    }
    emit("update:modelValue", newValue);
  } else {
    emit("update:modelValue", isChecked ? props.trueValue : props.falseValue);
  }
};
</script>
