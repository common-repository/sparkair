<script setup lang="ts">
import { useVModel } from "@vueuse/core";

const props = defineProps<{
  modelValue: any;
  tabs: {
    name: string;
    disabled?: boolean;
    icon?: any;
  }[];
}>();

const emit = defineEmits(["update:modelValue"]);
const selectedValue = useVModel(props, "modelValue", emit);
</script>

<template>
  <div class="p-4 sm:hidden sm:px-6">
    <label for="tabs" class="sr-only">Select a tab</label>
    <select
      v-model="selectedValue"
      id="tabs"
      name="tabs"
      class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-aprimary-600 focus:outline-none focus:ring-aprimary-600 sm:text-sm"
    >
      <option
        v-for="(tab, tabIdx) in tabs"
        :key="tabIdx"
        :selected="tabIdx === selectedValue"
        :value="tabIdx"
        :disabled="tab.disabled"
      >
        <slot name="item.option" :tab="tab"> {{ tab.name }}</slot>
      </option>
    </select>
  </div>
  <div class="hidden overflow-x-scroll sm:block">
    <div class="border-b border-gray-200 px-4 sm:px-6">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <span
          v-for="(tab, tabIdx) in tabs"
          :key="tabIdx"
          @click="
            () => {
              if (!tab.disabled) {
                selectedValue = tabIdx;
              }
            }
          "
          :class="[
            tab.disabled
              ? 'cursor-not-allowed text-gray-400'
              : tabIdx === selectedValue
              ? 'border-aprimary-600 text-aprimary-700'
              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
            'flex cursor-pointer space-x-1 whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
          ]"
          :disabled="tab.disabled"
          :aria-current="tabIdx === selectedValue ? 'page' : undefined"
        >
          <component
            v-if="tab.icon"
            :is="tab.icon"
            :class="{
              'text-aprimary-500': tabIdx === selectedValue,
              'text-gray-400 group-hover:text-gray-500':
                tabIdx !== selectedValue,
            }"
            class="-ml-0.5 mr-2 h-5 w-5"
            aria-hidden="true"
          />
          <slot name="item.tab" :tab="tab">
            {{ tab.name }}
          </slot>
        </span>
      </nav>
    </div>
  </div>
</template>
