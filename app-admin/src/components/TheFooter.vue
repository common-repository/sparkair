<script setup lang="ts">
import useRegistryStore from "@/stores/registry";

const registryStore = useRegistryStore();
</script>

<template>
  <div class="mx-auto flex items-center justify-end space-x-5 pt-4">
    <div class="flex-shrink-0">
      <div class="relative text-right">
        <span class="text-gray-500">This plugin is proudly provided by</span>
        <a
          href="https://sparkplugins.com"
          target="_blank"
          class="-m-1.5 p-1.5"
          rel="noreferrer"
        >
          <img
            class="h-12"
            :src="`${registryStore.imagePrefix}/sparkplugins-logo.svg`"
            alt="SparkPlugins.com"
          />
        </a>
        <a
          href="https://sparkplugins.com/support"
          target="_blank"
          class="-m-1.5 p-1.5 text-aprimary-600"
          rel="noreferrer"
        >
          Help &amp; Support
        </a>
      </div>
    </div>
  </div>
</template>
