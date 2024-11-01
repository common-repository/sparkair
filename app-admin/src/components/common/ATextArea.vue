<template>
  <AInputWrapper
    :label="label"
    :readonly="readonly"
    :label-class="labelClass"
    :error-message="errorMessage"
    :loading="loading"
    :hide-details="!validationName || hideDetails"
  >
    <template #default="{ id }">
      <div class="w-full">
        <textarea
          :value="validationName ? validationValue : value"
          @input="onInput"
          :readonly="readonly"
          :disabled="readonly"
          :name="id"
          :id="id"
          class="block w-full rounded-md border-0 bg-transparent p-2 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
          :placeholder="placeholder"
          :rows="rows"
        ></textarea>
        <div v-if="$slots.actions" class="flex space-x-2 px-3 py-2">
          <slot name="actions"></slot>
        </div>
      </div>
    </template>
  </AInputWrapper>
</template>

<script setup lang="ts">
import { useVModel } from "@vueuse/core";
import AInputWrapper from "./AInputWrapper.vue";
import { useField } from "vee-validate";
import { ref, type Ref } from "vue";

const props = defineProps({
  modelValue: { type: [String, Number] },
  label: { type: String },
  placeholder: { type: String },
  labelClass: { type: String },
  rows: { type: Number, default: 4 },
  readonly: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  validationName: { type: String, default: null },
  hideDetails: { type: Boolean, default: false },
});

const {
  value: validationValue,
  errorMessage,
}: { value: Ref<string | undefined>; errorMessage: Ref<string | undefined> } =
  props.validationName
    ? useField(() => props.validationName, undefined, {
        keepValueOnUnmount: true,
      })
    : { value: ref(undefined), errorMessage: ref(undefined) };

const emit = defineEmits(["update:modelValue", "close", "update"]);

const value = useVModel(props, "modelValue", emit);

const setValue = (v: any) => {
  if (props.validationName) {
    validationValue.value = v;
  } else {
    value.value = v;
  }
  emit("update", v);
};

const onInput = (event: Event) => {
  setValue((event.target as HTMLInputElement)?.value);
};
</script>
