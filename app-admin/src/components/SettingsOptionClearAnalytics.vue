<script setup lang="ts">
import type { SparkPlugin } from "@/stores/registry";
import SettingsOption from "./SettingsOption.vue";
import { ref } from "vue";

const props = defineProps<{
  plugin: SparkPlugin;
}>();

const loading = ref(false);
const done = ref(false);
const clearAnalytics = async () => {
  done.value = false;
  loading.value = true;
  await props.plugin.api.delete("/analytics/product-renders");
  loading.value = false;
  done.value = true;
};
</script>

<template>
  <SettingsOption
    label="Free up space"
    type="button"
    :plugin="plugin"
    @saved="clearAnalytics"
    :loading="loading"
    :done="done"
  >
    <template #value-hint>
      Remove some analytics data from the database older than 90 days to free up
      space.
    </template>
  </SettingsOption>
</template>
