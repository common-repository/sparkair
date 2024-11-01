<script setup lang="ts">
import type { ApexOptions } from "apexcharts";
import VueApexCharts from "vue3-apexcharts";
import { computed } from "vue";
import { mergeDeep } from "@/services/utils";

const props = defineProps<{
  series: ApexAxisChartSeries;
  options?: ApexOptions;
}>();

const defaultOptions: ApexOptions = {
  stroke: {
    curve: "smooth",
    width: 2,
  },
  fill: {
    opacity: 0.3,
  },
  yaxis: {
    min: 0,
  },
  colors: ["#4f46e5"],
  grid: {
    padding: {
      top: 2,
    },
  },
};

const mergedOptions = computed(() => mergeDeep(defaultOptions, props.options));
</script>

<template>
  <VueApexCharts :series="series" :options="mergedOptions" height="50">
  </VueApexCharts>
</template>
