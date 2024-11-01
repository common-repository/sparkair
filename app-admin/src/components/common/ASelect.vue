<template>
  <Listbox as="div" v-model="selectedObjectInner" :disabled="readonly">
    <ListboxLabel
      v-if="label"
      class="mb-1 block text-sm font-medium text-gray-700"
      >{{ label }}</ListboxLabel
    >
    <div class="relative">
      <slot name="button">
        <ListboxButton
          class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 text-left shadow-sm focus:border-aprimary-500 focus:outline-none focus:ring-1 focus:ring-aprimary-500 sm:text-sm"
          :class="{
            'pr-10': !dismissable,
            'pr-16': dismissable,
          }"
        >
          <XMarkIcon
            class="absolute right-7 z-10 inline-block h-5 w-5 cursor-pointer text-gray-400"
            v-if="dismissable && selectedObjectInner"
            @click.stop="selectedObjectInner = null"
            aria-hidden="true"
          />
          <span class="flex items-center">
            <span
              aria-label="Laden"
              v-if="loading"
              class="mdi mdi-spin mdi-loading mr-3 flex-shrink-0 text-aprimary-600"
            >
            </span>
            <span class="block truncate">
              {{
                selectedObjectInner ? selectedObjectInner[keyText] : placeholder
              }}
            </span>
          </span>
          <span
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
          >
            <ChevronUpDownIcon
              class="h-5 w-5 text-gray-400"
              aria-hidden="true"
            />
          </span>
        </ListboxButton>
      </slot>
      <transition
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <ListboxOptions
          class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
        >
          <ListboxOption
            as="template"
            v-for="object in items"
            :key="object[keyId]"
            :value="object"
            v-slot="{ active, selected }"
          >
            <li
              :class="[
                active ? 'bg-aprimary-600 text-white' : 'text-gray-900',
                'relative cursor-pointer select-none py-2 pl-3 pr-9',
              ]"
            >
              <div class="flex items-center">
                <!-- <span
                  :class="[
                    person.online ? 'bg-green-400' : 'bg-gray-200',
                    'mr-3 inline-block h-2 w-2 flex-shrink-0 rounded-full',
                  ]"
                  aria-hidden="true"
                /> -->
                <span
                  :class="[
                    selected ? 'font-semibold' : 'font-normal',
                    'block truncate',
                  ]"
                >
                  <slot name="item" :text="object[keyText]" :item="object">
                    {{ object[keyText] }}
                  </slot>
                </span>
              </div>

              <span
                v-if="selected"
                :class="[
                  active ? 'text-white' : 'text-aprimary-600',
                  'absolute inset-y-0 right-0 flex items-center pr-4',
                ]"
              >
                <CheckIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>

<script setup lang="ts">
import { useVModel } from "@vueuse/core";
import { computed, ref } from "vue";
import {
  Listbox,
  ListboxButton,
  ListboxLabel,
  ListboxOption,
  ListboxOptions,
} from "@headlessui/vue";
import { CheckIcon, ChevronUpDownIcon } from "@heroicons/vue/20/solid";
import { XMarkIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(["update:modelValue"]);

const props = withDefaults(
  defineProps<{
    modelValue: any;
    keyText?: string;
    keyId?: string;
    returnObject?: boolean;
    placeholder?: string;
    label?: string;
    items: any[];
    readonly?: boolean;
    loading?: boolean;
    disabled?: boolean;
    dismissable?: boolean;
  }>(),
  {
    keyId: "id",
    keyText: "text",
    returnObject: false,
    readonly: false,
    loading: false,
    disabled: false,
    dismissable: false,
  }
);
const selectedValue = useVModel(props, "modelValue", emit);

const selectedObjectInner = computed({
  get() {
    if (!props.returnObject) {
      return props.items.find((i) => i[props.keyId] === selectedValue.value);
    }
    return selectedValue.value;
  },
  set(value) {
    if (!value) {
      selectedValue.value = null;
    } else {
      selectedValue.value = props.returnObject ? value : value[props.keyId];
    }
  },
});
</script>
