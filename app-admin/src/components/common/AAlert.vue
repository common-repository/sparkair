<script setup lang="ts">
import useRegistryStore from "@/stores/registry";
import {
  InformationCircleIcon,
  ExclamationTriangleIcon,
} from "@heroicons/vue/20/solid";

const registryStore = useRegistryStore();

const props = withDefaults(
  defineProps<{
    title?: string;
    type?: string;
  }>(),
  {
    type: "info",
  }
);

type AlertType = {
  icon?: any;
  image?: any;
};

const types: Record<string, AlertType> = {
  info: {
    icon: InformationCircleIcon,
  },
  spark: {
    image: `${registryStore.imagePrefix}/sparkplugins-icon.svg`,
  },
  warning: {
    icon: ExclamationTriangleIcon,
  },
  error: {
    icon: ExclamationTriangleIcon,
  },
};
</script>

<template>
  <div
    class="rounded-md p-4"
    :class="{
      'bg-blue-50': type === 'info',
      'bg-aprimary-50': type === 'spark',
      'bg-yellow-50': type === 'warning',
      'bg-red-50': type === 'error',
    }"
  >
    <div class="flex">
      <div class="flex-shrink-0">
        <component
          v-if="types[type].icon"
          :is="types[type].icon"
          class="h-5 w-5"
          :class="{
            'text-blue-400': type === 'info',
            'text-aprimary-400': type === 'spark',
            'text-yellow-400': type === 'warning',
            'text-red-400': type === 'error',
          }"
          aria-hidden="true"
        />
        <img
          v-else-if="types[type].image"
          :src="types[type].image"
          class="h-10 w-10"
        />
      </div>
      <div class="ml-3 w-full md:flex md:justify-between md:space-x-4">
        <div class="flex flex-col justify-center">
          <h3
            v-if="title"
            class="text-sm font-medium"
            :class="{
              'text-blue-800': type === 'info',
              'text-aprimary-800': type === 'spark',
              'text-yellow-800': type === 'warning',
              'text-red-800': type === 'error',
            }"
          >
            {{ title }}
          </h3>
          <div
            class="text-sm"
            :class="{
              'text-blue-700': type === 'info',
              'text-aprimary-700': type === 'spark',
              'text-yellow-700': type === 'warning',
              'text-red-700': type === 'error',
            }"
          >
            <slot name="default"></slot>
          </div>
        </div>
        <div
          v-if="$slots.actions"
          class="mt-4 flex self-center md:mt-0 md:shrink"
        >
          <slot name="actions"></slot>
        </div>
      </div>
    </div>
  </div>
</template>
