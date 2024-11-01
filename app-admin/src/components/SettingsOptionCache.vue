<script setup lang="ts">
import type { SparkPlugin } from "@/stores/registry";
import SettingsOption from "./SettingsOption.vue";
import { ref } from "vue";

const props = defineProps<{
  plugin: SparkPlugin;
}>();

const loading = ref(false);
const done = ref(false);

const clearCache = async () => {
  done.value = false;
  loading.value = true;
  await props.plugin.api.delete("/cache");
  loading.value = false;
  done.value = true;
};
</script>

<template>
  <SettingsOption
    label="Clear cache"
    type="button"
    :plugin="plugin"
    @saved="clearCache"
    :loading="loading"
    :done="done"
  >
  </SettingsOption>
</template>
