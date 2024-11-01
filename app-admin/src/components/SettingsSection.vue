<script setup lang="ts">
import type { SparkPlugin } from "@/stores/registry";
import ACardContent from "./common/ACardContent.vue";
import { computed } from "vue";

const props = withDefaults(
  defineProps<{
    title: string;
    subtitle: string;
    options?: string[];
    plugin: SparkPlugin;
  }>(),
  {
    options: () => [],
  }
);

const pluginOptions = computed(() => Object.keys(props.plugin.options));

const hasAnyPluginOptions = computed(() =>
  props.options.some((o: string) => pluginOptions.value.includes(o))
);
</script>
<template>
  <ACardContent
    v-if="options.length === 0 || hasAnyPluginOptions"
    :class="{
      'ring-2 ring-inset ring-aprimary-600': options.includes(
        String($route.query.highlight)
      ),
    }"
  >
    <div
      class="grid grid-cols-1 gap-x-8 gap-y-10 md:grid-cols-3 md:px-4 md:py-2"
    >
      <div>
        <span class="text-base font-semibold leading-7 text-gray-900">
          {{ title }}
        </span>
        <div class="mt-1 text-sm leading-6 text-gray-500">
          {{ subtitle }}
        </div>
      </div>

      <div class="-mt-3 md:col-span-2 lg:-mt-6">
        <dl class="space-y-6 divide-y divide-gray-100 text-sm leading-6">
          <slot name="default"></slot>
        </dl>
      </div>
    </div>
  </ACardContent>
</template>
