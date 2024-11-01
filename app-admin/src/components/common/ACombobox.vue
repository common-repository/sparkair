<template>
  <Combobox as="div" v-model="selectedObjectInner" :disabled="readonly">
    <AInputWrapper
      :label="label"
      :readonly="readonly"
      :label-class="labelClass"
      :error-message="errorMessage"
      :closeable="closeable"
      @close="close"
      :loading="loading"
      :hide-details="!validationName || hideDetails"
    >
      <template #default="{ id }">
        <ComboboxInput
          :id="id"
          :display-value="formatText"
          class="w-full rounded-md border-0 bg-transparent px-3 py-1.5 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
          @change="query = $event.target.value"
          @focus="(e: any) => e?.target?.select()"
          :placeholder="placeholder"
          :readonly="readonly"
          :disabled="readonly"
        />
      </template>
      <template #prependButtons="">
        <ComboboxButton class="">
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </ComboboxButton>
      </template>
      <template #after>
        <transition
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ComboboxOptions
            v-if="filteredObjects.length > 0"
            class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
          >
            <ComboboxOption
              v-for="object in filteredObjects"
              :key="object[keyId]"
              :value="object"
              v-slot="{ active, selected }"
            >
              <li
                :class="[
                  'relative cursor-default select-none py-2 pl-3 pr-9',
                  active ? 'bg-aprimary-600 text-white' : 'text-gray-900',
                ]"
              >
                <span :class="['block truncate', selected && 'font-semibold']">
                  {{ formatText(object) }}
                </span>

                <span
                  v-if="selected"
                  :class="[
                    'absolute inset-y-0 right-0 flex items-center pr-4',
                    active ? 'text-white' : 'text-aprimary-600',
                  ]"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </transition>
      </template>
    </AInputWrapper>
  </Combobox>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { CheckIcon, ChevronUpDownIcon } from "@heroicons/vue/20/solid";
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxLabel,
  ComboboxOption,
  ComboboxOptions,
} from "@headlessui/vue";
import { useVModel } from "@vueuse/core";
import { useField } from "vee-validate";
import AInputWrapper from "./AInputWrapper.vue";

const emit = defineEmits(["update:modelValue", "close", "update"]);

const close = () => {
  selectedObjectInner.value = null;
  emit("close");
};

const props = withDefaults(
  defineProps<{
    modelValue?: any;
    keyText?: string;
    keyId?: string;
    returnObject?: boolean;
    label?: string;
    placeholder?: string;
    items: any[];
    readonly?: boolean;
    textFormatter?: (object: any) => string;
    valueFormatter?: (object: any) => any;
    labelClass?: any;
    validationName?: string;
    hideDetails?: boolean;
    closeable?: boolean;
    loading?: boolean;
  }>(),
  {
    keyId: "id",
    returnObject: false,
    readonly: false,
    hideDetails: false,
    closeable: false,
    loading: false,
  }
);

const selectedValue = useVModel(props, "modelValue", emit);

const { value: validationValue, errorMessage } = props.validationName
  ? useField(() => props.validationName ?? "")
  : { value: ref(undefined), errorMessage: ref(undefined) };

const selectedObjectInner = computed({
  get() {
    const inner = props.validationName
      ? validationValue.value
      : selectedValue.value;

    if (inner === null || inner === undefined) return inner;

    if (!props.returnObject) {
      return props.items.find((i) => formatValue(i[props.keyId]) === inner);
    }
    return inner;
  },
  set(value) {
    const inner =
      value !== null && value !== undefined
        ? formatValue(props.returnObject ? value : value[props.keyId])
        : value;
    if (props.validationName) {
      validationValue.value = inner;
    } else {
      selectedValue.value = inner;
    }
    emit("update", inner);
  },
});

const formatText = (object: any) => {
  if (!object) return "";
  return typeof props.textFormatter === "function"
    ? props.textFormatter(object)
    : props.keyText
    ? object?.[props.keyText]
    : object;
};

const formatValue = (object: any) => {
  return typeof props.valueFormatter === "function"
    ? props.valueFormatter(object)
    : object;
};

const query = ref("");
const filteredObjects = computed(() =>
  query.value === ""
    ? props.items
    : props.items.filter((item: any) => {
        return (props.keyText ? item[props.keyText] : item)
          .toLowerCase()
          .match(
            query.value.toLowerCase().replace(/[.*+?^${}()|[\]\\]/g, "\\$&")
          );
      })
);
</script>
