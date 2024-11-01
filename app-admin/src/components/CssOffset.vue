<script setup lang="ts">
import ATextField from "@/components/common/ATextField.vue";
import { useVModels } from "@vueuse/core";

const props = defineProps<{
  offset: number;
  offsetUnit: string;
  label: string;
}>();

const emit = defineEmits(["update:offset", "update:offsetUnit"]);
const { offset, offsetUnit } = useVModels(props, emit);

const paddingOptions = ["em", "px", "rem", "pt", "%"];
</script>

<template>
  <ATextField v-model.number="offset" :label="label" type="number">
    <template #append>
      <div class="absolute inset-y-0 right-0 flex items-center">
        <select
          v-model="offsetUnit"
          class="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-gray-500 focus:ring-0 sm:text-sm"
        >
          <option v-for="o in paddingOptions" :key="o">
            {{ o }}
          </option>
        </select>
      </div>
    </template>
  </ATextField>
</template>
