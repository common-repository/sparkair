<template>
  <AInputWrapper
    :label="label"
    :closeable="closeable"
    @close="close"
    :readonly="readonly"
    :prepend-icon="prependIcon"
    :prefix="prefix"
    :suffix="suffix"
    :label-class="labelClass"
    :error-message="errorMessage"
    :loading="loading"
    :hide-details="hideDetails || (!validationName && !hint)"
    :align="alignText"
    :append-button-icon="appendButtonIcon"
    :append-button-tooltip="appendButtonTooltip"
    :hint="hint"
    @click:append-button="emit('click:append-button')"
  >
    <template #default="{ id }">
      <input
        v-if="validationName"
        :value="transformFrom(validationValue)"
        @input="transformTo"
        v-bind="binds(id)"
        ref="input"
      />
      <input
        v-else
        :value="transformFrom(value)"
        @input="transformTo"
        v-bind="binds(id)"
        ref="input"
      />
    </template>
    <template v-if="$slots.append" #append>
      <slot name="append"></slot>
    </template>
  </AInputWrapper>
</template>

<script setup lang="ts">
import AInputWrapper from "./AInputWrapper.vue";
import { useVModel } from "@vueuse/core";
import { useField } from "vee-validate";
import { computed, ref, useSlots } from "vue";

const props = withDefaults(
  defineProps<{
    modelValue?: string | number | null;
    type?: string;
    label?: string;
    placeholder?: string;
    closeable?: boolean;
    readonly?: boolean;
    prependIcon?: any;
    appendButtonIcon?: any;
    appendButtonTooltip?: string;
    prefix?: string;
    suffix?: string;
    labelClass?: any;
    align?: string;
    validationName?: string;
    hint?: string;
    hideDetails?: boolean;
    loading?: boolean;
  }>(),
  {
    type: "text",
    closeable: false,
    readonly: false,
    prefix: "",
    suffix: "",
    loading: false,
  }
);

const { value: validationValue, errorMessage } = props.validationName
  ? useField(() => String(props.validationName))
  : { value: ref(undefined), errorMessage: ref(undefined) };

const emit = defineEmits(["update:modelValue", "close", "click:append-button"]);

const close = () => {
  setValue(undefined);
  emit("close");
};

const value = useVModel(props, "modelValue", emit);

const setValue = (v: any) => {
  if (props.validationName) {
    validationValue.value = v;
  } else {
    value.value = v;
  }
};

const transformFrom = (v: any) => {
  let newValue = v;
  return newValue;
};

const transformTo = (e: Event) => {
  const v = (e.target as HTMLInputElement).value;
  setValue(v);
};

const alignText = props.align ? props.align : "left";

const slots = useSlots();

const binds = computed(() => (id: string) => ({
  type: props.type,
  readonly: props.readonly,
  disabled: props.readonly,
  name: id,
  id: id,
  class: {
    "pr-1": props.suffix || slots.append,
    "pl-1": props.prefix,
    "pl-2": props.prependIcon,
    "pr-2": props.appendButtonIcon,
    "text-right": alignText === "right",
    "w-full rounded-md border-0 bg-transparent px-3 py-1.5 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6":
      true,
  },
  placeholder: props.placeholder,
}));

const input = ref();
const focus = () => {
  input.value?.focus();
};

const select = () => {
  input.value?.select();
};

defineExpose({ focus, select });
</script>
